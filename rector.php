<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
   ->withPaths([
      __DIR__ . '/src',
   ])
   ->withPhpSets(
      php82: true,
   )
   ->withPreparedSets(
      deadCode: true,
      codeQuality: true,
      codingStyle: true,
      typeDeclarations: true,
      privatization: true,
      naming: true,
      instanceOf: true,
      earlyReturn: true,
      strictBooleans: true,
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

      // Attribute-specific helpers (more specific first)
      \WP_Lemon\Package\Rector\AttributesAlignToSetAlignmentRector::class,
      \WP_Lemon\Package\Rector\AttributesIdToSetAnchorRector::class,
      \WP_Lemon\Package\Rector\AttributesArrayToSetAttributeMethodRector::class,

      // Field helpers
      \WP_Lemon\Package\Rector\FieldsArrayToGetFieldMethodRector::class,

      // Inner block transformation
      \WP_Lemon\Package\Rector\InnerBlocksStringToMethodRector::class,
   ])
   ->withImportNames(
      importShortClasses: false,
      removeUnusedImports: true,
   );
