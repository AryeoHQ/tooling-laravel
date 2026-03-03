# Generator Commands

This package provides conveniences for building Laravel `make:*` generator commands with namespace resolution via Composer autoload maps and structural enforcement via PHPStan rules and Rector auto-fixes.

## Architecture

A generator command in this system is composed of:

1. A **command class** extending `Illuminate\Console\GeneratorCommand` that implements the `GeneratesFile` interface
2. The **`GeneratorCommandCompatibility`** trait that bridges `GeneratesFile` with Laravel's `GeneratorCommand` abstract class
3. A **`Reference`** value object that derives all naming, path, and namespace info from the command's input
4. Optional **concerns** for namespace resolution, class search, and test scaffolding (via `make:test`)

The flow: the command receives user input → constructs a `Reference` → `GeneratorCommandCompatibility` delegates Laravel's stub/path/namespace methods to the `Reference` → the file is generated.

### The `GeneratesFile` Contract

Every generator command must implement `Tooling\GeneratorCommands\Contracts\GeneratesFile`, which requires three PHP 8.4 hooked properties:

```php
interface GeneratesFile
{
    // Path to the stub file used to generate the output
    public string $stub { get; }

    // The name provided by the user (typically from argument('name'))
    public Stringable $nameInput { get; }

    // The value object that derives paths, namespaces, FQCNs, and test info
    public Reference $reference { get; }
}
```

### The `GeneratorCommandCompatibility` Trait

This trait bridges `GeneratesFile` with Laravel's `GeneratorCommand` by implementing the methods Laravel's base class expects:

- `getStub()` → delegates to `$this->stub`
- `getNameInput()` → delegates to `$this->nameInput`
- `rootNamespace()` / `getDefaultNamespace()` → delegates to `$this->reference->namespace`
- `getPath()` → delegates to `$this->reference->directoryPath`

This trait **must** be used alongside `GeneratesFile` (enforced by PHPStan and Rector — see [Structural Enforcement](#structural-enforcement)).

## Reference Value Objects

The `Tooling\GeneratorCommands\References\Contracts\Reference` interface defines the shape of a value object that derives all file and namespace information from the command's input:

```php
interface Reference
{
    public Stringable $name { get; }                // e.g. MakeClassFinal
    public Stringable $namespace { get; }           // e.g. App\Rector\Rules
    public Stringable $fqcn { get; }                // namespace\name
    public Stringable $directory { get; }           // e.g. src/Rector/Rules
    public Stringable $directoryPath { get; }       // Absolute directory path
    public null|Stringable $subdirectory { get; }   // Optional subdirectory (e.g. Carbon)
    public Stringable $filePath { get; }            // Absolute path to the generated file
    public TestClass $test { get; }                 // Derived test file info
}
```

### `TestClass`

The `TestClass` value object wraps a parent `Reference` and derives test-specific naming:

- `$name` → parent name + `Test` suffix
- `$namespace` → same as parent namespace
- `$fqcn` → `$namespace\$nameTest`
- `$filePath` → co-located next to the source file

## Concerns

### `SearchesNamespaces`

Provides `$availableNamespaces` — a `Collection<string, string>` mapping namespace prefixes to directory paths. It merges:

1. The application namespace from `$this->laravel->getNamespace()`
2. PSR-4 `autoload` entries from the project's `composer.json`
3. PSR-4 `autoload-dev` entries from the project's `composer.json`

This is the foundation trait for namespace-aware functionality. It is **required** by `SearchesClasses` and `RetrievesNamespaceFromInput` (enforced by PHPStan and Rector).

### `SearchesClasses`

Provides class search functionality using the Composer classmap:

- `$searchableClasses` — a lazy-loaded `Collection` of classes from the optimized classmap, filtered to available namespaces
- `getClassSearchResults(string $search)` — fuzzy-searches classes by name

Automatically optimizes the classmap on first access. Optionally, define a `filterSearchableClasses(Collection): Collection` method to further narrow results.

**Requires**: `SearchesNamespaces`

### `RetrievesNamespaceFromInput`

Provides interactive namespace resolution:

- `promptForNamespace()` — resolves namespace from `--namespace` option or an interactive `select()` prompt
- Sets `$baseNamespace` and `$baseDirectory` for use in the `Reference` constructor

**Requires**: `SearchesNamespaces`

### `CreatesColocatedTests`

Adds `--test` / `--no-test` option (defaults to `true`) that generates a co-located test file next to the generated class via `make:test`.

**Requires**: `GeneratesFile`

## Building a Custom Command

Here's how to build a `make:rector:rule` command as a complete example:

### 1. Create the Reference

Define a value object implementing `Reference` that knows how to derive paths for your tool:

```php
final class RectorRule implements Reference
{
    public Stringable $name;
    public Stringable $baseNamespace;

    public function __construct(Stringable|string $name, Stringable|string $baseNamespace)
    {
        $this->name = str($name);
        $this->baseNamespace = str($baseNamespace);
    }

    public null|Stringable $subdirectory = null;

    public Stringable $namespace {
        get => $this->baseNamespace->finish('\\')->append('Rector\\Rules');
    }

    public Stringable $fqcn {
        get => $this->namespace->append('\\', $this->name->toString());
    }

    public TestClass $test {
        get => new TestClass($this);
    }
}
```

### 2. Create the Stub

Create a Blade-style stub file:

```php
// stubs/rule.stub
<?php

declare(strict_types=1);

namespace {{ namespace }};
```

### 3. Create the Command

```php
#[AsCommand(name: 'make:rector:rule', description: 'Make a new Rector rule')]
class MakeRule extends GeneratorCommand implements GeneratesFile
{
    use CreatesColocatedTests;
    use GeneratorCommandCompatibility;
    use RetrievesNamespaceFromInput;
    use SearchesNamespaces;

    protected $type = 'Rector Rule';

    public string $stub {
        get => __DIR__.'/stubs/rule.stub';
    }

    public Stringable $nameInput {
        get => $this->nameInput ??= str($this->argument('name'));
    }

    public Reference $reference {
        get => $this->reference ??= new RectorRule(
            name: $this->nameInput,
            baseNamespace: $this->baseNamespace,
        );
    }

    public function handle(): null|bool
    {
        $this->promptForNamespace();

        return parent::handle();
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
            new InputOption('namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace'),
        ];
    }
}
```

The pattern is:
1. Extend `GeneratorCommand`, implement `GeneratesFile`
2. Use `GeneratorCommandCompatibility` + any needed concerns
3. Declare `$stub`, `$nameInput`, `$reference` as hooked properties
4. Call `promptForNamespace()` in `handle()` if using `RetrievesNamespaceFromInput`

## Testing Infrastructure

The package provides reusable test traits that verify generator commands and Reference implementations work correctly.

### `GeneratesFileTestCases`

A trait for testing generator commands. Provides:

- Auto-derived `$command` property from the test class name
- `it_generates_a_file_with_the_correct_namespace` — runs the command and asserts the generated file contains the expected namespace

**Requirements on the test class**: must define `$baselineInput` (array of artisan input) and a `$reference` property.

### `ReferenceTestCases`

A trait for testing Reference value objects. Provides test cases for each derived property (`name`, `subdirectory`, `fqcn`, `filePath`, test name, and test file path).

**Requires**: the test class must implement `TestsReference`, which declares `$subject` (Reference), `$expectedName` (string), and `$expectedSubdirectory` (null|string).

### `RetrievesNamespaceFromInputTestCases`

Tests namespace resolution with and without trailing backslashes. **Requires** `$withNamespaceBackslashInput` and `$withoutNamespaceBackslashInput` properties on the test class.

## Structural Enforcement

The package enforces correct trait/interface composition via PHPStan rules (for detection) and Rector configured rules (for auto-fixing). If you use a trait without the required interface, or implement an interface without the required trait, PHPStan will flag it and Rector can fix it automatically. See [PHPStan](phpstan.md) and [Rector](rector.md) for details.

## Available Commands

### `make:test`

Creates a test file next to any class in the project.

```bash
php artisan make:test
# or
php ./vendor/bin/testbench make:test
```

Prompts for a class via interactive fuzzy search against the Composer classmap. Generates a test file with `#[CoversClass]`, `#[Test]`, and extending `TestCase`, placed in the same directory as the source class.

### `make:rector:rule`

Creates a new Rector rule (and test). See [Rector — Writing Custom Rules](rector.md#writing-custom-rules).

```bash
php artisan make:rector:rule MyRuleName
```

### `make:phpstan:rule`

Creates a new PHPStan rule (and test). See [PHPStan — Writing Custom Rules](phpstan.md#writing-custom-rules).

```bash
php artisan make:phpstan:rule MyRuleName
```
