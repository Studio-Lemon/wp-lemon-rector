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

final class AttributesIdToSetAnchorRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->attributes["id"] = "value" to $this->set_anchor("value")',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->attributes['id'] = 'formulier';
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->set_anchor('formulier');
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
      // We're looking for $this->attributes['id'] = 'value';
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

      // Check the array key is 'id'
      $dim = $arrayDimFetch->dim;
      if (!$dim instanceof String_ || $dim->value !== 'id') {
         return null;
      }

      // Ensure the assigned value is a string (anchor)
      if (!$node->expr instanceof String_) {
         return null;
      }

      $anchorValue = $node->expr;

      // Build method call: $this->set_anchor('value')
      $args = [new Arg($anchorValue)];

      $methodCall = new MethodCall(new Variable('this'), 'set_anchor', $args);

      return $methodCall;
   }
}
