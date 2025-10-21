<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Custom Rector rule to transform:
 *   '<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />'
 * into:
 *   self::create_inner_blocks($allowed_blocks, $template)
 */
final class InnerBlocksStringToMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert InnerBlocks HTML string concatenation to self::create_inner_blocks() method call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
'<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />'
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
self::create_inner_blocks($allowed_blocks, $template)
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [Concat::class];
   }

   /**
    * @param Concat $node
    */
   public function refactor(Node $node): ?Node
   {
      // We need to find the pattern of string concatenation that contains '<InnerBlocks'
      // and extract the variables from esc_attr(wp_json_encode($var))

      $stringParts = $this->extractConcatParts($node);

      // Check if this looks like an InnerBlocks pattern
      if (!$this->isInnerBlocksPattern($stringParts)) {
         return null;
      }

      // Extract the variables from the pattern
      $variables = $this->extractVariablesFromPattern($stringParts);

      if (empty($variables)) {
         return null;
      }

      // Create the method call: self::create_inner_blocks($allowed_blocks, $template)
      $args = [];
      foreach ($variables as $var) {
         $args[] = new Arg($var);
      }

      $methodCall = new StaticCall(
         new Name('self'),
         'create_inner_blocks',
         $args
      );

      return $methodCall;
   }

   /**
    * Extract all parts of a concatenation chain
    */
   private function extractConcatParts(Node $node): array
   {
      $parts = [];

      if ($node instanceof Concat) {
         // Recursively get left side
         if ($node->left instanceof Concat) {
            $parts = array_merge($parts, $this->extractConcatParts($node->left));
         } else {
            $parts[] = $node->left;
         }

         // Add right side
         $parts[] = $node->right;
      } else {
         $parts[] = $node;
      }

      return $parts;
   }

   /**
    * Check if the pattern looks like InnerBlocks HTML
    */
   private function isInnerBlocksPattern(array $parts): bool
   {
      foreach ($parts as $part) {
         if ($part instanceof String_ && str_contains($part->value, '<InnerBlocks')) {
            return true;
         }
      }
      return false;
   }

   /**
    * Extract variables from esc_attr(wp_json_encode($var)) calls
    */
   private function extractVariablesFromPattern(array $parts): array
   {
      $variables = [];

      foreach ($parts as $part) {
         // Look for esc_attr(wp_json_encode($var))
         if ($part instanceof FuncCall && $this->isName($part->name, 'esc_attr')) {
            if (!empty($part->args)) {
               $innerCall = $part->args[0]->value;

               // Check if it's wp_json_encode
               if ($innerCall instanceof FuncCall && $this->isName($innerCall->name, 'wp_json_encode')) {
                  if (!empty($innerCall->args)) {
                     $variables[] = $innerCall->args[0]->value;
                  }
               }
            }
         }
      }

      return $variables;
   }
}
