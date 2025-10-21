<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
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
 *   $this->classes[] = 'section hero alignfull has-background';
 * into:
 *   $this->add_class(['section', 'hero'], 'alignfull', 'has-background');
 */
final class ClassesArrayToAddClassMethodRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert $this->classes[] array assignment to $this->add_class() method call',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$this->classes[] = 'section hero alignfull has-background';
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$this->add_class(['section', 'hero'], 'alignfull', 'has-background');
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
      // Check if this is $this->classes[]
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

      if (!$this->isName($propertyFetch->name, 'classes')) {
         return null;
      }

      // Check if the assigned value is a string
      if (!$node->expr instanceof String_) {
         return null;
      }

      // Parse the string value into class names
      $classString = $node->expr->value;
      $classNames = preg_split('/\s+/', trim($classString));

      if (empty($classNames)) {
         return null;
      }

      // Build arguments for add_class method
      // Put all classes into a single array
      $args = [];
      $arrayItems = [];

      foreach ($classNames as $className) {
         $arrayItems[] = new Node\Expr\ArrayItem(new String_($className));
      }

      $args[] = new Arg(new Array_($arrayItems));

      // Create the method call: $this->add_class(...)
      $methodCall = new MethodCall(
         new Variable('this'),
         'add_class',
         $args
      );

      return $methodCall;
   }
}
