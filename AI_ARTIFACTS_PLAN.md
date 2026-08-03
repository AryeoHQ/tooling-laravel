# AI Artifacts Plan

Build our own cross-provider publishing system for skills and agents, modeled on Laravel
Boost's patterns — interface-per-capability providers, config-overridable paths,
canonical-author-once, discovery via composer metadata — without depending on Boost. Includes
a hooks seam; guidelines and plugins are deferred. This lives in `tooling-laravel`.

## Background

Skills and agents are provider-specific filesystem artifacts: each AI client (Copilot, Claude
Code, …) reads them from its own conventional directory. A package can ship canonical
definitions once, and tooling publishes them into every provider's expected location.

Boost already solves this for guidelines + skills, with third-party package discovery. We are
borrowing its conventions but building our own system so we own the model and aren't coupled
to Boost's cadence.

Plugins — a distributable bundle composing selected artifacts — are deferred; see
"Future considerations" for why and what seam to preserve.

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
  `SupportsAgents` (and a `SupportsHooks` seam we design but likely defer). Each declares where
  its artifacts land, config-overridable with sensible defaults (Copilot skills
  `.github/skills`; Claude skills `.claude/skills`). The interfaces are deliberately asymmetric
  because the two artifact kinds differ in how much a provider actually varies:
  - `SupportsSkills` is essentially **path-only**. Skills are a cross-provider open standard
    (agentskills.io — `SKILL.md` with `name`/`description` frontmatter, same everywhere), so
    the rendered skill is byte-identical across providers and only the destination directory
    changes. `skillsPath()` is the whole per-provider surface.
  - `SupportsAgents` is **path + a frontmatter projection**. Agents have no open standard: the
    body (system prompt) and the common fields (`name`, `description`) are provider-agnostic,
    but `tools`, `model`, the file extension, and the location diverge (see the agent-shape
    bullet). So it exposes `agentsPath()` **and** a transform that projects the canonical
    superset frontmatter into the provider's native syntax.
  - We build the provider plumbing now even though today's two providers overlap (VS Code reads
    Claude-format agents/skills, so a naive impl could target just `.claude/`). The seam is the
    point: adding a third provider, or emitting native Copilot artifacts, is then additive.
- **Author toward the superset**: canonical artifacts carry the **union** of what any provider
  understands, expressed in a neutral shape, and each provider projects *down* to its native
  output — never the reverse. Concretely for agents: the canonical frontmatter names `tools` as
  a logical list and `model` as a logical alias (or omits `model` to inherit); the ClaudeCode
  provider emits a comma-separated `tools` string + Claude model vocab into `.claude/agents/`,
  and the Copilot provider emits a YAML-array `tools` + qualified model names (and may carry
  Copilot-only fields like `handoffs`) into `.github/agents/*.agent.md`. Authoring once against
  the superset is the only thing that makes "write once, publish natively to every provider"
  true rather than "write in one provider's dialect and hope the others tolerate it."
- **Canonical artifacts are always Blade**: every skill/agent is authored as a `.blade.php`
  file — there is no plain `.md` variant. Blade is a strict superset of Markdown, so a static
  artifact is just a `.blade.php` with no directives (`Blade::render()` returns it unchanged).
  Forcing Blade removes the ceremony of the static→dynamic transition: when a currently-static
  artifact needs a config value (e.g. a resource-generation skill emitting the project's
  actual configured namespace via `{{ config('api-first.namespace') }}`), the author just adds
  the interpolation — no file rename, no updating the `extra.tooling.ai` path reference, no
  manifest/history churn that a `.md` → `.blade.php` switch would force. This diverges from
  Boost (which supports both) deliberately: the authoring model prioritizes seamless
  static→dynamic evolution over matching Boost's extension handling. Rendering rules:
  - Render with `Blade::render($contents, $data)`, **not** `Blade::markdown()`. `markdown()`
    runs the compiled output through a CommonMark→HTML converter and would emit HTML; we want
    Markdown out. `render()` returns the raw rendered text.
  - One render pass over the **whole file** (frontmatter + body together) — no need to split
    frontmatter from body at render time. Blade treats frontmatter lines as plain text and
    interpolates `{{ }}` wherever they appear.
  - **Then** parse frontmatter from the rendered string (render-before-parse ordering).
  - Escaping caveat: `{{ }}` HTML-escapes (`'` → `&#039;`, `&` → `&amp;`), which corrupts YAML
    frontmatter values. Authors must use `{!! !!}` (raw) for anything interpolated into
    frontmatter. Static prose that contains literal `@` or `{{` uses Blade's normal escapes
    (`@@`, `@{{`). Documented authoring rules, not architecture concerns.
- **Artifact shapes differ — agent = single file, skill = directory**: an agent is one
  `.blade.php` file (both Claude Code sub-agents and Copilot custom agents are a single
  Markdown file with YAML frontmatter — never a directory of supporting materials; they compose
  other artifacts by *reference* via `skills:`/`mcpServers:` frontmatter, not co-located files).
  A skill is a **directory**: a required `SKILL.blade.php` entry plus optional supporting
  material in subdirectories like `references/`, `scripts/`, `assets/`.
  - **Skill naming invariant**: the Agent Skills spec (and Copilot) require the `name`
    frontmatter value to equal the skill's directory name, and restrict it to 1–64 chars,
    lowercase alphanumeric + single hyphens (no leading/trailing/consecutive hyphens). A
    mismatch or invalid character makes the skill silently fail to load. So the canonical skill
    directory name is the source of truth and `MakeSkill` must validate it and keep the
    `name` frontmatter in sync. Required skill frontmatter is just `name` + `description`;
    `license`, `compatibility`, `metadata`, `allowed-tools` are optional passthrough.
  - **Agent frontmatter is a projected superset, not passthrough**: because `tools`/`model`
    diverge across providers (comma-string vs YAML array; `inherit`/`sonnet` vs
    `"GPT-5.2 (copilot)"`), the canonical agent frontmatter is neutral and each provider's
    `SupportsAgents` transform renders it natively. Required canonical fields are `name` +
    `description`; `tools`/`model` are optional and, when omitted, agents stay trivially
    portable (Claude `model` defaults to `inherit`).

  The supporting-materials handling below applies to **skills only**:
  - The whole skill directory is materialized **recursively** into each provider's skills path
    (`.github/skills/<name>/`, `.claude/skills/<name>/`), preserving `references/`, `scripts/`,
    etc.
  - Rendering is decided **per file by extension**: any `.blade.php` in the tree renders via
    `Blade::render()` and drops the `.blade.php` extension on output (`SKILL.blade.php` →
    `SKILL.md`, `references/usage.blade.php` → `references/usage.md`); every other file
    (`.sh`, `.png`, already-`.md`, …) is copied verbatim, never rendered. This lets reference
    docs be templated too (e.g. a reference pulling `{{ config(...) }}`) while binaries and
    scripts pass through untouched.
  - Internal links are authored against the **source** filename (`references/usage.blade.php`)
    so links resolve in the package's own repo (click-through works, link validators pass),
    and the writer **rewrites them to the output name** (`references/usage.md`) at publish. The
    rewrite is **link-target-scoped**, not a blanket string replace: only the target of a
    Markdown link/image (`[...](target)` / `![...](target)`) is rewritten, and only when the
    target resolves to a sibling `.blade.php` that actually exists in the skill tree and was
    rendered. A bare `.blade.php` occurring in prose (e.g. "author your skill as
    `SKILL.blade.php`") is left untouched — this is why it must be link-scoped, not
    `str_replace('.blade.php', '.md')`.
  - The drift manifest hashes the **whole rendered directory tree**, not just `SKILL.md` — a
    change to any file under the skill (e.g. `references/*`) counts as a change.
- **Discovery by composer `extra` declaration**: a package announces its artifacts in its own
  `composer.json` under `extra.tooling.ai` — `extra.tooling.ai.agents` is an array of file
  paths (an agent is a single file), `extra.tooling.ai.skills` is an array of directory paths
  (a skill is a directory of `SKILL.blade.php` plus supporting files, rendered to `SKILL.md`
  at publish). tooling reads each installed
  package's `extra.tooling.ai` from the composer metadata (`installed.json`), so discovery is a
  cheap metadata read, not a filesystem crawl. This is the explicit-opt-in analog of the
  russian doll: packages declare what they ship, tooling pulls it. Chosen over magic-path
  scanning because it's explicit, author-controlled, and mirrors Laravel's own
  `extra.laravel.providers` auto-discovery. It lives under `extra.tooling` (alongside the
  existing `phpstan`/`pint`/`rector` blocks) because discovery/publishing is a
  tooling-laravel-provided feature — a package declaring artifacts for tooling to publish is a
  tooling participant by definition.
- **Config-driven selection + sync**: a project declares which discovered skills/agents it
  wants (config), and an on-demand sync command materializes that selection into each
  provider's paths. A manifest (`{source, version, hash}`) detects staleness, with optional
  composer-lifecycle auto-republish via tooling-laravel's existing composer plugin. There is
  no interactive picker and no bundling in this scope.
- **Authoring via bespoke MCP tools**: creating a new skill/agent is exposed as two
  hand-written `Laravel\Mcp\Server\Tool` subclasses (like the existing
  `Tooling\*\Mcp\Tools\{Pint,PHPStan,Rector}`), **not** artisan generator commands. This
  sidesteps the CLI-argument problem entirely — a Blade document (line breaks, YAML frontmatter
  colons, `{{ }}`) can't survive shell quoting as a command argument, but a bespoke tool's
  `schema()` declares a plain `content` string property that MCP passes as a JSON string value,
  which handles multi-line content fine. Flow: the user and LLM iterate on the body in
  conversation, then call the tool once to *commit* it. The tool's value is enforcement:
  standardized location, naming, and wiring the `extra.tooling.ai` composer key — the parts
  that are easy to get wrong — are done deterministically in `handle()`, while the creative
  body stays with the model. Two separate tools (not one with a `type` discriminator) so each
  has a clean, self-describing schema:
  - `MakeSkill` — schema `name` + `content` (+ optional supporting files); writes
    `resources/ai/skills/<name>/SKILL.blade.php`; wires `extra.tooling.ai.skills` (dir path).
  - `MakeAgent` — schema `name` + `content`; writes `resources/ai/agents/<name>.blade.php`;
    wires `extra.tooling.ai.agents` (file path).
  Both are registered on the `Development` server alongside the existing MCP tools. The tool
  edits `composer.json` itself (deterministic wiring is the point) and returns a `Response`
  confirming the path and the composer key it updated.

## Steps

### Phase 1 — Provider model
1. In a new `Tooling\Ai\*` module, define a `Provider` abstraction and per-kind capability
   interfaces: `SupportsSkills` (`skillsPath()`) and `SupportsAgents` (`agentsPath()` **plus a
   frontmatter-projection method** that maps the canonical superset frontmatter to the
   provider's native syntax). Paths config-overridable with defaults. _Depends on nothing._
2. Implement a starting subset of concrete providers — Copilot and Claude Code first. Their
   `SupportsSkills` differ only by path; their `SupportsAgents` differ by path **and**
   projection (Claude: comma-string `tools` + `.claude/agents/<name>.md`; Copilot: YAML-array
   `tools` + `.github/agents/<name>.agent.md`). _Depends on 1._

### Phase 2 — Canonical artifacts + writers
3. Canonical value objects `Skill` (a directory) and `Agent` (a single file), authored as
   `.blade.php` (always Blade, no `.md` variant). A renderer compiles each `.blade.php` via
   `Blade::render()` (whole file, one pass), then parses frontmatter from the rendered result.
   _Depends on 1._
4. Per-kind writers (`SkillWriter`, `AgentWriter`) that translate canonical definitions into
   each selected provider's path. `AgentWriter` renders the single file, then applies the
   provider's `SupportsAgents` frontmatter projection (canonical superset → native `tools`/
   `model`/extension) before writing. `SkillWriter` walks the skill directory recursively,
   rendering each `.blade.php` (dropping the extension), copying every other file verbatim, and
   rewriting Markdown link/image targets that point at a sibling rendered `.blade.php` to their
   `.md` output name (link-target-scoped, not a blanket string replace); the skill body is
   provider-agnostic, so no frontmatter projection is needed for skills. _Depends on 2, 3._

### Phase 3 — Discovery
5. A `Catalog` fed by reading each installed composer package's `extra.tooling.ai` declaration
   (`extra.tooling.ai.agents` = file paths, `extra.tooling.ai.skills` = directory paths) from
   the composer metadata, feeding the sync command. _Depends on 3._

### Phase 4 — Selection + sync/drift
6. Config-driven selection: a project declares which discovered skills/agents it wants. Absent
   config, sync everything discovered. _Depends on 3, 5._
7. An on-demand `sync` artisan command that reads the config selection and the catalog
   (Phase 3), then drives the writers to materialize the selection into each selected
   provider's paths. _Depends on 4, 5, 6._
8. A manifest (`{source, version, hash}`) for staleness detection — for a skill the `hash`
   covers the whole rendered directory tree, not just `SKILL.md`, so a change to any supporting
   file is detected. Plus optional composer-lifecycle auto-republish via the existing composer
   plugin. _Depends on 7._

### Phase 5 — Authoring tools + dogfood
9. Two bespoke MCP tools `MakeSkill` and `MakeAgent` (hand-written `Tool` subclasses) that
   accept `name` + `content`, enforce the standardized `resources/ai/{skills,agents}` location
   and naming, write the `.blade.php`, edit the `extra.tooling.ai.*` composer key, and return a
   confirmation. Registered on the `Development` server. _Depends on 3._
10. Ship tooling-laravel's own `authoring-ai-artifacts` skill (declared via its own
    `extra.tooling.ai.skills`) documenting the conventions — `.blade.php`-only, skill-is-a-
    directory, `{!! !!}` in frontmatter, link authoring, the `extra.tooling.ai` wiring — so an
    AI consults it before drafting a body. Doubles as the first real discovery/publish
    integration fixture (tooling-laravel dogfoods its own system). _Depends on 3, 5._

## Relevant files

- New in tooling-laravel:
  - `src/Tooling/Ai/Providers/{Provider,Copilot,ClaudeCode}.php`
  - `src/Tooling/Ai/Contracts/{SupportsSkills,SupportsAgents}.php`
  - `src/Tooling/Ai/{Skill,Agent}.php`
  - `src/Tooling/Ai/Writers/{SkillWriter,AgentWriter}.php`
  - `src/Tooling/Ai/Catalog.php`
  - `src/Tooling/Ai/Console/Commands/Sync.php`
  - `src/Tooling/Mcp/Tools/{MakeSkill,MakeAgent}.php` — bespoke authoring tools.
  - `resources/ai/skills/authoring-ai-artifacts/SKILL.blade.php` — dogfood authoring skill.
- `src/Tooling/Provider.php` — bind the new singletons, register the sync command, register the
  authoring tools on the `Development` server, declare the authoring skill in `extra.tooling.ai`.
- `src/Tooling/Composer/Plugins/PublishConfigurations.php` — optional auto-republish hook.

## Decisions

- Own system, Boost conventions borrowed, no Boost dependency.
- Guidelines deferred. Skills and agents first. Hooks seam designed but likely deferred, since
  cross-provider hook standards are weak.
- Providers are interface-per-capability objects with config-overridable paths. `SupportsSkills`
  is path-only (skills are a cross-provider standard); `SupportsAgents` is path + a frontmatter
  projection (agents have no standard, so `tools`/`model`/extension are projected per provider).
- Artifacts are author-once against the **superset**: canonical frontmatter carries the union
  of provider capabilities in a neutral shape, and each provider projects down to its native
  output. We never author in one provider's dialect. Discovery is by `extra.tooling.ai`
  composer declaration.
- Build the provider plumbing now even though ClaudeCode and Copilot overlap today (VS Code
  reads Claude-format files). The seam exists so a third provider or native Copilot output is
  additive, not a rewrite.
- Materialized output is a gitignored, reproducible build artifact; drift is handled by
  on-demand re-sync plus an optional composer-lifecycle hook.
- Selection is config-driven, not an interactive picker; the sync command only reads the
  registries, it never registers.
- Authoring is exposed as bespoke MCP `Tool` subclasses (`MakeSkill`/`MakeAgent`), not artisan
  generators — a `content` string in the tool schema handles multi-line Blade that a CLI
  argument can't. The tool enforces location/naming and edits the `extra.tooling.ai` key; the
  model supplies the body.
- Plugins (bundling/composition) are out of scope for this project; the seam is preserved so
  they can be added later without rework.

## Verification

1. `./vendor/bin/testbench tooling:pint`, `tooling:phpstan`, `tooling:rector --dry-run`, and
   `./vendor/bin/phpunit` all green.
2. Register a sample skill in a fake package fixture, confirm discovery finds it, and the
   sync command materializes the correct files at both the Copilot and Claude paths.
3. Confirm the sync command rewrites drifted artifacts and the manifest flags a
   bumped-version fixture as stale.
4. Call `MakeSkill` with a multi-line `content` (frontmatter + body) and assert it writes
   `resources/ai/skills/<name>/SKILL.blade.php` verbatim (line breaks intact) and adds the path
   to `extra.tooling.ai.skills` in composer.json; same for `MakeAgent` → single file +
   `extra.tooling.ai.agents`.
5. Confirm tooling-laravel's own `authoring-ai-artifacts` skill is discovered and syncs
   (end-to-end dogfood).

## Out of scope

- Guidelines.
- Plugins (bundling/composition, interactive builder, archives).
- The full provider matrix (start with Copilot and Claude Code).
- Exact hook file formats.

## Future considerations — plugins

Plugins (a distributable bundle composing selected skills/agents) are cut from this project
because they are the least-settled, highest-risk piece and nothing else depends on them. Every
plugin question spawned an unresolved sub-problem — distribution scope, archive-vs-local
rendering, and how a bundle references its providing packages — none of which affect the
artifact layer. The provider/writer/catalog work here is a strict prerequisite for plugins, so
building it first loses nothing. Claude Code plugins and marketplaces are also young and
shifting, so waiting avoids reinventing a format the providers may standardize.

To keep the seam clean for a later plugin layer: the sync command consumes a selection but
does not assume it came from config, so a future picker or plugin manifest can supply the same
selection shape.

## Open questions

1. **Hooks in v1?** Lean toward deferring — design the `SupportsHooks` seam but don't
   implement.
2. **Module namespace** — `Tooling\Ai\*` (recommended) versus `Tooling\Artifacts\*`.
