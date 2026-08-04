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
- **We can't reconfigure most clients to read a custom path.** Researched: only VS Code Copilot
  exposes a setting (`chat.agentSkillsLocations`); Claude Code and Cursor read fixed directory
  sets and offer no "add a custom skills dir" knob. The one directory all three read by default
  is `.claude/skills/` (Claude native; Cursor reads it for compat; VS Code lists it by default),
  and likewise `.claude/agents/` for agents. So `.claude/skills/` + `.claude/agents/` are the
  universal publish target, matching the `.claude`-as-lingua-franca decision already made for
  agents. We publish there rather than inventing a path clients won't read.
- **Full ownership of the output dirs makes drift/orphans a non-problem.** Rather than tracking
  what we wrote (a manifest) to clean up stale files, `tooling:publish` **owns** its two output
  subtrees, `.claude/skills/` and `.claude/agents/`, and **empties then rebuilds them** on every
  run. A package that drops a skill, or a narrowed selection, leaves no orphan because the whole
  tree is regenerated from scratch. This is why no manifest is needed — full-ownership wipe, not
  diffing, is the freshness guarantee. Consequence: those two dirs are **tooling-managed build
  output**, gitignored, and must not be hand-authored in (anything a user puts there is wiped on
  the next publish). Scope the wipe to exactly those two subtrees — never `.claude/` itself,
  which also holds user-authored `settings.json`, `CLAUDE.md`, `commands/`, `agent-memory/`.
- **Two authoring homes, split by whether the artifact is shared or personal:**
  - **Shared/team artifacts** are declared like any package: a project lists its own artifacts
    under `extra.tooling.ai` pointing at committed `tooling/ai/` sources (the consuming project
    is just another discovered package — self-package discovery, same path the dogfood skill
    uses). Committed, versioned, code-reviewed. `tooling/ai/` sits alongside the existing
    `tooling/phpstan/` and `tooling/rector/` source dirs, so it matches the repo convention.
  - **Personal/machine-local artifacts** get one standardized, gitignored inbox: `tooling/ai/local/`
    (`tooling/ai/local/skills/`, `tooling/ai/local/agents/`). A dev drops an artifact there and
    discovery picks it up with zero ceremony and **zero per-skill `.gitignore` edits** — the
    single ignored `local/` subdir covers them all. This is the escape hatch for "my own scratch
    skill" that must never force gitignore churn or leak into the team repo. `local` names the
    intent the same way `settings.local.json` / `CLAUDE.local.md` do.
  - Ignoring is done **per-directory**, Laravel-style (like `storage/framework/*/.gitignore`):
    each managed dir carries its own `.gitignore` containing `*` + `!.gitignore`, so the dir
    self-ignores its contents while the ignore file stays tracked. We ship one into each of
    `.claude/skills/`, `.claude/agents/`, and `tooling/ai/local/` — **the root `.gitignore` is
    never touched**. For the two owned output dirs, publish wipes the dir fully and then rewrites
    the `.gitignore` as the first step of the rebuild — no preserve-across-wipe special case, the
    file is just another artifact emitted every run. For `tooling/ai/local/` (a source dir we
    don't wipe) it's scaffolded once if absent. Note `tooling/ai/` itself is committed — only its
    `local/` subdir self-ignores.
- **Name-collision precedence**: when two sources resolve to the same output name (a vendor
  package ships `testing` and local `tooling/ai/local/skills/testing/` also exists), **local
  overrides vendor** — your own project beats a dependency — and the shadowed vendor copy is
  skipped with a logged notice.
- **Rendering vendor Blade executes vendor PHP (known, accepted).** `Blade::render()` compiles
  and runs the template, so a `.blade.php` shipped by a dependency runs that dependency's PHP at
  publish time (including in CI). This isn't a new exposure — `composer` already runs dependency
  install-scripts and plugins — but it's less obvious (a file that looks like docs can execute
  code). We accept it and treat vendor skills as vendor code: install only what you trust. Not
  sandboxing; a constrained data scope wouldn't stop `@php`/`shell_exec` anyway, so it'd be
  theater.

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
  - `SupportsAgents` also exposes a frontmatter-projection step, but in v1 it is
    **identity / pass-through**. Agents have no open standard, yet the Claude sub-agent format
    is the de-facto lingua franca: Claude Code, Copilot/VS Code, and Cursor all read
    `.claude/agents/*.md` (VS Code even maps Claude tool names to its own). So we author agents
    in Claude format and publish them as-is to `.claude/agents/` — every consumer we care about
    reads it. The projection method exists as a seam (so a future provider that *can't* read
    Claude format, or native Copilot `.github/agents/*.agent.md` output, is additive) but does
    no real mapping today. Building speculative `tools`/`model` translation now would be YAGNI.
  - We build the provider plumbing now even though today's providers all read Claude format. The
    seam is the point: adding a non-Claude provider, or native Copilot artifacts, is then
    additive. But the seam is *empty* for agents in v1 (identity projection), not a real
    translator.
- **Author toward the superset (as a seam, implemented lazily)**: the canonical artifact is the
  richest single form we can author, and providers project *down* to native output — never the
  reverse. For skills that's inherent (open standard). For agents in v1 the "superset" is simply
  the Claude format, projected via identity to `.claude/agents/`. The only authoring guidance
  that keeps agents portable today: **omit `model`** (it defaults to `inherit`, which every
  consumer honors — a Claude alias like `sonnet` would be meaningless to Copilot), and use
  `tools` names Copilot already maps. If a real divergence ever appears (native Copilot
  placement, `handoffs`, model pinning), the ClaudeCode/Copilot projections get filled in then —
  the canonical shape and the seam don't change. Authoring against the superset is what makes
  "write once, publish natively to every provider"
  true rather than "write in one provider's dialect and hope the others tolerate it."
- **Blade is opt-in at every level; a `.blade.php` always renders to `.md`**: Blade is not
  forced on anything. Any file in an artifact *may* be authored as `.blade.php`, and the writer
  renders it and drops the extension so it lands as `.md` (`SKILL.blade.php` → `SKILL.md`,
  `references/usage.blade.php` → `references/usage.md`). Every other file is copied verbatim,
  never rendered — so a `scripts/run.sh` stays a runnable script, a `references/notes.md` stays
  plain Markdown, and an `assets/logo.png` passes through untouched. This is deliberately not
  "everything is Blade": scripts and binaries can't be Blade (a `.sh` would render to `.md` and
  stop being executable, and code legitimately contains `@`/`{{`), and a fully static skill can
  be authored entirely in `.md` with no Blade at all. Because `.blade.php` **always** targets a
  `.md` output, Blade is only ever used for Markdown-producing files, and static→dynamic
  evolution stays cheap: a static `foo.md` that later needs a config value is renamed to
  `foo.blade.php` and the interpolation added — the *published* name (`foo.md`) is unchanged,
  so links and the `extra.tooling.ai` path key on the rendered output, not the source
  extension, and don't churn on the switch. Rendering rules for a `.blade.php` file:
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
  - Known limitation: because the `.blade.php` extension *is* the render signal, you can't ship
    a literal, unrendered `.blade.php` as an asset (it would always render to `.md`). Rare;
    noted, not solved.
- **Artifact shapes differ — agent = single file, skill = directory**: an agent is one file
  that publishes to a single Markdown file (both Claude Code sub-agents and Copilot custom
  agents are a single Markdown file with YAML frontmatter — never a directory of supporting
  materials; they compose other artifacts by *reference* via `skills:`/`mcpServers:`
  frontmatter, not co-located files). Its source is `<name>.md` or, when it needs interpolation,
  `<name>.blade.php`. A skill is a **directory**: a required `SKILL.md` entry (authored as
  `SKILL.blade.php` when it needs interpolation) plus optional supporting material in
  subdirectories like `references/`, `scripts/`, `assets/`.
  - **Skill naming invariant**: the Agent Skills spec (and Copilot) require the `name`
    frontmatter value to equal the skill's directory name, and restrict it to 1–64 chars,
    lowercase alphanumeric + single hyphens (no leading/trailing/consecutive hyphens). A
    mismatch or invalid character makes the skill silently fail to load. **`name:` is the source
    of truth**: `SkillWriter` derives the published directory name *from* the (validated) `name:`
    value, so the two can't disagree by construction — this holds for every source, including
    third-party skills we didn't author (their frontmatter is trusted for `name`, but the output
    dir is named from it, not from their on-disk folder). Validate `name:` against the spec rules
    at publish and normalize/fix it if malformed; `MakeSkill` validates the same rule at
    authoring time as an early guard. Required skill frontmatter is just `name` + `description`;
    `license`, `compatibility`, `metadata`, `allowed-tools` are optional passthrough.
  - **Agent frontmatter is Claude format, published as-is (v1)**: the canonical agent is
    authored in Claude sub-agent frontmatter and published verbatim to `.claude/agents/`, which
    Claude Code, Copilot/VS Code, and Cursor all read. Required fields are `name` +
    `description`; `tools`/`model` are optional. Keep `model` **omitted** so it defaults to
    `inherit` (a Claude alias like `sonnet` means nothing to Copilot), and use `tools` names
    Copilot maps. The `SupportsAgents` projection is identity today; real per-provider mapping
    (comma-string vs YAML array `tools`, model pinning, native `.github/agents/*.agent.md`) is a
    future seam, not built now.

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
- **Discovery by composer `extra` declaration**: a package announces its artifacts in its own
  `composer.json` under `extra.tooling.ai` — `extra.tooling.ai.agents` is an array of file
  paths (an agent is a single file), `extra.tooling.ai.skills` is an array of directory paths
  (a skill is a directory with a `SKILL.md` entry — authored as `SKILL.blade.php` when it needs
  interpolation — plus supporting files). tooling reads each installed
  package's `extra.tooling.ai` from the composer metadata (`installed.json`), so discovery is a
  cheap metadata read, not a filesystem crawl. This is the explicit-opt-in analog of the
  russian doll: packages declare what they ship, tooling pulls it. Chosen over magic-path
  scanning because it's explicit, author-controlled, and mirrors Laravel's own
  `extra.laravel.providers` auto-discovery. It lives under `extra.tooling` (alongside the
  existing `phpstan`/`pint`/`rector` blocks) because discovery/publishing is a
  tooling-laravel-provided feature — a package declaring artifacts for tooling to publish is a
  tooling participant by definition. The consuming project is itself a discovered package (its
  own `extra.tooling.ai` pointing at committed `tooling/ai/` sources), which is how a team
  ships shared, version-controlled artifacts — same path the dogfood skill uses.
- **Personal inbox for zero-ceremony local artifacts**: alongside `extra.tooling.ai` discovery,
  the publisher also scans a single standardized directory in the project, `tooling/ai/local/`
  (`tooling/ai/local/skills/`, `tooling/ai/local/agents/`), and treats anything found there as a
  discovered artifact. This is the home for **personal, machine-local** skills/agents: a dev
  drops one in and it's picked up with no `composer.json` edit and no `.gitignore` edit (the
  `tooling/ai/local/` subdir is gitignored once). It complements the committed `extra.tooling.ai`
  path — shared artifacts are declared and committed under `tooling/ai/`, personal ones live in
  the ignored `tooling/ai/local/`. On a name clash, a local artifact overrides a vendor
  package's (your project beats a dependency); the shadowed vendor copy is skipped with a logged
  notice.
- **Publish on `composer` dump (primary), config-driven selection**: publishing is driven by
  the existing composer plugin's `POST_AUTOLOAD_DUMP` hook, so every `composer install`,
  `update`, or `dump-autoload` re-materializes the selected artifacts into each provider's
  paths. The published docs are therefore always current with the installed package versions —
  no manual step, no staleness window. This reuses the exact seam the plugin already drives
  (`DiscoverTooling` shells `php artisan tooling:discover && tooling:optimize`); AI publishing
  becomes another step there (a `tooling:publish` command). A project declares which discovered
  skills/agents it wants via config; absent config, everything discovered is published. The
  command **empties its two owned output subtrees (`.claude/skills/`, `.claude/agents/`) then
  rebuilds them from scratch** every run — so a dropped package or narrowed selection leaves no
  orphan, and **no manifest is needed** because full-ownership wipe (not diffing) is the
  freshness guarantee. A manual `tooling:publish` command still exists for republishing without a
  full composer run (fast iteration while authoring), but the composer hook is the mechanism of
  record. "Publish" (not "sync") is deliberate: the operation is one-way wholesale
  materialization, not bidirectional reconciliation. There is no interactive picker and no
  bundling in this scope.
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
    `tooling/ai/skills/<name>/SKILL.blade.php`; wires `extra.tooling.ai.skills` (dir path).
  - `MakeAgent` — schema `name` + `content`; writes `tooling/ai/agents/<name>.blade.php`;
    wires `extra.tooling.ai.agents` (file path).
  The tools default to a `.blade.php` extension (not because Blade is required, but because it's
  the zero-cost default that never needs a later rename to add interpolation); a purely static
  artifact is equally valid as `.md`.
  Both are registered on the `Development` server alongside the existing MCP tools. The tool
  edits `composer.json` itself (deterministic wiring is the point) and returns a `Response`
  confirming the path and the composer key it updated.
- **Baseline templates as an MCP `ResourceTemplate`**: the canonical starting skeleton for each
  artifact kind is exposed as a single `ResourceTemplate` with the kind as the variable slot —
  `tooling-ai://artifact-template/{kind}`, `kind ∈ {skill, agent}`. Reading `.../skill` returns
  the baseline `SKILL.blade.php` skeleton (valid frontmatter keys, `name:`/`description:`
  scaffolded, body headings); `.../agent` returns the baseline agent `.blade.php`. The point is a
  *coherent, usable baseline* for the `.blade.php` files we create, not a way to read existing
  artifacts back — the variable is `{kind}`, not an artifact name. **Single source of truth**:
  the same baseline the `ResourceTemplate` serves is the scaffold `MakeSkill`/`MakeAgent` write
  from, so the model can read the skeleton *before* it drafts and the resulting `content` already
  conforms, with the tool's deterministic enforcement as a backstop rather than the only guard.
  This is the literal skeleton; the `authoring-ai-artifacts` skill is the prose about how/why —
  complementary, not duplicative. Registered on the `Development` server alongside the tools.

## Steps

### Phase 1 — Provider model
1. In a new `Tooling\Ai\*` module, define a `Provider` abstraction and per-kind capability
   interfaces: `SupportsSkills` (`skillsPath()`) and `SupportsAgents` (`agentsPath()` plus a
   frontmatter-projection method that is **identity in v1** — the seam exists for a future
   non-Claude provider but does no mapping now). Paths config-overridable with defaults.
   _Depends on nothing._
2. Implement a starting subset of concrete providers — Claude Code first (Copilot/Cursor read
   its output). `SupportsSkills` differ only by path; `SupportsAgents` publish Claude-format
   agents as-is to `.claude/agents/` (identity projection). A native Copilot provider
   (`.github/agents/*.agent.md` with mapped `tools`/`model`) is deferred to when it's needed.
   _Depends on 1._

### Phase 2 — Canonical artifacts + writers
3. Canonical value objects `Skill` (a directory) and `Agent` (a single file). A renderer
   compiles any `.blade.php` source via `Blade::render()` (whole file, one pass) and emits it
   as `.md`, copying every non-`.blade.php` file verbatim, then parses frontmatter from the
   rendered result. Blade is opt-in per file, not required. _Depends on 1._
4. Per-kind writers (`SkillWriter`, `AgentWriter`) that translate canonical definitions into
   each selected provider's path. `AgentWriter` renders the single file, then applies the
   provider's `SupportsAgents` projection before writing — which is identity in v1, so it writes
   the Claude-format file straight to `.claude/agents/`. `SkillWriter` walks the skill directory
   recursively,
   rendering each `.blade.php` (dropping the extension), copying every other file verbatim, and
   rewriting Markdown link/image targets that point at a sibling rendered `.blade.php` to their
   `.md` output name (link-target-scoped, not a blanket string replace); the skill body is
   provider-agnostic, so no frontmatter projection is needed for skills. `SkillWriter` also
   **derives the output directory name from the validated `name:` frontmatter** (normalizing a
   malformed value), so `name` and dir can't disagree and third-party skills can't silently fail
   to load. _Depends on 2, 3._

### Phase 3 — Discovery
5. A `Catalog` fed by two sources: (a) `extra.tooling.ai` declarations, discovered using the
   **exact same participation mechanism `Manifest` already uses for phpstan/rector** —
   `Composer::packages->concat([currentAsPackage])` so the root project is included alongside
   vendor packages, with the same base-dir-vs-`vendor/<name>/` path resolution keyed on
   `currentAsPackage` (see `Manifest::extractRector`/`extractPhpStan`). This is what makes the
   consuming project's own shared artifacts (and the dogfood skill) discoverable, not just
   dependencies'. (b) the project's personal `tooling/ai/local/` inbox
   (`tooling/ai/local/{skills,agents}/`) scanned directly from disk. On a name clash between the
   two, the local inbox wins and the shadowed vendor entry is skipped with a logged notice.
   _Depends on 3._

### Phase 4 — Selection + publish-on-dump
6. Config-driven selection: a project declares which discovered skills/agents it wants (a new
   `ai` key in `config/tooling.php`). Absent config, publish everything discovered. _Depends on
   3, 5._
7. A `tooling:publish` command that **empties its owned output subtrees (`.claude/skills/`,
   `.claude/agents/`) then rebuilds them** from the config selection + catalog (Phase 3),
   driving the writers. Wipe is scoped to exactly those two subtrees, never `.claude/` itself.
   The rebuild's first step is (re)writing each dir's own `.gitignore`, so the wipe can be a
   plain full clear — no preserve-across-wipe special case. Full-ownership rebuild means no
   orphans and no manifest. As it runs it collects a **result
   per artifact** — published (name + source origin) or skipped (name/path + reason, e.g.
   "missing `name:`", "invalid `name:`", "shadowed by local `<name>`") — rendered with the same
   `$this->components->*` vocabulary the other `tooling:*` commands use (`twoColumnDetail` rows
   `artifact .......... published`/`skipped: reason`). **The command owns its own signal policy:
   quiet on success, loud on skip.** On a fully-successful run it prints nothing but a one-line
   `info` count; whenever an artifact is skipped it warns the skipped rows — *always*, regardless
   of how it was invoked. `-q` is therefore not load-bearing: skips surface even during a `-q`
   composer dump, and success stays quiet even on a manual run. `-q` is left to do only what it
   does for `discover`/`optimize` — mute framework-level chatter — not to decide whether our own
   important output appears. _Depends on 4, 5, 6._
8. Ship a self-ignoring `.gitignore` (`*` + `!.gitignore`, Laravel `storage/`-style) **into each
   managed dir** rather than editing the root `.gitignore`: `.claude/skills/` and
   `.claude/agents/` get theirs rewritten as the first rebuild step of every publish (so the
   wipe is a plain full clear); `tooling/ai/local/` gets one scaffolded once if absent (it's a
   source dir we don't wipe). `tooling/ai/` itself stays committed. The root `.gitignore` is
   never touched. _Depends on 7._
9. Wire `tooling:publish` into the existing composer plugin's `POST_AUTOLOAD_DUMP` step
   (alongside `tooling:discover`/`tooling:optimize` in `DiscoverTooling`) so every `composer
   install`/`update`/`dump-autoload` republishes, and check the shelled command's exit code so a
   publish failure surfaces instead of failing silently. _Depends on 7._

### Phase 5 — Authoring tools + dogfood
10. A baseline template per artifact kind exposed as a single MCP `ResourceTemplate`
    (`tooling-ai://artifact-template/{kind}`, `kind ∈ {skill, agent}`), serving the canonical
    `SKILL.blade.php` / agent `.blade.php` skeleton. Same bytes the make-tools scaffold from
    (single source of truth). Registered on the `Development` server. _Depends on nothing._
11. Two bespoke MCP tools `MakeSkill` and `MakeAgent` (hand-written `Tool` subclasses) that
    accept `name` + `content`, enforce the standardized `tooling/ai/{skills,agents}` location
    and naming, scaffold from the Phase-5 baseline template, write the `.blade.php`, edit the
    `extra.tooling.ai.*` composer key, and return a confirmation. Registered on the `Development`
    server. _Depends on 3, 10._
12. Ship tooling-laravel's own `authoring-ai-artifacts` skill (declared via its own
    `extra.tooling.ai.skills`). It's a **procedural walkthrough**, not passive reference docs: it
    drives the agent step-by-step through authoring an artifact — clarify what the skill/agent
    does → draft `description` → **collect the tools it needs and pare to least privilege**
    (`tools` for an agent, `allowed-tools` for a skill) → write the body → read the
    `ArtifactTemplate` baseline for the kind → call `MakeSkill`/`MakeAgent` to commit. Collecting
    tools is the *skill's* job (a conversational step with reasoning), which is why the make-tools
    don't need `tools` schema fields and the template only scaffolds the keys. It also carries the
    conventions the walkthrough relies on — opt-in Blade→`.md`, skill-is-a-directory, `{!! !!}` in
    frontmatter, link authoring, `extra.tooling.ai` wiring, the `tooling/ai/local/` inbox. Doubles
    as the first real discovery/publish integration fixture (tooling-laravel dogfoods its own
    system). _Depends on 3, 5, 10, 11._

### Phase 6 — Documentation
13. Add `docs/ai-artifacts.md` in the same style as `docs/{phpstan,rector,pint}.md` — what
    skills/agents are, the `extra.tooling.ai` declaration, the `tooling/ai/` (committed) vs
    `tooling/ai/local/` (personal, gitignored) authoring homes, the `.blade.php`-renders-to-`.md`
    rule, `tooling:publish`, and the publish-on-`composer`-dump behavior. _Depends on 7, 11._
14. Update `README.md` to match reality: add `tooling:publish` to the Usage command list; add
    `docs/ai-artifacts.md` to the intro feature links; add `extra.tooling.ai` to the "Extending
    Tooling" section (which already documents `extra.tooling.rector`/`phpstan` pointing at
    `tooling/rector/`, `tooling/phpstan/` — so `tooling/ai/` slots right in); and extend "How
    Discovery Works" step 2 (currently "runs `tooling:discover` and `tooling:optimize`") to
    include `tooling:publish` on the same `post-autoload-dump` hook, noting it materializes
    skills/agents into `.claude/` rather than a `vendor/` cache. The README references the plugin
    generically (not by class name), so the `CacheConfigurations` rename needs no README fix.
    _Depends on 13._

## Relevant files

- New in tooling-laravel:
  - `src/Tooling/Ai/Providers/{Provider,Copilot,ClaudeCode}.php` — `Copilot` carries its own
    `.github/skills` path for `SupportsSkills`; for agents its projection is identity (agents
    publish to `.claude/agents/`, which Copilot reads). Native Copilot agent output is deferred.
  - `src/Tooling/Ai/Contracts/{SupportsSkills,SupportsAgents}.php`
  - `src/Tooling/Ai/{Skill,Agent}.php`
  - `src/Tooling/Ai/Writers/{SkillWriter,AgentWriter}.php`
  - `src/Tooling/Ai/Catalog.php`
  - `src/Tooling/Ai/Console/Commands/Publish.php` — the `tooling:publish` command.
  - `src/Tooling/Mcp/Tools/{MakeSkill,MakeAgent}.php` — bespoke authoring tools.
  - `src/Tooling/Mcp/Resources/ArtifactTemplate.php` — the `ResourceTemplate` serving the
    baseline skill/agent skeleton the make-tools scaffold from (single source of truth).
  - `tooling/ai/skills/authoring-ai-artifacts/SKILL.blade.php` — dogfood authoring skill.
- `src/Tooling/Provider.php` — bind the new singletons, register the `tooling:publish` command, register the
  authoring tools on the `Development` server, declare the authoring skill in `extra.tooling.ai`.
- `src/Tooling/Composer/Plugins/Features/DiscoverTooling.php` — add the AI publish step to the
  `POST_AUTOLOAD_DUMP` run (and check its exit code) so every composer dump republishes
  (primary trigger).
- `config/tooling.php` — add an `ai` key for the skills/agents selection (currently only
  `phpstan`/`pint`/`rector`).
- `tooling/ai/{skills,agents}/` — committed shared-artifact sources (alongside the existing
  `tooling/phpstan/`, `tooling/rector/`); `tooling/ai/local/` is the gitignored personal inbox.
- per-directory `.gitignore` files (`*` + `!.gitignore`) shipped into `.claude/skills/`,
  `.claude/agents/`, and `tooling/ai/local/` — Laravel `storage/`-style; the root `.gitignore`
  is not touched.
- `docs/ai-artifacts.md` — new feature doc in the `docs/{phpstan,rector,pint}.md` style.
- `README.md` — Usage list, intro links, `extra.tooling.ai` under "Extending Tooling", and the
  "How Discovery Works" steps updated for `tooling:publish`.

## Decisions

- Own system, Boost conventions borrowed, no Boost dependency.
- Guidelines deferred. Skills and agents first. Hooks seam designed but likely deferred, since
  cross-provider hook standards are weak.
- Providers are interface-per-capability objects with config-overridable paths. `SupportsSkills`
  is path-only (skills are a cross-provider standard); `SupportsAgents` has a projection seam
  that is **identity in v1** — agents are authored in Claude format and published as-is to
  `.claude/agents/`, which Claude Code, Copilot, and Cursor all read.
- Agents accept the Claude format as canonical rather than inventing a neutral superset now,
  because Claude format already reaches every consumer we target. "Author toward the superset"
  is satisfied by keeping the projection seam, not by building speculative `tools`/`model`
  mapping (YAGNI). Portability rule: omit `model` (defaults to `inherit`). Discovery is by
  `extra.tooling.ai` composer declaration.
- Build the provider plumbing now even though all current providers read Claude format. The
  seam exists so a non-Claude provider or native Copilot `.github/agents/*.agent.md` output is
  additive, not a rewrite — but it stays empty (identity) until something needs it.
- We can't reconfigure Claude Code or Cursor to read a custom path (only VS Code exposes
  `chat.agentSkillsLocations`), and `.claude/skills/` + `.claude/agents/` are the one target all
  three read by default. So we publish there and **take full ownership** of those two subtrees:
  `tooling:publish` empties then rebuilds them each run. Full-ownership wipe (not a manifest)
  makes orphans impossible; a dropped package or narrowed selection just doesn't reappear. The
  wipe is scoped to those two subtrees only — never `.claude/` itself. Consequence: those dirs
  are tooling-managed, gitignored, not-hand-authored.
- Two authoring homes: shared/team artifacts are declared+committed via `extra.tooling.ai` +
  `tooling/ai/` (the project is a self-discovered package; `tooling/ai/` matches the existing
  `tooling/phpstan`, `tooling/rector` source dirs); personal/machine-local artifacts go in the
  standardized gitignored inbox `tooling/ai/local/{skills,agents}/`, discovered with zero
  `composer.json`/`.gitignore` churn. Ignoring is per-directory, Laravel `storage/`-style: a
  self-ignoring `.gitignore` (`*` + `!.gitignore`) shipped into each of `.claude/skills/`,
  `.claude/agents/`, `tooling/ai/local/`; the root `.gitignore` is never edited and
  `tooling/ai/` itself stays committed.
- Name-collision precedence: local `tooling/ai/local/` overrides a vendor package of the same
  name (your project beats a dependency); shadowed vendor entry skipped with a logged notice.
- `name:` frontmatter is the source of truth for a skill's identity: `SkillWriter` derives the
  published directory name from the validated `name:` (fixing a malformed value), so
  name/dir can never disagree — works for third-party skills too, closing the silent-load-failure
  hole. `MakeSkill` validates the same rule at authoring time as an early guard.
- Discovery reuses `Manifest`'s existing participation mechanism verbatim
  (`packages->concat([currentAsPackage])` + base-dir-vs-`vendor/<name>/` resolution), so the
  root project participates like any other package — no bespoke self-package handling.
- Rendering vendor `.blade.php` executes that dependency's PHP at publish (same trust surface
  `composer` already has); accepted and documented, not sandboxed.
- Materialized output is a gitignored, reproducible build artifact, republished wholesale on
  every `composer` dump via the existing `POST_AUTOLOAD_DUMP` plugin hook — so it can't go
  stale and needs no manifest or drift detection. A manual `tooling:publish` command exists for
  fast authoring iteration, but the composer lifecycle is the mechanism of record.
- The command is `tooling:publish`, not `sync`: flat `tooling:*` namespace like the other
  commands, and "publish" honestly names a one-way wholesale materialization (no diffing or
  reconciliation, matching the no-manifest decision).
- `tooling:publish` tracks a per-artifact result (published vs skipped-with-reason) and renders
  it in the existing `components` style. It owns its own signal policy — **quiet on success (a
  one-line count), loud on skip (always warns skipped rows)** — so correctness never depends on
  whether `-q` was passed. `-q` stays cosmetic (framework-noise suppression only, like
  `discover`/`optimize`); a missing/invalid `name:` surfaces even during a `-q` composer dump.
- Selection is config-driven, not an interactive picker; publishing only reads the catalog, it
  never registers.
- Authoring is exposed as bespoke MCP `Tool` subclasses (`MakeSkill`/`MakeAgent`), not artisan
  generators — a `content` string in the tool schema handles multi-line Blade that a CLI
  argument can't. The tool enforces location/naming and edits the `extra.tooling.ai` key; the
  model supplies the body.
- Authoring is a **three-way division of labor**: the `authoring-ai-artifacts` **skill** is the
  procedural walkthrough that *collects* everything (name, description, tools/allowed-tools with
  least-privilege reasoning, body); the `ArtifactTemplate` **ResourceTemplate** is the baseline
  skeleton it fills in; the **make-tools** do the deterministic commit (path, naming, composer
  wiring). So `tools`/`allowed-tools` are NOT tool-schema fields — collection is the skill's job,
  and hoisting them would force frontmatter re-serialization for no structural gain. Structured
  tool data is only worth it when native Copilot agent projection (deferred) needs it.
- Plugins (bundling/composition) are out of scope for this project; the seam is preserved so
  they can be added later without rework.

## Verification

1. `./vendor/bin/testbench tooling:pint`, `tooling:phpstan`, `tooling:rector --dry-run`, and
   `./vendor/bin/phpunit` all green.
2. Register a sample skill and a sample agent in a fake package fixture, confirm discovery
   finds them, and `tooling:publish` materializes the skill at each configured skills path and
   the Claude-format agent at `.claude/agents/`.
3. Confirm a `composer dump-autoload` in the fixture republishes the artifacts (the
   `POST_AUTOLOAD_DUMP` path), and that a changed source is reflected wholesale on the next
   dump without any manifest step.
4. **Orphan removal**: publish, then remove the sample skill from the fixture, publish again,
   and assert the previously-published output is gone (wipe-then-rebuild owns the dir). Also
   assert the wipe never touches sibling `.claude/settings.json` / `CLAUDE.md`.
5. **Personal inbox + precedence**: drop a skill in `tooling/ai/local/skills/<name>/`, confirm
   it publishes with no `composer.json` edit; add a vendor package skill of the same name and
   confirm the local one wins with a logged notice. Confirm each managed dir
   (`.claude/skills/`, `.claude/agents/`, `tooling/ai/local/`) has its own self-ignoring
   `.gitignore` after publish (rewritten every run for the owned dirs), and that the root
   `.gitignore` is untouched.
6. Call `MakeSkill` with a multi-line `content` (frontmatter + body) and assert it writes
   `tooling/ai/skills/<name>/SKILL.blade.php` verbatim (line breaks intact) and adds the path
   to `extra.tooling.ai.skills` in composer.json; same for `MakeAgent` → single file +
   `extra.tooling.ai.agents`. Assert the `ArtifactTemplate` `ResourceTemplate` returns the
   baseline skeleton for `{kind}` = `skill`/`agent`, and that it's the same skeleton the tools
   scaffold from (single source of truth).
7. Confirm tooling-laravel's own `authoring-ai-artifacts` skill is discovered and published
   (end-to-end dogfood) — this exercises root/self-package discovery via the `Manifest`
   participation mechanism, not just vendor packages.
8. **name/dir derivation**: publish a skill whose `name:` frontmatter differs from its source
   folder name and assert the *output* directory is named from `name:` (so it loads), and that a
   malformed `name:` is normalized rather than published broken.
9. **Publish summary + skip reporting**: publish a mix of a valid artifact and one missing
   `name:`; assert a fully-successful run is quiet (one-line count only) while the skipped one is
   warned with its reason, and that the skip surfaces identically with and without `-q` (the
   command's policy, not the flag, decides).
10. **Docs**: `docs/ai-artifacts.md` exists and covers the model; `README.md` lists
    `tooling:publish`, documents `extra.tooling.ai`, and its "How Discovery Works" mentions AI
    publishing on `post-autoload-dump`. Run the repo's own doc/link checks if any.

## Out of scope

- Guidelines.
- Plugins (bundling/composition, interactive builder, archives).
- The full provider matrix (start with Claude Code + Copilot; Copilot reuses Claude-format
  agents).
- Native Copilot agent output (`.github/agents/*.agent.md` with mapped `tools`/`model`) — the
  `SupportsAgents` projection seam exists but stays identity until this is needed.
- Exact hook file formats.

## Future considerations — plugins

Plugins (a distributable bundle composing selected skills/agents) are cut from this project
because they are the least-settled, highest-risk piece and nothing else depends on them. Every
plugin question spawned an unresolved sub-problem — distribution scope, archive-vs-local
rendering, and how a bundle references its providing packages — none of which affect the
artifact layer. The provider/writer/catalog work here is a strict prerequisite for plugins, so
building it first loses nothing. Claude Code plugins and marketplaces are also young and
shifting, so waiting avoids reinventing a format the providers may standardize.

To keep the seam clean for a later plugin layer: the `tooling:publish` command consumes a selection but
does not assume it came from config, so a future picker or plugin manifest can supply the same
selection shape.

## Open questions

1. **Hooks in v1?** Lean toward deferring — design the `SupportsHooks` seam but don't
   implement.
2. **Module namespace** — `Tooling\Ai\*` (recommended) versus `Tooling\Artifacts\*`.
