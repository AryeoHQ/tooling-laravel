# Rector

This package ships a preconfigured Rector setup that dynamically loads rules from all installed packages. When you run `tooling:rector`, rules from this package and any downstream package that registers them are automatically applied.

## What's Included

### Dynamic Rule Loading

The bundled `rector.php` uses a manifest-based discovery system. Rather than hardcoding rules, it reads from a cached manifest that aggregates rule registrations from all installed packages.

### Bundled Rules

The package registers its own rules via `extra.tooling.rector.rules` in `composer.json`:

- **PHPUnit annotation-to-attribute conversions** — `@covers`, `@dataProvider`, `@depends`, `@ticket`, and `test` prefix are converted to their PHP attribute equivalents
- **Carbon to Date facade** — replaces direct `Carbon\Carbon` and `Carbon\CarbonImmutable` usage with `Illuminate\Support\Facades\Date` (companion to the [PHPStan rule](phpstan.md) that disallows direct Carbon usage)

### Downstream Package Rules

Rules registered by other packages via `extra.tooling.rector` in their `composer.json` are automatically discovered and loaded. See [Registering Rules](#registering-rules).

## Configuration

### Paths

Set `RECTOR_PATHS` in your `.env` or `testbench.yaml`:

```env
RECTOR_PATHS=app,tests
```

This maps to the `tooling.rector.cli.arguments.source` config key.

### Configuration File

By default, the bundled `rector.php` is used. This is set via `tooling.rector.cli.options.config` in `config/tooling.php`.

### CLI Passthrough

All native Rector CLI options work through the Artisan command:

```bash
php artisan tooling:rector --dry-run
php artisan tooling:rector --clear-cache
```

## Writing Custom Rules

The `Tooling\Rector\Rules\Rule` base class simplifies writing Rector rules by providing a structured lifecycle, declarative metadata via attributes, and helpful traits.

### Base Class

Instead of extending `Rector\Rector\AbstractRector` directly, extend `Tooling\Rector\Rules\Rule`:

```php
<?php

namespace Your\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Rule;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\NodeType;

#[Definition('Add the final keyword to all classes')]
#[NodeType(Class_::class)]
#[Sample('your-package.rector.rules.samples')]
final class MakeClassFinal extends Rule
{
    public function shouldHandle(Node $node): bool
    {
        // Return false to skip this node
        return ! $node->isFinal();
    }

    public function handle(Node $node): null|Node
    {
        // Apply the transformation
        $node->flags |= Class_::MODIFIER_FINAL;

        return $node;
    }
}
```

### Lifecycle

| Method | Purpose |
|--------|---------|
| `shouldHandle(Node)` | Optional. Return `false` to skip this node. Only called after the node type has been matched against declared `#[NodeType]` attributes. |
| `handle(Node)` | Apply the transformation. Return the modified node, or `null` if no change was made. |

### Attributes

#### `#[NodeType]`

Declare which AST node type(s) your rule handles. This is the same attribute used by PHPStan rules (`Tooling\Rules\Attributes\NodeType`). It is repeatable:

```php
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use Tooling\Rules\Attributes\NodeType;

#[NodeType(Class_::class)]
#[NodeType(Enum_::class)]
```

#### `#[Definition]`

Provide a human-readable description of what your rule does. This is used by Rector's `getRuleDefinition()`:

```php
use Tooling\Rector\Rules\Definitions\Attributes\Definition;

#[Definition('Add the final keyword to all classes')]
```

#### `#[Sample]`

Declare the Blade view namespace containing before/after code samples. These are used by Rector's `getRuleDefinition()` to generate documentation:

```php
use Tooling\Rector\Rules\Samples\Attributes\Sample;

#[Sample('your-package.rector.rules.samples')]
```

The view name is derived from your rule class name in `snake-case` with hyphens. For a rule named `MakeClassFinal`, the views resolved are:

```
your-package.rector.rules.samples::make-class-final.before
your-package.rector.rules.samples::make-class-final.after
```

Create the corresponding Blade templates:

```
resources/views/rector/rules/make-class-final/before.blade.php
resources/views/rector/rules/make-class-final/after.blade.php
```

Each template should contain a plain PHP code sample (without `<?php` tags or fencing) showing the code before and after the transformation.

### Available Traits

The base class includes these traits automatically:

#### `ValidatesInheritance`

Check whether a class extends, implements, or uses a given class/interface/trait. Uses PHP's native `ReflectionClass` for deep inheritance checking.

```php
$this->inherits($node, 'App\Models\Model');
$this->inherits($node, ['Interface\A', 'Interface\B']);
$this->doesNotInherit($node, 'Some\BaseClass');
```

> **Note**: The Rector version of this trait uses native PHP reflection (not PHPStan's reflection provider), which means the class must be autoloadable.

#### `ValidatesAttributes`

Check for PHP attributes on any node type that supports them (classes, methods, functions, closures, arrow functions, properties, constants, enum cases, parameters, and property hooks):

```php
$this->hasAttribute($node, 'App\Attributes\SomeAttribute');
```

#### `EnsuresInterfaces`

Safely add an interface to a class node, including the `use` import:

```php
$this->ensureInterfaceIsImplemented($node, 'App\Contracts\SomeInterface');
```

Skips if the class already implements the interface.

#### `EnsuresTraits`

Safely add a trait to a class node, including the `use` import:

```php
$this->ensureTraitIsUsed($node, 'App\Concerns\SomeTrait');
```

Skips if the class already uses the trait.

#### `ParsesNodes`

Parse a PHP file into AST nodes:

```php
$classNode = $this->getClassNode('/path/to/file.php');
```

Returns the first `Class_` node found in the file, or `null`. Useful for loading fixture files or comparing against reference implementations.

### Configurable Rules

If your rule needs configuration, implement `ConfigurableRectorInterface`:

```php
use Rector\Contract\Rector\ConfigurableRectorInterface;

#[Definition('Add interface by used trait')]
#[NodeType(Class_::class)]
#[Sample('your-package.rector.rules.samples')]
final class AddInterfaceByTrait extends Rule implements ConfigurableRectorInterface
{
    private array $interfaceByTrait = [];

    public function configure(array $configuration): void
    {
        $this->interfaceByTrait = $configuration;
    }

    public function handle(Node $node): null|Node
    {
        // Use $this->interfaceByTrait...
    }
}
```

See [Registering Rules](#registering-rules) for how to pass configuration.

## Registering Rules

To make your rules available to all consumers of your package, register them in `composer.json`:

```json
{
    "extra": {
        "tooling": {
            "rector": {
                "rules": "tooling/rector/rules.php"
            }
        }
    }
}
```

### Simple Rules

Return a flat array of rule classes:

```php
<?php

use Your\Rector\Rules\RuleA;
use Your\Rector\Rules\RuleB;

return [
    RuleA::class,
    RuleB::class,
];
```

### Configured Rules

Return an associative array where keys are rule classes and values are configuration arrays:

```php
<?php

use Your\Rector\Rules\AddInterfaceByTrait;

return [
    AddInterfaceByTrait::class => [
        'App\Concerns\HasFilters' => 'App\Contracts\Filterable',
    ],
];
```

Both styles can coexist in the same file — flat entries are loaded as simple rules, associative entries are loaded as configured rules.

After `composer install` or `composer update`, the Composer plugin automatically runs `tooling:discover` to rebuild the manifest. Your rules will be included in subsequent Rector runs.
