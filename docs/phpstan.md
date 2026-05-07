# PHPStan

This package ships a preconfigured PHPStan setup at level 6 with multiple bundled rule sets. When you run `tooling:phpstan:analyze`, all of this is loaded automatically.

## What's Included

The bundled configuration is composed of several modular neon files, all included from the root `phpstan.neon`:

### Analysis Level

Level **6** with `treatPhpDocTypesAsCertain: false`.

### Larastan

[Larastan](https://github.com/larastan/larastan) is automatically loaded when running through `artisan` or `testbench`. It provides Laravel-aware static analysis — model relationships, query builders, facades, etc.

### Ergebnis Rules

A curated subset of [ergebnis/phpstan-rules](https://github.com/ergebnis/phpstan-rules) is enabled. Several opinionated rules are disabled by default:

- `final` — disabled
- `finalInAbstractClass` — disabled
- `noExtends` — disabled
- `noNamedArgument` — disabled
- `noParameterWithNullableTypeDeclaration` — disabled
- `noConstructorParameterWithDefaultValue` — disabled
- `noParameterWithNullDefaultValue` — disabled
- `noNullableReturnTypeDeclaration` — disabled
- `noPhpstanIgnore` — disabled

### PHPUnit Conventions

Four rules enforce PHPUnit test conventions:

- Test classes must extend a configured `TestCase` class
- Test methods must use the `#[Test]` attribute (not the `test` method prefix)
- Test methods must not have a `test` prefix
- Test method names must be `snake_case`

The base `TestCase` class(es) can be configured via the `aryeo.tests.testCaseClass` parameter in your PHPStan neon:

```neon
parameters:
    aryeo:
        tests:
            testCaseClass:
                - Tests\TestCase
```

The default recognizes `Tests\TestCase` and `PHPStan\Testing\RuleTestCase`.

### Carbon

Direct usage of `Carbon\Carbon` and `Carbon\CarbonImmutable` is disallowed. Use the `Illuminate\Support\Facades\Date` facade instead. A companion [Rector rule](rector.md) can automatically fix violations.

### Strict & Deprecation Rules

- [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules)
- [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules)
- PHPStan bleeding edge features are enabled

### Meta Rules

Two meta rules enforce that custom PHPStan and Rector rules extend the base `Rule` classes provided by this package:

- PHPStan rules must extend `Tooling\PHPStan\Rules\Rule`
- Rector rules must extend `Tooling\Rector\Rules\Rule`

### Generator Command Enforcement

Five rules enforce correct trait/interface composition on [generator commands](generator-commands.md). Companion [Rector configured rules](rector.md#configurable-rules) can auto-fix most of these violations automatically.

### Downstream Package Rules

Rules registered by other packages via `extra.tooling.phpstan.rules` in their `composer.json` are automatically discovered and included. See [Registering Rules](#registering-rules).

## Commands

| Command | Description |
|---------|-------------|
| `tooling:phpstan:analyze` | Run static analysis |
| `tooling:phpstan:cache-clear` | Clear the result cache |
| `tooling:phpstan:diagnose` | Run PHPStan diagnostics |
| `tooling:phpstan:parameters-dump` | Dump resolved parameters |
| `tooling:phpstan:bisect` | Bisect PHPStan releases to find a regression |

The `tooling:phpstan:analyze` command also accepts `--cache-clear` to clear the result cache before running analysis:

```bash
php artisan tooling:phpstan:analyze --cache-clear
```

## Configuration

### Paths

Set paths via `extra.tooling.phpstan.config.paths` in your `composer.json`:

```json
{
    "extra": {
        "tooling": {
            "phpstan": { "config": { "paths": ["app", "tests"] } }
        }
    }
}
```

This maps to the `tooling.phpstan.cli.{Inspector}.arguments.paths` config key in `config/tooling.php`, where `{Inspector}` is the fully-qualified inspector class name (e.g. `Tooling\PhpStan\Console\Inspectors\Analyze`).

### Configuration File

By default, the bundled `phpstan.neon` is used. This is set via `tooling.phpstan.cli.{Inspector}.options.configuration` in `config/tooling.php`.

### CLI Passthrough

All native PHPStan CLI options work through the Artisan command:

```bash
php artisan tooling:phpstan:analyze --generate-baseline
php artisan tooling:phpstan:analyze --level=8
php artisan tooling:phpstan:analyze --memory-limit=512M
```

## Scaffolding a Rule

Use the `make:phpstan:rule` command to scaffold a new PHPStan rule:

```bash
php artisan make:phpstan:rule MyRuleName
```

The generated rule extends `Tooling\PHPStan\Rules\Rule` and includes the `#[NodeType]` attribute. See [Generator Commands](generator-commands.md) for details on the generator command system.

## Writing Custom Rules

The `Tooling\PHPStan\Rules\Rule` base class simplifies writing PHPStan rules by providing a structured lifecycle and helpful traits.

### Base Class

Instead of implementing `PHPStan\Rules\Rule` directly, extend `Tooling\PHPStan\Rules\Rule`:

```php
<?php

namespace Your\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\PHPStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class ClassMustBeFinal extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        // Return false to skip this node
        return ! $node->isFinal();
    }

    public function handle(Node $node, Scope $scope): void
    {
        // Called only when shouldHandle() returns true
        $this->error(
            message: 'Classes must be declared as final.',
            line: $node->name->getStartLine(),
            identifier: 'classes.mustBeFinal'
        );
    }
}
```

### Lifecycle

| Method | Purpose |
|--------|---------|
| `prepare(Node, Scope)` | Optional setup before processing. Called on every node regardless of `shouldHandle()`. |
| `shouldHandle(Node, Scope)` | Return `false` to skip this node. Defaults to `true`. |
| `handle(Node, Scope)` | Analyse the node and call `$this->error()` to report violations. |

### `#[NodeType]` Attribute

Declare which AST node type(s) your rule handles using the `#[NodeType]` attribute from `Tooling\Rules\Attributes\NodeType`. This replaces the `getNodeType()` method.

```php
use PhpParser\Node\Stmt\Class_;
use Tooling\Rules\Attributes\NodeType;

#[NodeType(Class_::class)]
```

### `error()` Helper

Report a rule violation:

```php
$this->error(
    message: 'Something is wrong.',
    line: $node->getStartLine(),
    identifier: 'rules.yourRuleIdentifier'
);
```

Multiple errors can be reported per node — they accumulate in the `$this->errors` collection.

### Available Traits

The base class includes these traits automatically:

#### `ValidatesInheritance`

Check whether a class extends, implements, or uses a given class/interface/trait. Uses PHPStan's reflection provider for deep inheritance checking.

```php
// Check if a class inherits from one or more classes/interfaces/traits
$this->inherits($node, 'App\Models\Model', $reflectionProvider);
$this->inherits($node, ['Interface\A', 'Interface\B'], $reflectionProvider);

// Negation
$this->doesNotInherit($node, 'Some\BaseClass', $reflectionProvider);
```

#### `ValidatesAttributes`

Check for PHP attributes on any node type that supports them (classes, methods, functions, closures, arrow functions, properties, constants, enum cases, parameters, and property hooks):

```php
$this->hasAttribute($node, 'App\Attributes\SomeAttribute');
```

## Mixin Reflection Helpers

When writing PHPStan extensions (e.g. a `MethodsClassReflectionExtension`), you often need to resolve dynamic methods from a mixin class whose methods return closures. The `Mixin` and `Macro` classes handle this.

### `Mixin`

`Tooling\PhpStan\Reflection\Classes\Mixin` looks up methods on a mixin class and resolves them into `Macro` reflections. It only considers methods whose return type is a `Closure` — anything else is ignored.

```php
use PHPStan\Reflection\ReflectionProvider;
use Tooling\PhpStan\Reflection\Classes\Mixin;

$mixin = new Mixin($reflectionProvider, MixesIn::class);

// Check if a method exists and returns a Closure
$mixin->hasMethod($classReflection, 'someMethod');

// Get the Macro reflection (or null)
$macro = $mixin->getMethod($classReflection, 'someMethod');

// Static methods
$macro = $mixin->getMethod($classReflection, 'someMethod', static: true);
```

Resolved macros are cached internally — repeated lookups for the same class and method return the same `Macro` instance.

The mixin class should have methods that return typed closures:

```php
final class MixesIn
{
    /** @return Closure(string $name): string */
    public function greet(): Closure
    {
        return fn (string $name): string => "Hello, {$name}!";
    }
}
```

### `Macro`

`Tooling\PhpStan\Reflection\Methods\Macro` implements `PHPStan\Reflection\MethodReflection`. It wraps a `ClosureType` and exposes its parameters and return type as a method on the target class. You typically won't construct these directly — `Mixin::getMethod()` creates them for you.

## Registering Rules

To make your rules available to all consumers of your package, register them in `composer.json`:

```json
{
    "extra": {
        "tooling": {
            "phpstan": {
                "rules": "tooling/phpstan/rules.neon"
            }
        }
    }
}
```

The neon file should register your rules as services:

```neon
services:
    -
        class: Your\PHPStan\Rules\SomeRule
        tags:
            - phpstan.rules.rule
    -
        class: Your\PHPStan\Rules\AnotherRule
        tags:
            - phpstan.rules.rule
```

After `composer install` or `composer update`, the Composer plugin automatically runs `tooling:discover` to rebuild the manifest. Your rules will be included in subsequent PHPStan runs.
