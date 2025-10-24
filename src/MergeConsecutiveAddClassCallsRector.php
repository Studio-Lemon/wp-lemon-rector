<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Arg;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class MergeConsecutiveAddClassCallsRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Merge consecutive $this->add_class() calls into a single call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->add_class(['bg-pill']);
$this->add_class(['section', 'has-background']);
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->add_class(['bg-pill', 'section', 'has-background']);
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [ClassMethod::class];
   }

   /**
    * @param ClassMethod $node
    */
   public function refactor(Node $node): ?Node
   {
      if ($node->stmts === null) {
         return null;
      }

      $hasChanged = false;
      $newStmts = [];
      $i = 0;

      while ($i < count($node->stmts)) {
         $stmt = $node->stmts[$i];

         // Check if this is an add_class call
         if ($this->isAddClassExpression($stmt)) {
            // Collect all consecutive add_class calls
            $addClassExpressions = [$stmt];
            $j = $i + 1;

            while ($j < count($node->stmts) && $this->isAddClassExpression($node->stmts[$j])) {
               $addClassExpressions[] = $node->stmts[$j];
               $j++;
            }

            // If we found multiple consecutive calls, merge them
            if (count($addClassExpressions) > 1) {
               $mergedExpr = $this->mergeAddClassExpressions($addClassExpressions);
               if ($mergedExpr !== null) {
                  $newStmts[] = $mergedExpr;
                  $hasChanged = true;
                  $i = $j; // Skip past all the merged statements
                  continue;
               }
            }
         }

         $newStmts[] = $stmt;
         $i++;
      }

      if (!$hasChanged) {
         return null;
      }

      $node->stmts = $newStmts;
      return $node;
   }

   private function isAddClassExpression(Node $stmt): bool
   {
      if (!$stmt instanceof Expression) {
         return false;
      }

      if (!$stmt->expr instanceof MethodCall) {
         return false;
      }

      $methodCall = $stmt->expr;
      if (!$methodCall->var instanceof Variable) {
         return false;
      }

      if ($methodCall->var->name !== 'this') {
         return false;
      }

      return $this->isName($methodCall->name, 'add_class');
   }

   /**
    * @param Expression[] $expressions
    */
   private function mergeAddClassExpressions(array $expressions): ?Expression
   {
      $mergedItems = [];

      foreach ($expressions as $expr) {
         if (!$expr->expr instanceof MethodCall) {
            continue;
         }

         $methodCall = $expr->expr;
         if (empty($methodCall->args)) {
            continue;
         }

         $arg = $methodCall->args[0];
         if (!$arg->value instanceof Array_) {
            continue;
         }

         foreach ($arg->value->items as $item) {
            if ($item instanceof ArrayItem) {
               $mergedItems[] = $item;
            }
         }
      }

      if (empty($mergedItems)) {
         return null;
      }

      // Create new merged add_class call
      $mergedArray = new Array_($mergedItems);
      $newMethodCall = new MethodCall(
         new Variable('this'),
         'add_class',
         [new Arg($mergedArray)]
      );

      return new Expression($newMethodCall);
   }
}
