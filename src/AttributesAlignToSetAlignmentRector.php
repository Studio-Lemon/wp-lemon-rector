<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AttributesAlignToSetAlignmentRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->attributes["align"] = "value" to $this->set_alignment("value")',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->attributes['align'] = 'full';
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->set_alignment('full');
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
      // We're looking for $this->attributes['align'] = 'value';
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

      // Check the array key is 'align'
      $dim = $arrayDimFetch->dim;
      if (!$dim instanceof String_ || $dim->value !== 'align') {
         return null;
      }

      // Ensure the assigned value is a string (alignment)
      if (!$node->expr instanceof String_) {
         return null;
      }

      $value = $node->expr;

      // Build method call: $this->set_alignment('value')
      $args = [new Arg($value)];

      $methodCall = new MethodCall(new Variable('this'), 'set_alignment', $args);

      return $methodCall;
   }
}
