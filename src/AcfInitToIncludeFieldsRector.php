<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AcfInitToIncludeFieldsRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         "In field-*.php and fields-*.php files, replace add_action('acf/init', with add_action('acf/include_fields'",
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
add_action('acf/init', 'my_acf_add_local_field_groups');
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
add_action('acf/include_fields', 'my_acf_add_local_field_groups');
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [FuncCall::class];
   }

   /**
    * @param FuncCall $node
    */
   public function refactor(Node $node): ?Node
   {
      // Only process files prefixed with "field-" or "fields-"
      $basename = basename($this->file->getFilePath());
      if (!str_starts_with($basename, 'field-') && !str_starts_with($basename, 'fields-')) {
         return null;
      }

      // Check if this is an add_action() call
      if (!$this->isName($node->name, 'add_action')) {
         return null;
      }

      // Ensure the first argument exists and is the string 'acf/init'
      if (!isset($node->args[0])) {
         return null;
      }

      $firstArg = $node->args[0]->value;
      if (!$firstArg instanceof String_) {
         return null;
      }

      if ($firstArg->value !== 'acf/init') {
         return null;
      }

      // Replace 'acf/init' with 'acf/include_fields'
      $firstArg->value = 'acf/include_fields';

      return $node;
   }
}
