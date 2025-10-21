# WP Lemon Rector

A pluggable Rector ruleset for WP Lemon projects.

## Installation

Install this package in your project using Composer:

```bash
composer require --dev wp-lemon/rector
```

## Usage

### Basic Usage

Create a `rector.php` file in the root of your project:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        // Add other paths as needed
    ])
    // Import the WP Lemon Rector configuration
    ->withSets([
        __DIR__ . '/vendor/wp-lemon/rector/rector.php',
    ]);
```

### Run Rector

To see what changes Rector would make (dry-run):

```bash
vendor/bin/rector process --dry-run
```

To apply the changes:

```bash
vendor/bin/rector process
```

### Customization

You can extend or override rules in your project's `rector.php`:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSets([
        __DIR__ . '/vendor/wp-lemon/rector/rector.php',
    ])
    ->withSkip([
        // Skip specific rules if needed
        SimplifyIfReturnBoolRector::class,
    ])
    ->withPhpSets(
        php82: true, // Override PHP version if needed
    );
```

## What's Included

This ruleset includes:

- **PHP 8.1+ Sets**: Modern PHP features and best practices
- **Dead Code Removal**: Removes unused code
- **Code Quality**: Improves code quality and readability
- **Coding Style**: Enforces consistent coding style
- **Type Declarations**: Adds type hints where possible
- **Privatization**: Makes properties and methods private where possible
- **Naming**: Improves naming conventions
- **Instance Of**: Simplifies instanceof checks
- **Early Return**: Promotes early return patterns
- **Strict Booleans**: Enforces strict boolean comparisons

### Skipped Rules

Some rules are intentionally skipped to maintain compatibility with WordPress:

- `StaticClosureRector`: WordPress often uses dynamic closures
- `StaticArrowFunctionRector`: WordPress often uses dynamic arrow functions
- `ReadOnlyPropertyRector`: May conflict with WordPress patterns

## Contributing

To modify the ruleset, edit `rector.php` in this package and submit a pull request.

## License

MIT
