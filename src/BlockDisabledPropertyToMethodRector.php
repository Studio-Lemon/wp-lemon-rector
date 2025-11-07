<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class BlockDisabledPropertyToMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Transform $this->block_disabled = true into $this->set_disabled()',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->block_disabled = true;
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->set_disabled();
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
      // Check if this is $this->block_disabled = true
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

      if (!$this->isName($propertyFetch->name, 'block_disabled')) {
         return null;
      }

      // Check if the value is true
      if (!$node->expr instanceof ConstFetch) {
         return null;
      }

      if (!$this->isName($node->expr->name, 'true')) {
         return null;
      }

      // Replace with $this->set_disabled()
      return new MethodCall(
         new Variable('this'),
         'set_disabled'
      );
   }
}
