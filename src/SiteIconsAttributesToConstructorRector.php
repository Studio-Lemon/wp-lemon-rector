<?php

declare(strict_types=1);

namespace WP_Lemon\Package\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Custom Rector rule to transform:
 *   $icons = new Site_Icons();
 *   $icons->short_name = 'BusinessBase';
 *   $icons->background_color = '#ffffff';
 * into:
 *   $icons = new Site_Icons([
 *       'short_name' => 'BusinessBase',
 *       'background_color' => '#ffffff',
 *   ]);
 */
final class SiteIconsAttributesToConstructorRector extends AbstractRector
{
   public function getRuleDefinition(): RuleDefinition
   {
      return new RuleDefinition(
         'Convert Site_Icons property assignments to constructor array argument',
         [
            new CodeSample(
               <<<'CODE_SAMPLE'
$icons = new Site_Icons();
$icons->short_name = 'BusinessBase';
$icons->background_color = '#ffffff';
$icons->theme_color = '#f5f9e5';
CODE_SAMPLE,
               <<<'CODE_SAMPLE'
$icons = new Site_Icons([
    'short_name' => 'BusinessBase',
    'background_color' => '#ffffff',
    'theme_color' => '#f5f9e5',
]);
CODE_SAMPLE
            ),
         ]
      );
   }

   public function getNodeTypes(): array
   {
      // Process at the container level (class methods, namespaces, or global file scope)
      return [ClassMethod::class, Namespace_::class, \PhpParser\Node\Stmt\Declare_::class];
   }

   /**
    * @param ClassMethod|Namespace_|\PhpParser\Node\Stmt\Declare_ $node
    */
   public function refactor(Node $node): ?Node
   {
      // Handle different node types - we need to traverse to find the statements
      // For file-level (declare or top-level), we need to work with parent
      if ($node instanceof \PhpParser\Node\Stmt\Declare_) {
         // For top-level file statements, we need to work at a higher level
         return null;
      }

      // Get statements from the node
      $stmts = $node->stmts ?? null;

      if ($stmts === null || empty($stmts)) {
         return null;
      }

      $hasChanged = false;
      $newStmts = [];
      $i = 0;

      while ($i < count($stmts)) {
         $stmt = $stmts[$i];

         // Check if this is a Site_Icons instantiation
         $variableName = null;
         if ($this->isSiteIconsInstantiation($stmt, $variableName)) {
            // Collect all consecutive property assignments on this variable
            $propertyAssignments = [];
            $j = $i + 1;

            while ($j < count($stmts)) {
               $nextStmt = $stmts[$j];
               $propertyName = null;
               $value = null;

               if ($this->isPropertyAssignment($nextStmt, $variableName, $propertyName, $value)) {
                  $propertyAssignments[$propertyName] = $value;
                  $j++;
               } else {
                  break;
               }
            }

            // If we found property assignments, transform the constructor
            if (!empty($propertyAssignments)) {
               $transformedStmt = $this->transformToConstructorArray($stmt, $propertyAssignments);
               if ($transformedStmt !== null) {
                  $newStmts[] = $transformedStmt;
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

   /**
    * Check if the statement is a Site_Icons instantiation
    */
   private function isSiteIconsInstantiation(Node $stmt, ?string &$variableName = null): bool
   {
      if (!$stmt instanceof Expression) {
         return false;
      }

      if (!$stmt->expr instanceof Assign) {
         return false;
      }

      $assign = $stmt->expr;

      // Check if we're assigning to a variable
      if (!$assign->var instanceof Variable) {
         return false;
      }

      // Check if the right side is a new Site_Icons()
      if (!$assign->expr instanceof New_) {
         return false;
      }

      $new = $assign->expr;
      if (!$new->class instanceof Name) {
         return false;
      }

      if (!$this->isNames($new->class, ['Site_Icons', 'HighGround\Bulldozer\Site_Icons'])) {
         return false;
      }

      // Get the variable name
      $variableName = $assign->var->name;
      return is_string($variableName);
   }

   /**
    * Check if the statement is a property assignment on the given variable
    */
   private function isPropertyAssignment(
      Node $stmt,
      string $variableName,
      ?string &$propertyName = null,
      ?Node &$value = null
   ): bool {
      if (!$stmt instanceof Expression) {
         return false;
      }

      if (!$stmt->expr instanceof Assign) {
         return false;
      }

      $assign = $stmt->expr;

      // Check if it's a property assignment
      if (!$assign->var instanceof PropertyFetch) {
         return false;
      }

      $propertyFetch = $assign->var;
      if (!$propertyFetch->var instanceof Variable) {
         return false;
      }

      if ($propertyFetch->var->name !== $variableName) {
         return false;
      }

      // Get the property name
      $propertyName = $this->getName($propertyFetch->name);
      if ($propertyName === null) {
         return false;
      }

      $value = $assign->expr;
      return true;
   }

   /**
    * Transform the Site_Icons instantiation to include constructor array
    */
   private function transformToConstructorArray(Node $stmt, array $propertyAssignments): ?Expression
   {
      if (!$stmt instanceof Expression) {
         return null;
      }

      if (!$stmt->expr instanceof Assign) {
         return null;
      }

      $assign = $stmt->expr;
      if (!$assign->expr instanceof New_) {
         return null;
      }

      $new = $assign->expr;

      // Build the array items for the constructor with multiline formatting
      $arrayItems = [];

      foreach ($propertyAssignments as $propertyName => $value) {
         $arrayItem = new ArrayItem(
            $value,
            new String_($propertyName)
         );
         $arrayItems[] = $arrayItem;
      }

      // Create array with multiline formatting
      $array = new Array_($arrayItems);
      $array->setAttribute('kind', Array_::KIND_LONG);

      $new->args = [new Arg($array)];

      return $stmt;
   }
}
