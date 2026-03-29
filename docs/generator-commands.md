# Generator Commands

This package provides conveniences for building Laravel `make:*` generator commands with namespace resolution via Composer autoload maps.

## Concerns

### `RetrievesNamespace`

Resolves which namespace to generate into through a chain of strategies: `--namespace` option → `namespace` argument → interactive `suggest()` prompt. The first strategy that yields a valid namespace wins.

- `resolveNamespace()` — runs the resolution chain, setting `$baseNamespace` and `$baseDirectory` for use in the `Reference` constructor
- `getNamespaceInputOptions()` — returns the `--namespace` `InputOption` definition (spread into `getOptions()`)
- `getNamespaceInputArguments()` — returns the `namespace` `InputArgument` definition (spread into `getArguments()` when positional namespace input is desired)

Available namespaces are discovered automatically from the project's PSR-4 `autoload` and `autoload-dev` entries in `composer.json`.

### `SearchesAutoloadCaches`

Provides class search functionality using the classmap `Cache` (pre-computed by `tooling:optimize`):

- `collector(): string` — abstract method; return the class-string of a `Collector` (e.g. `Untested::class`, `All::class`) that determines which classes are searchable
- `getClassSearchResults(string $search)` — fuzzy-searches classes by name
- `filterSearchableClasses(Collection): Collection` — optional method to narrow results

### `CreatesColocatedTests`

Adds `--test` / `--no-test` option (defaults to `true`) that generates a co-located test file next to the generated class via `make:test`.

## Building a Custom Command

A generator command has three pieces: a `Reference` value object, a stub, and the command class.

### 1. Create the Reference

A `Reference` derives all naming, path, and namespace info from the command's input. Domain-specific references override `$subNamespace` to place generated files in a sub-namespace beneath the base:

```php
final class RectorRule extends GenericClass
{
    public null|Stringable $subNamespace {
        get => str('Rector\\Rules');
    }
}
```

### 2. Create the Stub

```php
// stubs/rule.stub
<?php

declare(strict_types=1);

namespace {{ namespace }};
```

### 3. Create the Command

Extend `GeneratorCommand`, implement `GeneratesFile`, and use `GeneratorCommandCompatibility` alongside any needed concerns:

```php
#[AsCommand(name: 'make:rector:rule', description: 'Make a new Rector rule')]
class MakeRule extends GeneratorCommand implements GeneratesFile
{
    use CreatesColocatedTests;
    use GeneratorCommandCompatibility;
    use RetrievesNamespace;

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
        $this->resolveNamespace();

        return parent::handle();
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
            ...$this->getNamespaceInputOptions(),
        ];
    }
}
```

If you want the namespace as a positional argument instead of an option (e.g. `make:rule MyRule App\\Rector`), spread `getNamespaceInputArguments()` into `getArguments()` and merge the prompt mapping:

```php
    protected function getArguments(): array
    {
        return [
            new InputArgument('name', InputArgument::REQUIRED, 'The name of the class'),
            ...$this->getNamespaceInputArguments(),
        ];
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            ...$this->getNamespacePromptForMissingArguments(),
        ];
    }
```

## References

A `Reference` is a value object that maps a named, namespaced PHP symbol to its file path. The `Reference` interface declares:

- `$name` — the short name (e.g. `Invoice`)
- `$baseNamespace` — the root namespace from PSR-4 (e.g. `\App`)
- `$subNamespace` — optional sub-namespace beneath the base (e.g. `Rector\Rules`), or `null`
- `$namespace` — computed: `$baseNamespace` + `\` + `$subNamespace` (or just `$baseNamespace` when `$subNamespace` is null)
- `$fqcn` — computed: `$namespace` + `\` + `$name`
- `$directory` — absolute path to the directory the file lives in, resolved via PSR-4
- `$filePath` — absolute path: `$directory` + `/` + `$name` + `.php`

All namespaces follow the invariant: **leading `\`, no trailing `\`** (e.g. `\App\Models`). The `$baseNamespace` property enforces this via a set hook, so callers can pass any format.

The abstract `Reference` base class has a `final` constructor — subclasses cannot override it. This guarantees that `fromFqcn()` can safely construct any subclass via `new static()`.

### `GenericClass`

Represents a PHP class. Accepts `name` and `baseNamespace` in the constructor. Override `$subNamespace` for domain-specific references (e.g. `PhpStanRule`, `RectorRule`).

- `$test: TestClass` — a test companion Reference, derived automatically (appends `Test` to the name)
- `GenericClass::fromFqcn($fqcn)` — static constructor that creates an instance from a fully-qualified class name, resolving the base namespace via longest-prefix matching against the project's PSR-4 autoload entries. When the subclass defines a non-null `$subNamespace`, `fromFqcn()` validates that the FQCN's namespace ends with it and strips it to derive `$baseNamespace`. Throws `\InvalidArgumentException` if the FQCN doesn't match the expected sub-namespace.

### `GenericTrait`

Represents a PHP trait. Same constructor as `GenericClass`. Override `$subNamespace` for domain-specific references.

- `$test: TestClass|TestCasesTrait` — a test companion Reference, which by default creates a `TestCasesTrait` (appends `TestCases` to the name)

### `TestCompanion`

A marker interface (`extends Reference`) implemented by `TestClass` and `TestCasesTrait`. Useful for type-checking whether a Reference is a test companion without coupling to a specific concrete type.

### `TestClass`

A `final class` extending `GenericClass` that implements `TestCompanion`. Represents a test class companion. Self-referential `$test` (returns `$this`).

### `TestCasesTrait`

A `final class` extending `GenericTrait` that implements `TestCompanion`. Represents a test-cases trait companion. Self-referential `$test` (returns `$this`).

## Testing

The package provides reusable test traits for generator commands:

- **`GeneratesFileTestCases`** — asserts the command generates a file with the correct namespace. The test class must define `$baselineInput` (array of artisan input) and a `$reference` property.
- **`ReferenceTestCases`** — co-located with the `Reference` base class in the `References` namespace. Tests each derived property on a `Reference` (name, fqcn, file path, namespace invariant). The test class must implement `TestsReference`, which declares `$subject` and `$expectedName`.
- **`ManagesNamespaceTestCases`** — co-located with the `ManagesNamespace` concern in `References\Concerns`. Tests the `$baseNamespace` set hook invariant (leading `\`, no trailing `\`).
- **`ResolvesPathsTestCases`** — co-located with the `ResolvesPaths` concern in `References\Concerns`. Tests path resolution properties (absolute directory, no trailing slash, file path within directory, `.php` extension).
- **`RetrievesNamespaceTestCases`** — tests namespace resolution with and without trailing backslashes. Requires `$withNamespaceBackslashInput` and `$withoutNamespaceBackslashInput` properties.

## Available Commands

### `make:test`

Creates a test file next to any class in the project.

```bash
php artisan make:test
# or
php ./vendor/bin/testbench make:test
```

Prompts for a class via interactive fuzzy search against the classmap cache (using the `Untested` collector). Generates a test file with `#[CoversClass]`, `#[Test]`, and extending `TestCase`, placed in the same directory as the source class.

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
