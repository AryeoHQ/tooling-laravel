# `canvural/larastan-strict-rules` under Laravel 13 / PHP 8.5 / PHPStan 2.2

## Summary

`canvural/larastan-strict-rules` was believed to be the one hard blocker for supporting
Laravel 13 and PHP 8.5: its latest release is `3.0.3` (tagged 2025-04-29) and its upstream
CI matrix runs **PHP 8.0 and 8.1 only**, with no verification against PHP 8.4, PHP 8.5,
Laravel 13, larastan 3.10 or PHPStan 2.2.

**It is not a blocker. Its rules are never loaded, so its staleness has no effect.**

The package should be removed from `composer.json`.

## Evidence

An exhaustive search for `canvural` across every `.neon`, `.php`, `.json`, `.yml` and `.md`
in the repository — excluding `vendor/` and `composer.lock` — returns exactly two hits:

1. `composer.json` — the `require` entry itself.
2. `composer-dependency-analyser.php:9` —

   ```php
   ->ignoreErrorsOnPackage('canvural/larastan-strict-rules', [ErrorType::UNUSED_DEPENDENCY])
   ```

The second hit is the important one: **the project's own dependency analyser already
classified this package as an unused dependency, and the finding was suppressed rather than
acted on.**

The PHPStan include chain cannot reach the package:

```
phpstan.neon
  └── tooling/phpstan/larastan/larastan.neon
        └── loader.neon.php          (conditional on artisan/testbench being present)
              └── config.neon
                    └── vendor/larastan/larastan/extension.neon
```

Nothing in that chain, and nothing in `tooling/phpstan/services.neon`, references
`canvural`. The only "strict rules" that *are* active come from a different package,
`phpstan/phpstan-strict-rules`, included at `tooling/phpstan/phpstan/phpstan.neon:4`.

## Verified target stack

The intended combination resolves and runs. On PHP **8.5.8** with `laravel/framework ^13.0`:

| Package | Resolved |
|---|---|
| `laravel/framework` | 13.23.0 |
| `symfony/console` | 8.1.1 |
| `phpstan/phpstan` | 2.2.6 |
| `larastan/larastan` | 3.10.0 |
| `orchestra/testbench` | 11.1.0 |
| `nikic/php-parser` | 5.8.0 |
| `laravel/pint` | 1.30.0 |
| `phpunit/phpunit` | 12.5.33 |
| `brianium/paratest` | 7.20.0 |
| `canvural/larastan-strict-rules` | 3.0.3 (installed, never loaded) |

`composer update` reports `No security vulnerability advisories found`, and
`testbench tooling:discover` exits 0.

## Action

Remove the dependency:

- delete `"canvural/larastan-strict-rules": "^3.0"` from `composer.json`
- delete the now-redundant `ignoreErrorsOnPackage(...)` line from `composer-dependency-analyser.php`
- regenerate `composer.lock`

Confirm `tooling:phpstan` reports an identical set of findings before and after, which
demonstrates the rules were never contributing.

## Two blockers found while running this spike

Neither is caused by this package; both are recorded separately.

### Parallel PHPStan is unreliable

`tooling:phpstan` in its default parallel mode — what CI runs — intermittently fails with
`Internal error: Tooling\Rector\Console\Commands\Process while analysing file …`, and when
it does not fail it reports **6 errors where single-process mode reports 9**.

Root cause is the collision already documented in `rector-autoloader-collision.md`, reached
by a new path:

```
src/Tooling/Rector/Console/Commands/Process.php
  → use Tooling\Rector\Console\Inspectors
       → Inspectors/Process.php:7   use Rector\Console\Command\ProcessCommand
```

Analysing that file makes larastan resolve a `Rector\*` class inside PHPStan's container,
which triggers Rector's `spl_autoload` callback, whose `static $composerAutoloader` is
poisoned to an `int` and never reset. Which parallel worker reaches a `Rector\*` class
first determines whether it manifests, hence the intermittency.

### PHPStan 2.2 is already what the constraints resolve to

Pinning `phpstan/phpstan: ^2.1.47` and `larastan/larastan: ^3.8` — the constraints currently
declared — still resolves **phpstan 2.2.6** and **larastan 3.10.0**, because `^2.1.47` means
`>=2.1.47 <3.0.0`.

`static-analysis` runs `composer install` against `composer.lock`, which pins `2.1.54`, so it
analyses on 2.1.x today and is green. Regenerating the lock moves it to 2.2.6 and releases 9
pre-existing findings. They are latent debt, not a consequence of Laravel 13 or PHP 8.5.

## Method

All PHP tooling ran containerised, pulled through the ECR Docker Hub pull-through cache
(`412210170733.dkr.ecr.us-east-1.amazonaws.com/docker-hub/library/...`); no DHI images.
Image: `php:8.5-cli` + `composer:2.10`, with `mbstring zip pcntl sockets intl` added.
Analysis ran against the merged `main` + PR #57 tree (`git merge-tree` → `2090db0`), which is
the state the planned rebase produces.
