# Security Incidents for GLPI

Tracks security incidents (unauthorized access, data leak, malware, phishing...) as a native GLPI
ITIL object, alongside Tickets/Problems/Changes in the "Assistance" menu — with its own actors,
notifications, timeline, and CVE reference tracking, rather than mixing incidents into the general
helpdesk ticket queue or a separate compliance register.

## Why a new object instead of a Ticket type?

A security incident needs its own workflow (assignment, investigation, containment, resolution)
and its own notification templates, distinct from a generic support ticket — and it needs to stay
searchable/reportable as its own thing, not buried in the general ticket volume. This plugin adds
`SecurityIncident` as a first-class `CommonITILObject`, the same mechanism GLPI core uses for
Ticket/Problem/Change.

This is a different, complementary concept from:
- `glpi-iso27001-management`'s own compliance-register entry for information security incidents
  (ISO 27001 clause A.5.24-27) — that is a compliance record, this is the operational workflow.
- `glpi-vulnerability-manager`'s CVE/vulnerability matching — this plugin only lets an analyst
  *reference* a CVE against an incident (see below), it does not scan or match anything itself.

## Features

- A `SecurityIncident` ITIL object: status/priority/urgency/impact, requester/observer/assign
  actors (users, groups, suppliers), tasks, notepad, history.
- An "Analysis" tab (impact, controls applied, rollback/containment plan).
- CVE reference tracking: associate one or more `CVE-YYYY-NNNN` identifiers with an incident.
- Native notifications (new / update / solved / closed).

## Requirements

- GLPI 11.0.0 – 11.99.99
- PHP >= 8.2

## Installation

```bash
cd glpi/plugins
git clone https://github.com/parime/glpi-security-incidents.git securityincidents
cd securityincidents
composer install --no-dev
```

Then install and activate it from GLPI's Configuration > Plugins page (or via
`bin/console plugin:install securityincidents && bin/console plugin:activate securityincidents`).

## License

GPL-3.0-or-later — see [LICENSE](LICENSE).
