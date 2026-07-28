# AI Artifacts Plan

Build our own cross-provider publishing system modeled on Laravel Boost's patterns —
interface-per-capability providers, config-overridable paths, canonical-author-once,
filesystem discovery — without depending on Boost. Scope is skills and agents (with a hooks
seam); guidelines and plugins are deferred. The MCP story stays in the `mcp` package;
artifacts live in `tooling-laravel`.

## Background

There are two participation stories in scope, plus one deferred:

1. **MCP** — the existing russian doll in the `mcp` package. Packages register
   Tool/Resource/Prompt into a named server (e.g. `Development`) via `Server::add()`; the
   registrar drains them into the server at boot. Served live over JSON-RPC. This stays as
   is; the only addition is making it enumerable.
2. **Agents / Skills / Hooks** — these are not MCP-servable. MCP only has three server
   primitives (tools, resources, prompts), and neither the protocol nor `laravel/mcp` has any
   concept of skills/agents/hooks. They are provider-specific filesystem artifacts that each
   AI client reads from conventional directories.
3. **Plugins (deferred)** — a bundle composing selected features from stories 1 and 2. Cut
   from this project's scope; see "Future considerations" for why and what seam to preserve.

Boost already solves cross-provider publishing for guidelines + skills + MCP config, with
third-party package discovery. We are borrowing its conventions but building our own system
so we own the model and aren't coupled to Boost's cadence.

## Key constraints

- LLM clients discover skills/agents/hooks by reading files from conventional in-project
  locations. There is no wire protocol for them, so the files must physically exist per
  project. That is how the whole ecosystem works, not a flaw in this design.
- Materialized files are a cache of vendor sources. The real problem to solve is **drift**
  (a package ships an updated skill and the published copy goes stale), not duplication.
  Treat materialized output as a reproducible, gitignored build artifact.

## Architecture

- **Providers** (Boost-style, ours): one object per AI assistant (start with Copilot and
  Claude Code). Capabilities are opt-in via segregated interfaces — `SupportsSkills`,
  `SupportsAgents`, `SupportsMcp` (and a `SupportsHooks` seam we design but likely defer).
  Each declares where its artifacts land, config-overridable with sensible defaults
  (Copilot skills `.github/skills`, mcp `.vscode/mcp.json`; Claude skills `.claude/skills`,
  mcp `.mcp.json`).
- **Canonical artifacts**: author once as value objects in a canonical directory (optional
  Blade templating like Boost's `SKILL.blade.php`), then translate per provider via per-kind
  writers.
- **Discovery by convention**: scan installed composer packages for
  `resources/<vendor>/{skills,agents}` directories, feeding the catalog. This is the artifact
  version of the russian doll — packages contribute by shipping files, not by registering PHP
  objects.
- **Config-driven selection + sync**: a project declares which discovered skills/agents and
  which exposed MCP servers it wants (config), and an on-demand sync command materializes that
  selection into each provider's paths. A manifest (`{source, version, hash}`) detects
  staleness, with optional composer-lifecycle auto-republish via tooling-laravel's existing
  composer plugin. There is no interactive picker and no bundling in this scope.

## Steps

### Phase 1 — `mcp`: enumeration only

The sync command needs to answer "what MCP features exist?" so it can materialize client
config for them. Today the `mcp` package's `Registrar` can only look up primitives for a
server it already knows the class name of (`for($server)`); it can't tell you which servers
exist or give you a full picture. Everything else in `mcp` stays untouched — this is purely
additive read access.

**Current shape** (`mcp/src/Servers/Registrar/Registrar.php`):

- `public private(set) array $registrations` keyed by server class, each holding an array of
  `class-string<Tool>|Primitive`.
- `register(string $server, string|Primitive $primitive): static` — write path, called by
  `Server::add()`.
- `for(string $server): array` — read primitives for one known server.

**What to add:**

1. `servers(): array` — return the list of registered server class-strings
   (`array_keys($this->registrations)`). This is what lets the sync command enumerate servers
   without being told their names up front.
2. `all(): array` — return the full `server => primitives` map. Convenience for enumerating
   the whole tree in one pass, and for the sync manifest to hash what's registered.
3. Keep `for()` as the single-server accessor; `servers()` + `for()` compose, and `all()` is
   sugar over both.

**Notes and edge cases:**

- **Read-only, no behavior change.** `boot()` still drains via `for(static::class)`; adding
  enumeration does not alter registration or draining order. The existing lifecycle
  (`add()` anytime → `boot()` drains → `createContext()` snapshots) is unchanged.
- **Registration must have happened first.** The sync command reads the registrar at command
   runtime, which is after all providers' `boot()` have run, so `add()` calls have already
   populated it. This is the same ordering guarantee the server relies on, so no new timing
   risk — but sync must run in a booted application, not mid-provider-registration.
- **Primitives may be class-strings or instances.** `registrations` holds
  `class-string<Tool>|Primitive`, so any consumer (sync command, manifest) must handle both:
  resolve/normalize to a stable identity (e.g. the primitive's `name()` or the class name)
  when displaying or hashing. Worth a small helper so the sync command and the manifest agree
  on identity.
- **Enumeration is not kind-aware yet.** `registrations` mixes tools/resources/prompts in one
  list per server. If a consumer wants to group by kind, it derives the kind the same way
  `Server::registerPrimitive()` does (`is_subclass_of` against `Tool`/`Resource`/`Prompt`).
  Consider exposing a tiny read-only kind resolver from `mcp` so consumers don't re-implement
  that `match`, keeping the classification logic in one place.
- **Facade/consumption.** tooling-laravel resolves the `Registrar` singleton (already bound
  in `mcp`'s provider) to call `servers()`/`all()`. No new binding needed in `mcp`; the
  dependency stays one-way (tooling-laravel → `mcp`).

**Decision — the offerable set is the intersection of "has primitives" and "is exposed".** An
MCP server is worth syncing into a project only if it satisfies both:

1. **Has at least one primitive** — from our custom registrar. A primitive-less server
   exposes nothing over the wire (`tools/list`/`resources/list`/`prompts/list` all empty), so
   there is nothing to offer. This filter is registration-derived by construction:
   `servers()` returns only keys of `registrations`, so empty servers are excluded
   automatically, not overlooked.
2. **Is actually exposed via `Mcp::local(...)` / `Mcp::web(...)`** — from the vendor
   `Laravel\Mcp\Server\Registrar`. This matters because a project's MCP contribution is not the
   server object; it is *client config* (`.vscode/mcp.json`, `.mcp.json`, …) telling the AI
   client how to reach the server — a command for local/stdio, or a URL for web. That
   reachability data lives only in the vendor registrar (keyed by handle/route). If a package
   called `Server::add(...)` but nobody wired the server via `local()`/`web()`, there is no
   handle/route and therefore no valid client-config entry to emit. Such a server must be
   excluded.

So, revising an earlier assumption: we *do* need the vendor registrar after all — not to pad
the list with empty servers, but to (a) confirm the server is reachable and (b) obtain the
handle/route to write into the client config. The offerable set is the intersection of the
two registrars' knowledge.

**Correlation caveat:** our custom registrar keys by **server class**, the vendor registrar
keys by **handle/route** (`servers()`, `getLocalServer($handle)`, `getWebServer($route)`). The
sync command must correlate class ↔ handle/route to both filter and emit config. This
correlation is unavoidable because the client config needs the handle/route regardless.

**Verification for this phase:** register a couple of primitives against the `Development`
server in a test, then assert `servers()` contains `Development::class` and `all()` returns
the expected `server => [primitives]` shape, with both a class-string and an instance
primitive normalized to the same identity. Also assert that (a) a server with no registered
primitives does not appear in `servers()`, and (b) the offerable set excludes a server that
has primitives but was never exposed via `local()`/`web()`.

### Phase 2 — Provider model
2. In a new `Tooling\Ai\*` module, define a `Provider` abstraction and per-kind capability
   interfaces: `SupportsSkills` (`skillsPath()`), `SupportsAgents` (`agentsPath()`),
   `SupportsMcp` (`mcpConfigPath()`, `mcpConfigKey()`, `mcpServerConfig()`,
   `httpMcpServerConfig()`). Paths config-overridable with defaults. _Depends on nothing._
3. Implement a starting subset of concrete providers — Copilot and Claude Code first.
   _Depends on 2._

### Phase 3 — Canonical artifacts + writers
4. Canonical value objects `Skill` and `Agent`, authored once in a canonical directory with
   optional Blade templating. _Depends on 2._
5. Per-kind writers (`SkillWriter`, `AgentWriter`, `McpWriter`) that translate canonical
   definitions into each selected provider's path. _Depends on 3, 4._

### Phase 4 — Discovery
6. A `Catalog` plus a discovery scan of installed composer packages for
   `resources/<vendor>/{skills,agents}` directories, feeding the sync command. _Depends on 4._

### Phase 5 — Selection + sync/drift
7. Config-driven selection: a project declares which discovered skills/agents and which
   exposed MCP servers it wants. Absent config, sync everything discovered/offerable.
   _Depends on 4, 6._
8. An on-demand `sync` artisan command that reads the config selection, the MCP enumeration
   (Phase 1), and the catalog (Phase 4), then drives the writers to materialize the selection
   into each selected provider's paths. _Depends on 1, 5, 6, 7._
9. A manifest (`{source, version, hash}`) for staleness detection, plus optional
   composer-lifecycle auto-republish via the existing composer plugin. _Depends on 8._

## Relevant files

- `mcp/src/Servers/Registrar/Registrar.php` — add enumeration (only `mcp` change).
- New in tooling-laravel:
  - `src/Tooling/Ai/Providers/{Provider,Copilot,ClaudeCode}.php`
  - `src/Tooling/Ai/Contracts/{SupportsSkills,SupportsAgents,SupportsMcp}.php`
  - `src/Tooling/Ai/{Skill,Agent}.php`
  - `src/Tooling/Ai/Writers/{SkillWriter,AgentWriter,McpWriter}.php`
  - `src/Tooling/Ai/Catalog.php`
  - `src/Tooling/Ai/Console/Commands/Sync.php`
- `src/Tooling/Provider.php` — bind the new singletons and register the command.
- `src/Tooling/Composer/Plugins/PublishConfigurations.php` — optional auto-republish hook.

## Decisions

- Own system, Boost conventions borrowed, no Boost dependency.
- Guidelines deferred. Skills and agents first. Hooks seam designed but likely deferred, since
  cross-provider hook standards are weak.
- Providers are interface-per-capability objects with config-overridable paths.
- Artifacts are author-once canonical, translated by per-provider writers; discovery is by
  filesystem convention.
- MCP stays in the `mcp` package; the dependency direction is one-way
  (tooling-laravel depends on `mcp`, never the reverse).
- Materialized output is a gitignored, reproducible build artifact; drift is handled by
  on-demand re-sync plus an optional composer-lifecycle hook.
- Selection is config-driven, not an interactive picker; the sync command only reads the
  registries, it never registers.
- Plugins (bundling/composition) are out of scope for this project; the seam is preserved so
  they can be added later without rework.

## Verification

1. `./vendor/bin/testbench tooling:pint`, `tooling:phpstan`, `tooling:rector --dry-run`, and
   `./vendor/bin/phpunit` all green.
2. Register a sample skill in a fake package fixture, confirm discovery finds it, and the
   sync command materializes the correct files at both the Copilot and Claude paths.
3. Confirm the MCP enumeration surfaces the `Development` server (and excludes empty or
   unexposed servers) and that sync writes correct client config for it.
4. Confirm the sync command rewrites drifted artifacts and the manifest flags a
   bumped-version fixture as stale.

## Out of scope

- Guidelines.
- Plugins (bundling/composition, interactive builder, archives).
- The full provider matrix (start with Copilot and Claude Code).
- Exact hook file formats.
- Wiring `mcp` into tooling-laravel's composer require.

## Future considerations — plugins

Plugins (a distributable bundle composing selected skills/agents/MCP servers) are cut from
this project because they are the least-settled, highest-risk piece and nothing else depends
on them. Every plugin question spawned an unresolved sub-problem — transport portability
(bundling `local()` vs `web()` servers), URL parameterization, recording each MCP server's
providing composer package, distribution scope, and archive-vs-local rendering — none of
which affect the artifact layer. The provider/writer/catalog/enumeration work here is a
strict prerequisite for plugins, so building it first loses nothing. Claude Code plugins and
marketplaces are also young and shifting, so waiting avoids reinventing a format the
providers may standardize.

To keep the seam clean for a later plugin layer: the sync command consumes selection +
registries but does not assume the selection came from config, so a future picker or plugin
manifest can supply the same selection shape; the MCP offerable-set logic (intersection of
primitives + exposure, with class ↔ handle/route correlation) already produces exactly the
data a plugin's MCP client config would need.

## Open questions

1. **Hooks in v1?** Lean toward deferring — design the `SupportsHooks` seam but don't
   implement.
2. **Module namespace** — `Tooling\Ai\*` (recommended) versus `Tooling\Artifacts\*`.
