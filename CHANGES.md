# Changelog

All notable changes to `qbank_tagassistant` are documented here. For the exact
Moodle-upgrade-relevant subset, see `upgrade.txt`.

## v3.0.4 (Build: 2026080804)

- Adds close-up screenshots of the tag-chip states (initial, expanded,
  selected) to the CAMP listing manifest. No functional changes.

## v3.0.3 (Build: 2026080803)

- Adds `.gitattributes` with `export-ignore` rules so dev-only paths
  (`.github`, `.camp`, `tests`, `node_modules`) are excluded from the CAMP
  distribution ZIP. No functional changes.

## v3.0.2 (Build: 2026080802)

- Adds a CAMP registry listing manifest (`.camp/listing.yml`) and release
  publish workflow (`.github/workflows/camp-release.yml`). No functional
  changes; this release exists so the tag carries the workflow needed to
  publish to the [CAMP registry](https://camp-registry.org).

## v3.0.1 (Build: 2026080801)

- **Hardcoded German text on non-German sites (user-visible bug):** the AMD
  module wrote the expansion button label directly as `'+ N weitere'`, so an
  English, French or any other language site showed German. The chips'
  `aria-label` was likewise hardcoded English, and the heading had a German
  fallback. All three now resolve through `core/str` against the existing
  `moretags` / `addtagaria` / `topquestionbanktags` language strings, which
  were already shipped in both language packs but never actually used.
  Screen-reader users on non-English sites were affected as well.
  Labels show a language-neutral placeholder (e.g. `+ 10`) for the moment
  before the string resolves, and stale async resolutions are discarded so a
  slow lookup cannot relabel a button that has meanwhile moved on.
  (The v3.0.0 audit removed the module's `core/str` import as "unused". It
  was unused — but the correct fix was to use it, not to drop it.)
- README: the feature list still described the pre-v3.0.0 expansion
  behaviour and used the internal working name "Option 3". Now describes the
  batches-of-10 behaviour, and documents that the UI is fully localised.

## v3.0.0 (Build: 2026080800)

**Minimum Moodle version raised to 5.1.** This release is the result of a
critical code-review audit against Moodle core development rules. Fixes:

- **The PSR-14 hook was never actually wired up (critical):** `db/hooks.php`
  registered a listener for `\core\hook\output\before_standard_html_head`,
  a class that does not exist in Moodle core. The real hook is
  `\core\hook\output\before_standard_head_html_generation`.
  This stayed invisible in v1.x/v2.x because the chips were really rendered
  by the *deprecated* `qbank_tagassistant_before_standard_html_head()`
  callback in `lib.php` — core keeps calling a deprecated callback until the
  component registers a listener for the hook that replaces it (see
  `\core\hook\manager::is_deprecating_hook_present()`). So v2.x did work,
  just never via the "PSR-14 Hooks API only" architecture it advertised.
  Removing `lib.php` in this release therefore made the bogus hook name
  fatal, which is how it was finally caught. The listener is now
  `classes/hook/before_standard_head_html_generation_listener.php` (class
  name matching the filename, as Moodle's autoloader requires) and
  `db/hooks.php` registers the correct hook. Verified end-to-end in a real
  browser against Moodle 5.2.1: chips render with correct labels and counts,
  clicking one populates Moodle's native tag autocomplete and disables the
  chip. Cross-checked on Moodle 5.1.5.
- **Pagetype matching was broken in two ways:** the quiz-edit pagetype was
  spelled `mod_quiz-edit` (underscore) instead of Moodle's actual
  `mod-quiz-edit`, and the plain question-bank page (`/question/edit.php`,
  whose pagetype Moodle derives automatically as `question-edit`) was not in
  the whitelist at all.
- **Cache staleness (functional bug):** tag counts could be up to 1 hour
  stale after a teacher tagged/untagged a question, because the
  `context_tags` cache had no invalidation event wired up and was never
  purged from production code. Added `db/events.php` +
  `classes/observer.php` to purge the affected context's cache on question
  create/update/delete, with the TTL dropped from 3600s to 300s as a
  fallback only.
- **Cache key design bug:** `purge_context_tags_cache()` only cleared keys
  for `limit` values 10, 15, and 20; any other limit silently orphaned a
  cache entry. Redesigned to one cache entry per context (sliced to the
  requested limit in PHP), which eliminates the bug by construction.
- **Behat test would have failed:** the feature file asserted a string that
  didn't match the actual (correct) English language string.
- **Test coverage gap:** added tests locking in the correlated-subquery
  version-selection behaviour (latest *ready* version wins over a newer
  draft; entries with no ready version are excluded) and the
  observer-driven cache invalidation.
- **False "minified" build artifact:** `amd/build/tag_chips.min.js` was a
  byte-for-byte copy of the source, not a build output. Added real build
  tooling (`package.json`, `scripts/build-amd.js`, `terser`) and
  regenerated a genuinely minified/mangled file with a source map.
- **AMD module cleanup:** dropped the unused `core/str` import and the
  unnecessary `jquery` dependency (all jQuery calls duplicated an adjacent
  native DOM call). Fixed a `MutationObserver` that was created fresh for
  every opened question-edit modal and never disconnected, leaking
  observers bound to detached DOM over a long editing session; replaced
  with a single shared, self-pruning observer.
- **Simplified architecture:** removed the legacy `lib.php` callback
  fallback for `before_standard_html_head` now that Moodle 5.1+ is the
  floor and PSR-14 hooks are always available. (Note: the varying presence
  of the `MOODLE_INTERNAL` guard across files is *correct*, not an
  inconsistency — Moodle's `MoodleInternalNotNeeded` sniff only wants the
  guard in files with side effects, i.e. those assigning `$callbacks`,
  `$definitions`, `$observers` or `$plugin`. Files that merely declare a
  function, such as the old `lib.php` and the new `db/upgrade.php`, must
  not have it.)
- Added `db/upgrade.php` + `upgrade.txt` so upgrading sites get the cache
  purged automatically instead of silently keeping stale-format entries.
- **Test-correctness fix found via real Moodle 5.1/5.2 Docker testing:** course-level
  question categories are no longer stored directly under the course context —
  Moodle's qbank-as-activity-module architecture redirects them to a dedicated
  `qbank` course-module context (`core_question\local\bank\question_bank_helper`).
  `tests/helper_test.php` previously created categories against
  `context_course::instance()` directly, which no longer matches what
  `core_question_generator` (or the real question bank UI) actually does. Tests
  now create a real qbank module context first. This does not affect
  production runtime behaviour, since the plugin's SQL/cache/hook code already
  works off whatever `$PAGE->context` is, without assuming a context level.
- End-to-end verified against real Moodle 5.1.5 and 5.2.1 (via Docker): fresh
  install, in-place upgrade from v2.0.1, event-driven cache invalidation using
  a real triggered `\core\event\question_created`, and the
  ready-vs-draft-version SQL edge case — all confirmed working, with no
  errors in either container's logs.

## v2.0.3 (Build: 2026080703)

- Removed `MOODLE_INTERNAL` guard from `lib.php` for phpcs compliance.

## v2.0.1–v2.0.2

- Fixed CLI upgrade `ArgumentCountError` and an AMD jQuery event-trigger
  issue in the Moodle 5.1+ autocomplete UI.
- Fixed CI matrix and added dual PSR-14/legacy callback support for Moodle
  4.5/5.0/5.1+ compatibility (superseded by the hooks-only architecture in
  v3.0.0).

## v2.0.0

- Major release: PSR-14 Hooks API, dynamic modal form rendering, and SPA
  pagetype support.

## v1.0.1

- Added PSR-14 Hooks API (`db/hooks.php`), pagetype checks, double-execution
  protection, and capability checks for Moodle 4.4+/5.0+.

## v1.0.0

- First stable, marketplace-ready release: Moodle Plugin CI, `composer.json`,
  plugin icon, documentation.

## v0.1-beta

- Initial release.
