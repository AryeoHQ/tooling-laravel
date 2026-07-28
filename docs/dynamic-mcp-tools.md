# Dynamic MCP Tools from Artisan Commands

Dynamically register MCP Tools into the `Development` server by introspecting all registered Artisan commands. Each command becomes a `Tool` instance with name, title, description, and schema derived from the command's `InputDefinition`.

## Phase 1: Registrar + Server support for Tool instances ✅

The upstream `ServerContext::resolvePrimitives()` already handles both class-strings and instances. But our `Registrar` and `Server::add()` only accept strings.

1. ✅ Update `Registrar` to store `string|Primitive` (not just class-strings)
2. ✅ Update `Server::add()` signature to accept `string|Primitive|array`
3. ✅ Update `Server::registerPrimitive()` to handle instances via `instanceof` checks alongside the existing `is_subclass_of` string checks

## Phase 2: Artisan command → MCP Tool factory ✅

4. ✅ Create `Mcp\Tools\ArtisanToolFactory` — takes a `Command`, returns an anonymous `Tool` instance:
   - **name** → `artisan_` prefix + command name with `:` replaced by `_` (e.g. `tooling:phpstan` → `artisan_tooling_phpstan`)
   - **title** → command description
   - **schema** → mapped from `InputArgument[]` + `InputOption[]`:
     - `VALUE_NONE` options → `$schema->boolean()->default(false)`
     - `VALUE_REQUIRED` options → `$schema->string()`
     - `VALUE_OPTIONAL` / arguments → `$schema->string()` with default
     - `VALUE_IS_ARRAY` → `$schema->array()->items($schema->string())`
     - Skip framework boilerplate options (`--help`, `--quiet`, `--verbose`, `--version`, `--ansi`, `--no-ansi`, `--no-interaction`, `--env`)
   - **handle()** → `Artisan::call($commandName, $mappedParams)` → `Response::structured()`

## Phase 3: Registration in Provider ✅

5. ✅ In `Provider::bootMcp()`, iterate `Artisan::all()`, filter to non-hidden `Command` instances, create tools via factory, register with `Development::add()`
6. Concrete Tool classes kept alongside for now (not removed)

## Phase 4: Default to structured output

All custom `tooling:*` commands should expose a single `--json` option that switches their output to machine-readable JSON. This unifies the surface area — the factory only needs to manage one option name (`json`) instead of tracking `--error-format`, `--format`, `--output-format`, `--no-progress`, `--no-progress-bar` per command.

### Command-side changes

Each tooling command adds `--json` and `--no-progress` as standardized `VALUE_NONE` boolean options. Internally each command maps these to whatever underlying flags it needs:

| Command | `--json` internally sets | `--no-progress` internally sets |
|---|---|---|
| `tooling:phpstan` | `--error-format=json` | `--no-progress` (already native) |
| `tooling:pint` | `--format=json` | no-op (no progress output) |
| `tooling:rector` | `--output-format=json` | `--no-progress-bar` |

Laravel's built-in commands (`about`, `route:list`, `model:show`, etc.) already have `--json` natively — no changes needed there.

### Factory-side changes

`ArtisanToolFactory` only needs:

| Constant | Contents | Purpose |
|---|---|---|
| `DEFAULT_OVERRIDES` | `['json' => true, 'no-progress' => true]` | Default both to `true` in the schema |
| `HIDDEN_OPTIONS` | `['error-format', 'format', 'output-format', 'output-to-file', 'no-progress-bar']` | Hide underlying format/progress options the LLM doesn't need |

### Implementation

7. Add `--json` and `--no-progress` options to `tooling:phpstan`, `tooling:pint`, and `tooling:rector` commands — each maps them to their specific underlying flags
8. Add `DEFAULT_OVERRIDES` and `HIDDEN_OPTIONS` constants to `ArtisanToolFactory`
9. Add `HIDDEN_OPTIONS` to the reject filter alongside `SKIP_OPTIONS`
10. In schema generation, when an option's name matches a `DEFAULT_OVERRIDES` key, use the override value as the schema default

## Resolved Questions

1. **Scope**: All non-hidden `Illuminate\Console\Command` instances (not just `tooling:*`)
2. **Replace vs coexist**: Concrete Tool classes kept alongside. The forced-options approach generically replicates their custom `handle()` logic.
3. **Name format**: `artisan_` prefix + snake_case (`artisan_tooling_phpstan`)

## Relevant Files

- `src/Mcp/Servers/Registrar/Registrar.php` — ✅ supports instances
- `src/Mcp/Servers/Server.php` — ✅ supports instances
- `src/Mcp/Tools/ArtisanToolFactory.php` — needs Phase 4 changes
- `src/Tooling/Provider.php` — ✅ dynamic registration
