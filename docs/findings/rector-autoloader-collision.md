# Rector Autoloader Collision in PHPUnit

## Summary

When running the full PHPUnit suite, tests extending PHPStan's `RuleTestCase` that load fixtures referencing `Rector\*` classes poison Rector's internal autoloader. This causes all subsequent tests that depend on Rector classes (e.g. `AbstractRector`) to fail with `Class "Rector\Rector\AbstractRector" not found`.

## Root Cause

Rector ships with a custom `spl_autoload` callback registered in `vendor/rector/rector/bootstrap.php`:

```php
spl_autoload_register(function (string $class): void {
    static $composerAutoloader; // set ONCE, never reset

    if (defined('__RECTOR_RUNNING__')) {
        return;
    }

    if (strpos($class, 'Rector\\') === 0) {
        if ($composerAutoloader === null) {
            $composerAutoloader = require __DIR__ . '/vendor/autoload.php';
        }

        // "some weird collision with PHPStan custom rule tests"
        if (! is_int($composerAutoloader)) {
            $composerAutoloader->loadClass($class);
        }
    }
});
```

The key detail is the **static** `$composerAutoloader` variable. It is assigned exactly once — the first time any `Rector\*` class is autoloaded — and never reset.

### The Sequence

1. **Composer loads `bootstrap.php` early** — the autoloader is registered with `$composerAutoloader = null`.
2. **PHPUnit discovers `RuleMustExtendRuleTest extends RuleTestCase`** — PHPStan's `RuleTestCase` boots a full PHPStan DI container with its own autoloading context.
3. **`analyse()` processes a fixture that references `Rector\Rector\AbstractRector`** — this triggers the `spl_autoload` callback for a `Rector\*` class for the first time.
4. **`require __DIR__ . '/vendor/autoload.php'` returns an `int`** inside PHPStan's container context instead of a `Composer\Autoload\ClassLoader` instance (this is a known issue — Rector's own code comments acknowledge the "weird collision with PHPStan custom rule tests").
5. **`$composerAutoloader` is permanently set to that int** — the static variable is only assigned when `=== null`, so it can never be corrected.
6. **The `is_int()` guard skips `loadClass()`** — no error is thrown, but the class silently fails to load.
7. **Later tests run** — `AddInterfaceByTraitTest` and `AddTraitByInterfaceTest` call `app(AddInterfaceByTrait::class)`, which resolves `AddInterfaceByTrait → Rule → AbstractRector`.
8. **The autoloader fires again for `Rector\Rector\AbstractRector`**, but `$composerAutoloader` is still the int — `loadClass()` is permanently skipped → **"Class not found"**.

### Why Only This Test

The `PHPStan/PHPStan/RuleMustExtendRuleTest` (for PHPStan rules) does **not** trigger this because its fixtures only reference PHPStan classes (`\PHPStan\Rules\Rule`), never `Rector\*` classes. Rector's autoloader callback is never invoked inside PHPStan's container for that test.

The collision is specific to `PHPStan/Rector/RuleMustExtendRuleTest` because it is a PHPStan `RuleTestCase` whose fixtures `extend AbstractRector` — a `Rector\*` class.

## Fix

Each test method in `RuleMustExtendRuleTest` (the Rector variant) is annotated with `#[RunInSeparateProcess]`. This runs each test in a child PHP process with a fresh autoloader state, preventing the static variable from poisoning the parent process.

```php
#[Test]
#[RunInSeparateProcess]
public function it_passes_when_rule_extends_base_rule(): void
{
    $this->analyse([...], []);
}
```

## Conclusion: No Impact on Downstream Consumers

This collision is **entirely contained within this package's own test suite** and will never affect downstream consumers. Here is why:

1. **Runtime isolation.** PHPStan and Rector always run in separate processes in production (`vendor/bin/phpstan analyse` and `vendor/bin/rector process`). Their autoloaders never coexist in the same process at runtime.

2. **Consumers will never write this kind of test.** The triggering condition is a PHPStan `RuleTestCase` that analyses fixtures referencing `Rector\Rector\AbstractRector`. This only happens because this package contains a meta-rule — a PHPStan rule that inspects whether Rector rules extend the correct base class. Consumers of this package write Rector rules and PHPStan rules for their *application code* (models, controllers, services), not rules that inspect other tooling rules.

3. **Consumer Rector rule tests don't use `RuleTestCase`.** Consumers test their custom Rector rules by extending `TestCase`, resolving the rule via `app()`, and calling `refactor()` directly. PHPStan's container is never involved, so the autoloader collision cannot occur.

4. **Consumer PHPStan rule tests analyse application fixtures.** When consumers write PHPStan rules and test them with `RuleTestCase`, their fixtures are application classes — they never reference `Rector\*` classes, so Rector's autoloader callback is never triggered inside PHPStan's container.

The `#[RunInSeparateProcess]` annotation is a localized fix for exactly one test class in this package, with no downstream implications.
