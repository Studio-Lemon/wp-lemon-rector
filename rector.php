<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;


$path = __DIR__;

// Detect if we're running tests on the package itself or being used in a project
$isTestMode = file_exists($path . '/tests/blocks/');
$isInstalledPackage = file_exists(dirname(dirname(dirname($path))) . '/web/app/themes/');

if ($isTestMode && !$isInstalledPackage) {
   // Running tests on the package itself
   $paths = [$path . '/tests/'];
   $skipPaths = [];
} else {
   // Being used in a project - move up three directories to get to project root
   $projectRoot = dirname(dirname(dirname($path)));
   $paths = [$projectRoot . '/web/app/themes/'];
   $skipPaths = [
      $projectRoot . '/web/app/themes/wp-lemon/*',
      $projectRoot . '/web/app/themes/*/vendor/*',
   ];
}

return RectorConfig::configure()
   ->withPaths($paths)
   ->withPhpSets(
      php83: true,
   )
   ->withSkip($skipPaths)
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
      \WP_Lemon\Package\Rector\SiteIconsAttributesToConstructorRector::class,

      // Attribute-specific helpers (more specific first)
      \WP_Lemon\Package\Rector\AttributesAlignToSetAlignmentRector::class,
      \WP_Lemon\Package\Rector\AttributesIdToSetAnchorRector::class,
      \WP_Lemon\Package\Rector\AttributesArrayToSetAttributeMethodRector::class,

      // Field helpers
      \WP_Lemon\Package\Rector\FieldsArrayToGetFieldMethodRector::class,
      \WP_Lemon\Package\Rector\AcfInitToIncludeFieldsRector::class,

      // Property to method transformations
      \WP_Lemon\Package\Rector\IsPreviewPropertyToMethodRector::class,
      \WP_Lemon\Package\Rector\BlockDisabledPropertyToMethodRector::class,

      // Inner block transformation
      \WP_Lemon\Package\Rector\InnerBlocksStringToMethodRector::class,
   ])
   ->withImportNames(
      importShortClasses: false,
      removeUnusedImports: true,
   );
