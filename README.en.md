# Security Incidents — security incidents as a native GLPI ITIL object

> **⚠️ Archived repository.** This plugin has been absorbed into
> [GLPI GRC Manager](https://github.com/parime/glpi-iso27001-management) (v2.0.0): the "Security
> Incident" ITIL object (actors, workflow, tasks, notifications, CVE tracking, incident templates)
> described below now lives there unchanged, merged with the ISO 27001 compliance fields
> (category, severity, root cause/lessons learned). Use GLPI GRC Manager for any new install.
> **Note**: GLPI GRC Manager's built-in automatic migration only covers its own former internal
> register — an install of **this** plugin keeps its data in its own tables
> (`glpi_plugin_securityincidents_*`), which are not picked up automatically; contact the author if
> you need help migrating real data. This repository is no longer maintained.

Created by **Vincent GUILLOTTE**.

[🇫🇷 Français](README.md) | 🇬🇧 **English**

## Table of contents

- [Why a new object instead of a Ticket type?](#why-a-new-object-instead-of-a-ticket-type)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Updating the plugin](#updating-the-plugin)
- [Configuration](#configuration)
- [License](#license)

## Why a new object instead of a Ticket type?

A security incident (unauthorized access, data leak, malware, phishing...) has a different
lifecycle from a regular support ticket: it needs a dedicated analysis (impact, controls applied,
rollback/containment plan), stays searchable as its own register rather than buried in the
general ticket volume, and shouldn't be creatable by accident through the same form as "my
printer doesn't work".

This plugin adds `SecurityIncident` as a first-class ITIL object, **the same core mechanism as
Ticket/Problem/Change** — not a category, not a ticket type, not a filter. Creating a security
incident never creates a Ticket under the hood; that's a deliberate distinction, verified in real
conditions (see [CHANGELOG.md](CHANGELOG.md)).

This is a different, complementary concept from:
- `glpi-iso27001-management`'s own compliance-register entry for information security incidents
  (ISO 27001 clause A.5.24-27) — that is a compliance record, this is the operational workflow
  (assign, investigate, resolve).
- `glpi-vulnerability-manager`'s CVE/vulnerability matching — this plugin only lets an analyst
  **associate** a CVE reference with an incident (see below), it does not scan or match anything
  itself.

## Features

- A full `SecurityIncident` ITIL object: status/priority/urgency/impact, requester/observer/assign
  actors (users, groups, suppliers), tasks, costs, asset linkage, notepad, history.
- A dedicated **Analysis** tab (impact, controls applied, rollback/containment plan).
- **CVE** reference tracking, including **bulk association**: paste several `CVE-YYYY-NNNN`
  identifiers at once (one per line and/or comma-separated), format validated on input,
  duplicates deduplicated automatically.
- Configurable **incident templates**: mandatory, hidden, read-only or predefined-value fields,
  per category — the same screen as a native ticket template.
- Native **dashboard cards** (total incidents, open incidents, breakdown by entity and by
  category), available from "Add a card" on any GLPI dashboard.
- Native notifications (new / update / solved / closed), pre-configured at install.
- A configuration screen (wrench icon on **Configuration > Plugins**) showing the installed
  version next to the latest version published on GitHub.

See [USER_GUIDE.md](USER_GUIDE.md) for a walkthrough of every screen.

## Requirements

- GLPI 11.0.x
- PHP 8.2+
- MariaDB / MySQL
- Composer **only if you're developing on the plugin** — **not required to install it**, see
  below.

## Installation

### Recommended method: release archive (no Git or Composer required)

Ideal for a production GLPI instance, including a minimal Docker container.

1. Download `glpi-security-incidents-X.Y.Z.zip` from the
   [GitHub Releases](https://github.com/parime/glpi-security-incidents/releases).
2. Extract the archive into your GLPI instance's `plugins/` folder:
   ```bash
   cd /path/to/glpi/plugins
   unzip glpi-security-incidents-X.Y.Z.zip
   ```
   The archive already contains a `securityincidents/` folder at its root, with `vendor/` included
   (no `composer install` step needed on the target server) — nothing to rename.
3. Install and activate, from the interface (**Setup > Plugins**, search for
   "Security Incidents") or from the command line:
   ```bash
   php bin/console plugin:install securityincidents
   php bin/console plugin:activate securityincidents
   ```

### Developer method: `git clone`

Convenient for a later `git pull` on update, but requires Composer on the machine you clone onto
(`vendor/` is not versioned):

```bash
cd /path/to/glpi/plugins
git clone https://github.com/parime/glpi-security-incidents.git securityincidents
cd securityincidents
composer install --no-dev
```

Important in both cases: the folder must be named exactly **`securityincidents`** — GLPI derives
the plugin key (`plugin_version_securityincidents()`, etc.) from the folder name under `plugins/`.

### Granting rights to the profiles that need it

After a fresh install, only the **Super-Admin** profile has access to the plugin. To grant access
to another profile: **Administration > Profiles**, open the relevant profile, the
**"Security incident"** tab, check the rights you want and save.

## Updating the plugin

1. Fetch the new code on the GLPI server, the same way you installed it (`git pull`, or
   re-download the ZIP archive, replacing the `securityincidents/` folder with its contents).
2. Re-run the migration and reactivate:
   ```bash
   php bin/console plugin:install securityincidents --force
   php bin/console plugin:activate securityincidents
   php bin/console cache:clear
   ```
   Clearing the cache is required whenever a `.twig` file changed (Analysis tab, CVE tab,
   configuration screen) — in a production environment, GLPI never automatically reloads a
   compiled template without this explicit clear.

## Configuration

An **Assistance > Security incidents** menu entry appears, along with a wrench icon on the
plugin's row in **Setup > Plugins** leading to a screen showing the installed version next to the
latest version published on GitHub, plus a reminder of where notification settings live
(**Setup > Notifications**, native to GLPI — this plugin does not duplicate that screen).

## License

GPL-3.0-or-later — see [LICENSE](LICENSE).
