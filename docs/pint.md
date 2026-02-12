# Pint

This package ships a preconfigured [Laravel Pint](https://laravel.com/docs/pint) setup with a curated set of rules on top of the `laravel` preset.

## What's Included

The bundled `pint.json` uses the **Laravel preset** with the following additional rules:

| Rule | Configuration | Purpose |
|------|---------------|---------|
| `no_unused_imports` | — | Removes unused `use` statements |
| `ordered_attributes` | `sort_algorithm: alpha` | Sorts PHP attributes alphabetically |
| `nullable_type_declaration` | `syntax: union` | Uses `Type\|null` instead of `?Type` |
| `single_trait_insert_per_statement` | — | One `use` statement per trait |
| `no_extra_blank_lines` | Tokens: `curly_brace_block`, `extra`, `parenthesis_brace_block`, `square_brace_block`, `throw`, `use` | Removes unnecessary blank lines in specific contexts |
| `attribute_empty_parentheses` | `use_parentheses: false` | `#[Test]` instead of `#[Test()]` |

## Configuration

### Paths

Set `PINT_PATHS` in your `.env` or `testbench.yaml`:

```env
PINT_PATHS=app,tests
```

This maps to the `tooling.pint.cli.arguments.path` config key.

### Configuration File

By default, the bundled `pint.json` is used. This is set via `tooling.pint.cli.options.config` in `config/tooling.php`.

### CLI Passthrough

All native Pint CLI options work through the Artisan command:

```bash
php artisan tooling:pint --test
php artisan tooling:pint --dirty
php artisan tooling:pint --preset=per
```

## Extending

Unlike PHPStan and Rector, Pint does not have an extension mechanism through this package's discovery system. Pint uses its own `pint.json` configuration file directly. If you need a different configuration, override the config path via `tooling.pint.cli.options.config` or pass `--config` at the command line.
