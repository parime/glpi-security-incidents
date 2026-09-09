# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

## [0.1.4] - 2026-09-09

### Added

- **Bulk CVE association** (#4): the CVE tab's single text input is now a textarea accepting
  several identifiers at once — one per line and/or comma-separated. Tokenizing is deduplicated
  and case-normalized up front (`PluginSecurityincidentsSecurityIncidentCve::splitIdentifiers()`);
  shape validation still happens per identifier when it's actually added, so one malformed line
  is skipped (with its usual `"X" is not a valid CVE identifier` message) instead of aborting the
  whole batch or silently accepting it. A summary message reports how many were added.

  Verified live: a batch mixing newlines, commas, a duplicate, and one invalid identifier added
  exactly the 3 valid unique ones and reported both the correct plural count and the specific
  invalid identifier — not just that "something" was rejected.

## [0.1.3] - 2026-09-09

### Fixed

Two more real, live-only bugs found by actually clicking through every tab and satellite feature,
not by re-reading code — same discipline as `0.1.2`:

- **Adding a cost from the incident's own Cost tab 404'd.** Same root cause as `0.1.2`'s task
  controller: `front/securityincidentcost.form.php` never existed, and the Cost tab's own "add a
  cost" subform posts there by convention (`front/changecost.form.php`/`front/ticketcost.form.php`
  are the core analogues). Added, delegating to core's shared `front/commonitilcost.form.php`.
- **Linking an asset via the "Item" tab fataled outright** (`RuntimeException: Collection class
  RulePluginSecurityincidentsSecurityIncidentCollection does not exists for rule type
  PluginSecurityincidentsSecurityIncident`). Two things were missing, not one:
  1. `front/securityincident_item.form.php` — same missing-front-controller pattern as above,
     fixed the same way (delegates to core's `front/commonitilobject_item.form.php`).
  2. `CommonItilObject_Item::prepareInputForAdd()` unconditionally calls
     `CommonITILObject::getRuleCollectionClassInstance()`, which requires a
     `Rule<Type>Collection` class to exist — an eighth previously-undiscovered core convention
     for this plugin (see the class-naming note below). Added
     `RulePluginSecurityincidentsSecurityIncident(Collection)`, minimal shape mirroring core's own
     `RuleChange`/`RuleChangeCollection`, plus the `rule_securityincident` right seeded (and
     cleaned up on uninstall) so Super-Admin isn't silently denied if a business-rule
     administration UI is ever built on top of this.

     Getting these two classes to actually load took a second discovery: GLPI's own legacy plugin
     autoloader (`src/autoload/legacy-autoloader.php`) only resolves a class name starting with
     `Plugin` — confirmed by reading it. `RulePluginSecurityincidentsSecurityIncident(Collection)`
     cannot be renamed to fit that convention (core computes the exact expected name itself by
     string concatenation), so both classes are now `require_once` explicitly from `setup.php`
     instead of relying on autoloading — the one exception to this plugin's usual "legacy classes
     just autoload from inc/" convention.

Verified live for both: a cost entry and an asset link were created for real through their actual
forms and confirmed in the database. Added a PHPUnit regression test for the asset-link crash
(directly exercises `getRuleCollectionClassInstance()`, unlike the missing-front-controller bugs,
which only a real HTTP request can catch — see `CONTRIBUTING.md`).

## [0.1.2] - 2026-09-09

### Fixed

- **Adding a task from an incident's own timeline 404'd.** `front/securityincidenttask.form.php`
  never existed — every core ITIL type has one (`front/changetask.form.php`,
  `front/tickettask.form.php`), the timeline's "add a task" action posts to it by convention
  (`<itemtype-in-lowercase>task.form.php`), and nothing surfaced the gap until a task was actually
  submitted through the timeline. Added, delegating to GLPI core's shared
  `front/commonitiltask.form.php` like every core ITIL task controller does. Verified live: task
  created, persisted with the correct fields, and shows up in the incident's timeline.

### Added

- Test coverage for `GithubVersionChecker` (cache hit / cached-empty-string-means-no-version), and
  a CI check that desinstalling the plugin leaves no residual columns on `glpi_itilcategories`/
  `glpi_profiles` either — the existing check only looked at `glpi_entities`, a gap since the
  `0.1.1` fix added matching columns there too.

## [0.1.1] - 2026-09-08

### Fixed

Found by actually clicking through the plugin end-to-end after the initial build, not by
inspection — a reminder that "the code compiles and CI is green" is not the same claim as
"a real admin can use this":

- **`Unknown "csrf_field" function` fataled both custom tabs (Analysis, CVE) the moment either
  was opened.** `csrf_field()` was never a real Twig function in GLPI core — invented, not copied
  from a working example. The real, confirmed-working pattern (read from a core template,
  `templates/pages/tools/kb/comment_form.html.twig`) is a plain hidden input reading the
  `csrf_token()` function: `<input type="hidden" name="_glpi_csrf_token" value="{{ csrf_token() }}">`.
  Fixed in both `templates/tabs/analysis.html.twig` and `templates/tabs/cve.html.twig`.
- **`Undefined array key "pluginsecurityincidentssecurityincidenttemplates_id"`** on every
  incident form — a seventh GLPI core convention this plugin hadn't discovered yet:
  `CommonITILObject::getITILTemplateToUse()` reads `$categ->fields[$field]` (an `ITILCategory`,
  no `isset()` guard) and `$_SESSION['glpiactiveprofile'][$field]`, both expecting a column named
  `strtolower(static::class) . 'templates_id'` — same derivation as the `glpi_entities` columns
  already added, but on `glpi_itilcategories` and `glpi_profiles` too (mirroring the native
  `changetemplates_id`/`problemtemplates_id` columns on both tables, confirmed against a real
  GLPI 11 schema). Added by `Install\Installer` (with an index, matching the native columns'
  shape), dropped on uninstall.
- **Two competing "security incident" menu entries confused testing, but neither was actually a
  bug in this plugin**: the still-active `glpi-vulnerability-manager` plugin (the one this project
  is meant to replace, per the project plan) registers its own "Tickets sécurité" shortcut under
  Assistance — a plain `Ticket` search filtered by category, not an autonomous object. Sitting
  next to this plugin's own, real "Security incidents" menu entry, it read exactly like "the new
  plugin still files into normal tickets." Deactivating `vulnerability-manager` removed the
  confusion; no code change was needed in this plugin. (`glpi-vulnerability-manager`'s repository
  itself is not touched by this — that decision stays separately gated, per the project plan.)
- **Every plural label rendered in English regardless of session language** — `locales/*.po`
  wrote each plural pair (`"Security incident"`/`"Security incidents"`, etc.) as two independent
  `msgid`/`msgstr` entries with no `Plural-Forms` header, which is not valid gettext plural syntax
  and cannot be matched by `_n()`/`ngettext()`. Rewrote all 5 languages with real
  `msgid`/`msgid_plural`/`msgstr[0]`/`msgstr[1]` blocks and a `Plural-Forms: nplurals=2;
  plural=(n > 1);` header (same convention read from assetsign-glpi's own locale files), and
  compiled the missing `.mo` files (`msgfmt`) — GLPI's plugin loader only reads compiled `.mo`,
  never `.po` directly. Also had to clear GLPI's own translation cache
  (`files/_cache/*/translations/`, separate from the Twig template cache) for the fix to take
  effect on an already-running instance — a stale compiled catalog kept serving the old, broken
  lookup even after a corrected `.mo` was in place.

### Added

- Initial plugin skeleton: `PluginSecurityincidentsSecurityIncident` as a native
  `CommonITILObject` (Assistance menu), with its own actors
  (`PluginSecurityincidentsSecurityIncident_User`/`_Group`/`_Supplier`), asset linkage
  (`PluginSecurityincidentsSecurityIncident_Item`), tasks
  (`PluginSecurityincidentsSecurityIncidentTask`), notifications
  (`PluginSecurityincidentsNotificationTargetSecurityIncident`), an object template
  (`PluginSecurityincidentsSecurityIncidentTemplate` and its hidden/mandatory/readonly/predefined-
  field satellites — required by GLPI core, not optional, see the note below), an "Analysis" tab
  (impact / controls applied / rollback plan), and CVE reference tracking
  (`PluginSecurityincidentsSecurityIncidentCve`, one or more `CVE-YYYY-NNNN` identifiers per
  incident).
- A Configuration screen (`front/config.php`, wrench icon on **Configuration > Plugins**) showing
  the installed version next to the latest GitHub release (`GithubVersionChecker`, same mechanism
  as the sibling plugins), and a repo/CI setup brought up to parity with them: `release.yml`
  (building and publishing an installable distribution archive — this plugin had none before),
  `securityincidents.xml` (marketplace catalogue descriptor), `docker-compose.test.yml`, and the
  standard set of contributor docs (`CONTRIBUTING.md`, `ROADMAP.md`, `SUPPORT.md`,
  `CODE_OF_CONDUCT.md`, bilingual `README.md`/`README.en.md`).

### Fixed

Four more real, reproduced-live GLPI 11 core surprises found while verifying end-to-end that a
created incident actually notifies anyone (see the naming-convention note below for the first two
found earlier in the same investigation):

- **`CommonITILObject`'s status-array methods default to an empty array** ("to be overridden by
  class") — left unoverridden, `NotificationTargetCommonITILObject::getDataForObject()` merges
  `getSolvedStatusArray()`/`getClosedStatusArray()` into a SQL `NOT IN (...)` clause and fatals with
  "Empty IN are not allowed" the moment a real notification is raised. Added
  `getAllStatusArray()`/`getClosedStatusArray()`/`getSolvedStatusArray()`/`getNewStatusArray()`/
  `getProcessStatusArray()` overrides using the base, universally-shared lifecycle constants
  (`INCOMING`/`ASSIGNED`/`PLANNED`/`WAITING`/`SOLVED`/`CLOSED`).
- **A `PluginSecurityincidentsSecurityIncidentCost` class is required, not optional** — same class
  of surprise as the Template requirement below:
  `NotificationTargetCommonITILObject::getDataForObject()` unconditionally builds
  `$item->getType() . 'Cost'` and calls `$costtype::getCostsSummary(...)` on it with **no existence
  check at all**. Added, mirroring GLPI core's own minimal `ChangeCost`, plus its own tab and table.
- **Several satellite tables used the wrong foreign-key column name.** `securityincidents_id`
  (chosen by hand) was never what GLPI core actually expects: `CommonITILObject::
  getAssociatedDocumentsCriteria()` (used to list a task's associated documents) builds its `WHERE`
  from `$this->getForeignKeyField()`, which — because it derives from the class's own `getTable()`,
  not a hand-picked property — is `plugin_securityincidents_securityincidents_id`. Renamed
  everywhere (tables, class properties, tests, front controllers, the CVE tab template) to match.
- **`Install\Installer` never seeded a `Notification`/`NotificationTemplate` row for any of the
  four ITIL lifecycle events** — without one, `NotificationEvent::raiseEvent()` (called from
  `post_addItem()`/`post_updateItem()`) silently does nothing; a real incident could be created with
  no email ever queued. One shared template for all four events plus their `Notification`/
  `NotificationTarget` rows are now seeded at install (target `items_id`/`type` values copied
  verbatim from a real GLPI 11 install's own native `Change` notification rows — confirmed the two
  numbering schemes involved are easy to confuse and only one is correct). Firing a notification
  for real also requires two GLPI core settings together (`use_notifications` *and*
  `notifications_mailing` — either one alone still queues nothing) and a real requester actor with
  a real email address on the "AUTHOR" notification target — none of this is this plugin's own
  responsibility to configure, but the test suite explicitly sets all three up itself rather than
  assume them, since a freshly auto-installed GLPI instance (confirmed live in this project's own
  CI, on a from-scratch `glpi/glpi:11.0.8` container) has neither on by default.
- **`ALLSTANDARDRIGHT` alone is not enough for an ITIL object's Super-Admin grant.**
  `PluginSecurityincidentsSecurityIncident::getRights()` adds `self::READALL` (bit 1024, distinct
  from the base `READ`/`READMY` bit it replaces) — granting only `ALLSTANDARDRIGHT` (31) at install
  left even Super-Admin with a real 403 viewing an incident they hadn't personally created or been
  assigned to. `Profile::install()` now grants `ALLSTANDARDRIGHT | PluginSecurityincidentsSecurityIncident::READALL`.

Verified for real after every fix above: creating an incident now queues a genuine
"New security incident" notification, and changing its status to solved queues
"Security incident solved" — the full chain (status arrays, seeded notification, the Cost/Template
classes core instantiates by convention, and the Super-Admin rights grant) confirmed working
together on a real GLPI 11 instance, not just individually. Regression tests added for all five.

### Note on the class naming convention

Every class hooking into GLPI's `CommonITILObject`/`ITILTemplate`/`CommonITILTask` conventions
lives in `inc/` under the **legacy global-namespace** `PluginSecurityincidentsXxx` convention,
*not* this plugin's own PSR-4 `GlpiPlugin\Securityincidents\*` (used everywhere else — `Install\
Installer`, `Profile`, `Parameters\SecurityIncidentParameters`). This was not the original plan —
it was forced by two real, reproduced-live GLPI 11 core limitations that assume a non-namespaced
class name:

1. `CommonITILObject::getAdditionalMenuLinks()` resolves the object's template class by string
   concatenation (`static::class . 'Template'`) and fatals with a `ClassNotFoundError` on every
   page if that class doesn't exist — a PSR-4-namespaced `SecurityIncidentTemplate` would need to
   live in the *same* namespace as `SecurityIncident` for this to resolve, which is what the
   PSR-4 version originally did, and it still wasn't enough for the next point.
2. `CommonITILObject::getITILTemplateToUse()` / `Entity::getUsedConfig()` build the per-entity
   "default template strategy" column name via `strtolower($this->getType())` with **no
   namespace-awareness at all** — for a PSR-4-namespaced class this produced a literal SQL column
   name containing backslashes (`glpiplugin\securityincidents\securityincidenttemplates_strategy`)
   that `glpi_entities` obviously doesn't have, crashing every page again.

Every other GLPI plugin extending a core ITIL/CommonDBTM convention this deeply (this author's own
`glpi-grc-manager`/`glpi-iso27001-management` included) already uses this same legacy convention —
confirmed by reading their source, not assumed. `src/` stays PSR-4 for the handful of classes that
never touch these conventions.

Table names for `PluginSecurityincidentsSecurityIncidentTemplate` and its four satellites are
explicitly shortened (`getTable()` overridden) — the class-name-derived default exceeds MySQL's
64-character identifier limit.
