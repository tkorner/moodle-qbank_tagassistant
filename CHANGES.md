# Changelog

All notable changes to `qbank_tagassistant` are documented here. For the exact
Moodle-upgrade-relevant subset, see `upgrade.txt`.

## v3.0.0 (Build: 2026080800)

**Minimum Moodle version raised to 5.1.** This release is the result of a
critical code-review audit against Moodle core development rules. Fixes:

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
  floor and PSR-14 hooks are always available. Resolves a `MOODLE_INTERNAL`
  guard inconsistency that only existed because of that legacy file.
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
