# Tooling for Laravel
Unified Artisan commands and with preconfigured rules for PHPStan, Rector, and Laravel Pint.

## Installation
```bash
composer require aryeo/tooling-laravel
```

## Configuration
You can instruct each tool which paths to inspect using:

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

## Usage
The commands you use depend on your context:

### In a Laravel Application:
```bash
php artisan tooling:phpstan
php artisan tooling:pint
php artisan tooling:rector
```

### In a Package:
```bash
php ./vendor/bin/testbench tooling:phpstan
php ./vendor/bin/testbench tooling:pint
php ./vendor/bin/testbench tooling:rector
```

## Extending Tooling
Additional configurations can be registered via `composer.json`:

> Note: Packages that register configurations will be automatically available when installed as a dependency.

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

### Considerations
Sometimes it is necessary for fixtures to purposefully violate domain specific PHPStan rules. Similarly, Rector rules that automatically fix these PHPStan violations for developer convenience may also exist.

However, this will either incorrectly cause PHPStan (and by extension CI) to fail or override the developers intent of creating an "incorrect" setup for the purposes of testing.

To provide affordance for these testing scenarios, we have adopted the standard that classes within a `Fixtures` namespace anywhere in `Tests` should be ignored for PHPStan & Rector rules. It is your responsibility to write your rules to account for this affordance if appropriate.

#### Example
```php
$className = $node->namespacedName?->toString() ?? '';

if (str($className)->is('Tests\\*Fixtures\\*')) {
    return [];
}
```
