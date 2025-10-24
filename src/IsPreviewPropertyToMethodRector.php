<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class IsPreviewPropertyToMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->is_preview property access to $this->is_preview() method call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
if ($this->is_preview) {
    // do something
}
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
if ($this->is_preview()) {
    // do something
}
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      return [PropertyFetch::class];
   }

   /**
    * @param PropertyFetch $node
    */
   public function refactor(Node $node): ?Node
   {
      // We're looking for $this->is_preview
      if (!$node->var instanceof Variable) {
         return null;
      }

      if ($node->var->name !== 'this') {
         return null;
      }

      if (!$this->isName($node->name, 'is_preview')) {
         return null;
      }

      // Transform to method call: $this->is_preview()
      $methodCall = new MethodCall(new Variable('this'), 'is_preview', []);

      return $methodCall;
   }
}
