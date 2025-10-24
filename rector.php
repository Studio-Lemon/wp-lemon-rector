<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;


$path = __DIR__;
// move up three directories
$path = dirname(dirname(dirname($path)));

return RectorConfig::configure()
   ->withPaths([
      $path . '/web/app/themes/',
   ])
   ->withPhpSets(
      php82: true,
   )
   ->withSkip([
      $path . '/web/app/themes/wp-lemon/*',
      $path . '/web/app/themes/*/vendor/*',
   ])
   ->withPreparedSets(
      deadCode: true,
      codeQuality: true,
      codingStyle: true,
      typeDeclarations: true,
      privatization: true,
      naming: true,
      instanceOf: true,
      earlyReturn: true,
   )
   ->withSkip([
      // Skip rules that might be too aggressive for WordPress projects
      ReadOnlyPropertyRector::class,
      FirstClassCallableRector::class,
      RemoveUselessParamTagRector::class,
      RemoveUselessReturnTagRector::class,
   ])
   ->withRules([
      // Class and presentation helpers
      \WP_Lemon\Package\Rector\ClassesArrayToAddClassMethodRector::class,
      \WP_Lemon\Package\Rector\MergeConsecutiveAddClassCallsRector::class,

      // Attribute-specific helpers (more specific first)
      \WP_Lemon\Package\Rector\AttributesAlignToSetAlignmentRector::class,
      \WP_Lemon\Package\Rector\AttributesIdToSetAnchorRector::class,
      \WP_Lemon\Package\Rector\AttributesArrayToSetAttributeMethodRector::class,

      // Field helpers
      \WP_Lemon\Package\Rector\FieldsArrayToGetFieldMethodRector::class,

      // Property to method transformations
      \WP_Lemon\Package\Rector\IsPreviewPropertyToMethodRector::class,

      // Inner block transformation
      \WP_Lemon\Package\Rector\InnerBlocksStringToMethodRector::class,
   ])
   ->withImportNames(
      importShortClasses: false,
      removeUnusedImports: true,
   );
