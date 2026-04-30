# Tooling for Laravel

Unified Artisan commands with preconfigured rules for [PHPStan](docs/phpstan.md), [Rector](docs/rector.md), and [Laravel Pint](docs/pint.md), plus conveniences for building [generator commands](docs/generator-commands.md).

Each tool ships with an opinionated configuration out of the box. All native CLI arguments and options are forwarded through the Artisan commands, so everything you can do with the underlying tool directly is available here.

## Installation

```bash
composer require aryeo/tooling-laravel
```

> Requires PHP 8.4+

## Configuration

Instruct each tool which paths to analyse using environment variables:

### In a Laravel Application `.env`:
```env
PHPSTAN_PATHS=app,tests
RECTOR_PATHS=app,tests
PINT_PATHS=app,tests
```

### In a Package `testbench.yaml`:
```yaml
env:
  - PHPSTAN_PATHS=src,tests
  - RECTOR_PATHS=src,tests
  - PINT_PATHS=src,tests
```

The config file (`config/tooling.php`) maps these environment variables to the appropriate CLI arguments for each tool and sets the default configuration file paths. You generally don't need to modify it.

## Usage

```bash
php artisan tooling:phpstan:analyze
php artisan tooling:phpstan:cache-clear
php artisan tooling:phpstan:diagnose
php artisan tooling:phpstan:parameters-dump
php artisan tooling:phpstan:bisect
php artisan tooling:pint
php artisan tooling:rector
php artisan tooling:discover
php artisan tooling:optimize
php artisan make:test
php artisan make:rector:rule {name}
php artisan make:phpstan:rule {name}
```

All native CLI options are forwarded. For example:

```bash
php artisan tooling:rector --dry-run
php artisan tooling:phpstan --generate-baseline
php artisan tooling:pint --test
```

### Packages

In a package context, replace `php artisan` with `php ./vendor/bin/testbench`:

```bash
php ./vendor/bin/testbench tooling:phpstan
```

### Discovery

The `tooling:discover` command rebuilds the cached tooling manifest by scanning all installed packages for tooling configurations. The `tooling:optimize` command rebuilds the classmap cache used by generator commands for interactive class search. Both run **automatically** after `composer install` and `composer update` via a Composer plugin — you typically don't need to run them manually.

```bash
php artisan tooling:discover
php artisan tooling:optimize
```

#### How Discovery Works

1. A Composer plugin fires on `post-autoload-dump`
2. It runs `tooling:discover` and `tooling:optimize`
3. Two caches are written to `vendor/aryeo/tooling-laravel/cache/`:
   - `configurations.php` — PHPStan and Rector configurations from all installed packages
   - `classmap.php` — pre-computed class lists for generator command prompts
4. PHPStan and Rector read from the configurations manifest at runtime
5. Generator commands read from the classmap cache for fast interactive search

The classmap cache automatically detects staleness via mtime checks and rebuilds on demand. After any `make:` command, a background process refreshes the cache to pick up newly generated files.

## Extending Tooling

Packages can register their own PHPStan and Rector configurations via `composer.json`. When the package is installed as a dependency, its rules are automatically discovered and loaded.

```json
{
    "extra": {
        "tooling": {
            "rector": {
                "rules": "tooling/rector/rules.php"
            },
            "phpstan": "tooling/phpstan/rules.neon"
        }
    }
}
```

See [PHPStan — Registering Rules](docs/phpstan.md#registering-rules) and [Rector — Registering Rules](docs/rector.md#registering-rules) for details on each format.

## Considerations

### Fixtures Namespace

Sometimes test fixtures purposefully violate domain-specific rules. To avoid false failures in PHPStan or unintended corrections by Rector, classes within a `Fixtures` namespace anywhere in `Tests` should be excluded from your rules.

It is your responsibility to account for this in any custom rules you write:

```php
$className = $node->namespacedName?->toString() ?? '';

if (str($className)->is('Tests\\*Fixtures\\*')) {
    return [];
}
```

The base rule classes documented in [PHPStan](docs/phpstan.md) and [Rector](docs/rector.md) do not enforce this automatically — the check belongs in your `shouldHandle()` method where contextually appropriate.
