# WP Lemon Rector

A comprehensive Rector package with custom rules specifically designed for WP Lemon projects. This package includes Rector itself along with custom transformation rules for the Bulldozer block framework.

## Installation

Install this package in your WP Lemon project using Composer:

```bash
composer require --dev studiolemon/wp-lemon-rector
```

This will install Rector along with all custom WP Lemon rules.

## Usage

Since this package includes Rector, you can run it directly without any additional configuration:

```bash
# Using the WP Lemon Rector executable
vendor/bin/wp-lemon-rector process path/to/your/blocks --dry-run
vendor/bin/wp-lemon-rector process path/to/your/blocks

# Or using Rector directly
vendor/bin/rector process path/to/your/blocks --dry-run
vendor/bin/rector process path/to/your/blocks
```

### Custom Configuration (Optional)

If you need to customize paths, add additional rules, or change settings, create a `rector.php` file in your project root:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/web/app/themes/your-theme/blocks',
        __DIR__ . '/web/app/plugins/your-plugin/src',
    ])
    // Import the WP Lemon Rector configuration
    ->withSets([
        __DIR__ . '/vendor/studiolemon/wp-lemon-rector/rector.php',
    ]);
```

## Custom Rules

This package includes custom Rector rules tailored for WP Lemon's Bulldozer block framework:

### 1. ClassesArrayToAddClassMethodRector

Transforms array assignments to the `add_class()` method:

```php
// Before
$this->classes[] = 'section hero alignfull has-background';

// After
$this->add_class(['section', 'hero', 'alignfull', 'has-background']);
```

### 2. MergeConsecutiveAddClassCallsRector

Optimizes multiple consecutive `add_class()` calls by merging them into a single call:

```php
// Before
$this->add_class(['bg-pill']);
$this->add_class(['section', 'has-background']);

// After
$this->add_class(['bg-pill', 'section', 'has-background']);
```

### 3. AttributesAlignToSetAlignmentRector

### 3. SiteIconsAttributesToConstructorRector

Transforms property assignments on a `Site_Icons` instance into a single constructor array argument:

```php
// Before
$icons = new Site_Icons();
$icons->short_name = 'BusinessBase';
$icons->background_color = '#ffffff';
$icons->theme_color = '#f5f9e5';

// After
$icons = new Site_Icons([
    'short_name' => 'BusinessBase',
    'background_color' => '#ffffff',
    'theme_color' => '#f5f9e5',
]);
```

Transforms the `align` attribute assignment to the `set_alignment()` method:

```php
// Before
$this->attributes['align'] = 'full';

// After
$this->set_alignment('full');
```

### 4. AttributesIdToSetAnchorRector

Transforms the `id` attribute assignment to the `set_anchor()` method:

```php
// Before
$this->attributes['id'] = 'formulier';

// After
$this->set_anchor('formulier');
```

### 5. AttributesArrayToSetAttributeMethodRector

Fallback transformation for attribute assignments into `set_attribute()` when no more-specific rule applies:

```php
// Before
$this->attributes['foo'] = 'bar';

// After
$this->set_attribute('foo', 'bar');
```

### 6. FieldsArrayToGetFieldMethodRector

Transforms field array access to the `get_field()` method:

```php
// Before
$value = $this->fields['image_field'];

// After
$value = $this->get_field('image_field');
```

### 7. AcfInitToIncludeFieldsRector

In files prefixed with `field-` or `fields-` (e.g. `field-hero.php`, `fields-hero.php`), replaces the `acf/init` hook with `acf/include_fields`. This ensures ACF field groups are registered at the correct point in the WordPress lifecycle.

```php
// Before (in field-hero.php or fields-hero.php)
add_action('acf/init', 'my_acf_add_local_field_groups');

// After
add_action('acf/include_fields', 'my_acf_add_local_field_groups');
```

> **Note:** This rule only applies to files whose filename starts with `field-` or `fields-`. Files with other names are left untouched.

### 8. IsPreviewPropertyToMethodRector

Transforms `is_preview` property access to method call:

```php
// Before
if ($this->is_preview) {
    // do something
}

// After
if ($this->is_preview()) {
    // do something
}
```

### 9. BlockDisabledPropertyToMethodRector

Transforms `block_disabled` property assignment to the `set_disabled()` method:

```php
// Before
$this->block_disabled = true;

// After
$this->set_disabled();
```

### 10. InnerBlocksStringToMethodRector

Transforms InnerBlocks HTML string concatenation to the `create_inner_blocks()` method:

```php
// Before
'InnerBlocks' => '<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />'

// After
'InnerBlocks' => self::create_inner_blocks($allowed_blocks, $template)
```

## Standard Rector Sets

In addition to custom rules, this package includes:

- **PHP 8.3+ Features**: Modern PHP syntax and features
- **Dead Code Removal**: Removes unused code and variables
- **Code Quality**: Improves overall code quality
- **Coding Style**: Enforces consistent coding standards
- **Type Declarations**: Adds type hints where possible
- **Privatization**: Makes properties and methods private when possible
- **Naming**: Improves variable and method naming
- **Early Return**: Promotes early return patterns
- **Strict Booleans**: Enforces strict boolean comparisons

### WordPress Compatibility

Some rules are intentionally skipped to maintain compatibility with WordPress:

- `StaticClosureRector` - WordPress often uses dynamic closures
- `StaticArrowFunctionRector` - WordPress often uses dynamic arrow functions
- `ReadOnlyPropertyRector` - May conflict with WordPress patterns

## Contributing

Contributions are welcome! To add or modify rules:

1. Edit or add Rector rules in the `src/` directory
2. Update the `rector.php` configuration to register new rules
3. Add test cases in `tests/blocks/`
4. Submit a pull request

## License

MIT
