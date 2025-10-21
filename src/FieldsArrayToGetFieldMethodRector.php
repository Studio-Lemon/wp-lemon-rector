<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Arg;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Custom Rector rule to transform:
 *   $this->fields['image_field']
 * into:
 *   $this->get_field('image_field')
 */
final class FieldsArrayToGetFieldMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->fields[key] array access to $this->get_field(key) method call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->fields['image_field']
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->get_field('image_field')
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [ArrayDimFetch::class];
   }

   /**
    * @param ArrayDimFetch $node
    */
   public function refactor(Node $node): ?Node
   {
      // Check if this is $this->fields[key]
      if (!$node->var instanceof PropertyFetch) {
         return null;
      }

      $propertyFetch = $node->var;
      if (!$propertyFetch->var instanceof Variable) {
         return null;
      }

      if ($propertyFetch->var->name !== 'this') {
         return null;
      }

      if (!$this->isName($propertyFetch->name, 'fields')) {
         return null;
      }

      // Get the array key (e.g., 'image_field')
      $arrayKey = $node->dim;
      if ($arrayKey === null) {
         return null;
      }

      // Build arguments for get_field method
      $args = [
         new Arg($arrayKey),
      ];

      // Create the method call: $this->get_field(key)
      $methodCall = new MethodCall(
         new Variable('this'),
         'get_field',
         $args
      );

      return $methodCall;
   }
}
