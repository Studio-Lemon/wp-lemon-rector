<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
   ->withPaths([
      __DIR__ . '/src',
   ])
   ->withPhpSets(
      php83: true,
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
      StaticClosureRector::class,
      StaticArrowFunctionRector::class,
      ReadOnlyPropertyRector::class,
   ])
   ->withRules([
      \WP_Lemon\Package\Rector\ClassesArrayToAddClassMethodRector::class,
      \WP_Lemon\Package\Rector\AttributesArrayToSetAttributeMethodRector::class,
      \WP_Lemon\Package\Rector\InnerBlocksStringToMethodRector::class,
      \WP_Lemon\Package\Rector\FieldsArrayToGetFieldMethodRector::class,
   ])
   ->withImportNames(
      importShortClasses: false,
      removeUnusedImports: true,
   );
