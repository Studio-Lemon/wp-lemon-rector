<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Arg;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Custom Rector rule to transform:
 *   $this->attributes['align'] = 'full';
 * into:
 *   $this->set_attribute('align', 'full');
 */
final class AttributesArrayToSetAttributeMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->attributes[key] array assignment to $this->set_attribute(key, value) method call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->attributes['align'] = 'full';
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->set_attribute('align', 'full');
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [Assign::class];
   }

   /**
    * @param Assign $node
    */
   public function refactor(Node $node): ?Node
   {
      // Check if this is $this->attributes[key] = value
      if (!$node->var instanceof ArrayDimFetch) {
         return null;
      }

      $arrayDimFetch = $node->var;
      if (!$arrayDimFetch->var instanceof PropertyFetch) {
         return null;
      }

      $propertyFetch = $arrayDimFetch->var;
      if (!$propertyFetch->var instanceof Variable) {
         return null;
      }

      if ($propertyFetch->var->name !== 'this') {
         return null;
      }

      if (!$this->isName($propertyFetch->name, 'attributes')) {
         return null;
      }

      // Get the array key (e.g., 'align')
      $arrayKey = $arrayDimFetch->dim;
      if ($arrayKey === null) {
         return null;
      }

      // Get the value being assigned
      $value = $node->expr;

      // Build arguments for set_attribute method
      $args = [
         new Arg($arrayKey),
         new Arg($value),
      ];

      // Create the method call: $this->set_attribute(key, value)
      $methodCall = new MethodCall(
         new Variable('this'),
         'set_attribute',
         $args
      );

      return $methodCall;
   }
}
