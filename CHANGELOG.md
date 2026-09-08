# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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
