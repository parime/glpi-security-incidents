<?php

/**
 * -------------------------------------------------------------------------
 * Security Incidents plugin for GLPI
 * Copyright (C) 2026 Vincent GUILLOTTE
 * https://github.com/parime/glpi-security-incidents
 * -------------------------------------------------------------------------
 * LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version. See LICENSE for the full text.
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

// GLPI does not autoload plugin src/ classes on its own — same convention as every sibling plugin
// by this author (assetsign-glpi, Configuration-glpi-auto): `composer install` must be run after
// cloning, and any release package must bundle vendor/. This covers only src/Install/Installer.php
// and src/Profile.php (PSR-4) — the ITIL object itself and its satellites live in inc/, GLPI's own
// legacy plugin autoloader (see PluginSecurityincidentsSecurityIncident's own docblock for why).
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// GLPI's own legacy autoloader (src/autoload/legacy-autoloader.php) only resolves a class whose
// name starts with "Plugin" — confirmed by reading it, not guessed. `RulePluginSecurityincidents-
// SecurityIncident(Collection)` cannot be renamed to fit that convention: GLPI core computes the
// exact expected name itself, by string concatenation
// (`CommonITILObject::getRuleCollectionClassInstance()`), so the class name is fixed. Required
// explicitly here instead — confirmed live, `is_a($expected, ..., true)` silently failed to find
// either class without this, throwing a `RuntimeException` the first time an asset was linked to
// an incident (the "Item" tab).
require_once __DIR__ . '/inc/rulesecurityincident.class.php';
require_once __DIR__ . '/inc/rulesecurityincidentcollection.class.php';

define('PLUGIN_SECURITYINCIDENTS_VERSION', '0.1.5');
define('PLUGIN_SECURITYINCIDENTS_MIN_GLPI', '11.0.0');
define('PLUGIN_SECURITYINCIDENTS_MAX_GLPI', '11.99.99');
define('PLUGIN_SECURITYINCIDENTS_MIN_PHP', '8.2.0');

function plugin_init_securityincidents(): void {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['securityincidents'] = true;

    // Native "Assistance" sector, alongside Ticket/Problem/Change — no core patch needed, any
    // itemtype key not already claimed by another entry in that sector's array is simply appended
    // (confirmed by reading GLPI core's Html::generateMenuSession()).
    $PLUGIN_HOOKS[Hooks::MENU_TOADD]['securityincidents'] = [
        'helpdesk' => [PluginSecurityincidentsSecurityIncident::class],
    ];

    $PLUGIN_HOOKS[Hooks::USE_MASSIVE_ACTION]['securityincidents'] = true;

    // Wrench icon on Configuration > Plugins — installed-vs-latest-GitHub-release version check,
    // same convention as the sibling plugins (Configuration-glpi-auto, glpi-iso27001-management).
    $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['securityincidents'] = 'front/config.php';

    // Dashboard cards (total/open incident counts, breakdown by entity/category) — see
    // plugin_securityincidents_dashboard_cards()'s own docblock in hook.php for the accumulator-
    // chain gotcha this must respect.
    $PLUGIN_HOOKS[Hooks::DASHBOARD_CARDS]['securityincidents'] = 'plugin_securityincidents_dashboard_cards';
}

function plugin_version_securityincidents(): array {
    return [
        'name' => 'Security Incidents',
        'version' => PLUGIN_SECURITYINCIDENTS_VERSION,
        'author' => 'Vincent GUILLOTTE',
        'license' => 'GPLv3',
        'homepage' => 'https://github.com/parime/glpi-security-incidents',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_SECURITYINCIDENTS_MIN_GLPI,
                'max' => PLUGIN_SECURITYINCIDENTS_MAX_GLPI,
            ],
            'php' => [
                'min' => PLUGIN_SECURITYINCIDENTS_MIN_PHP,
            ],
        ],
    ];
}

function plugin_securityincidents_check_prerequisites(): bool {
   if (version_compare(PHP_VERSION, PLUGIN_SECURITYINCIDENTS_MIN_PHP, '<')) {
       echo sprintf('This plugin requires PHP %s or later.', PLUGIN_SECURITYINCIDENTS_MIN_PHP);

       return false;
   }

   if (defined('GLPI_VERSION')
        && (
            version_compare(GLPI_VERSION, PLUGIN_SECURITYINCIDENTS_MIN_GLPI, '<')
            || version_compare(GLPI_VERSION, PLUGIN_SECURITYINCIDENTS_MAX_GLPI, '>')
        )
    ) {
       echo sprintf(
           'This plugin requires GLPI %s minimum (up to %s).',
           PLUGIN_SECURITYINCIDENTS_MIN_GLPI,
           PLUGIN_SECURITYINCIDENTS_MAX_GLPI
       );

       return false;
   }

    return true;
}

function plugin_securityincidents_check_config(bool $verbose = false): bool {
    return true;
}
