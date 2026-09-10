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

use GlpiPlugin\Securityincidents\Dashboard\CardProvider;
use GlpiPlugin\Securityincidents\Install\Installer;

function plugin_securityincidents_install(): bool {
    $migration = new Migration(PLUGIN_SECURITYINCIDENTS_VERSION);

    return (new Installer())->install($migration);
}

function plugin_securityincidents_uninstall(): bool {
    $migration = new Migration(PLUGIN_SECURITYINCIDENTS_VERSION);

    return (new Installer())->uninstall($migration);
}

/**
 * Renders the Analysis fields (impact/controls/rollback plan) as a native-looking accordion
 * directly in the incident's own field panel — the same visual location as `Change`'s/`Problem`'s
 * own "Analysis"/"Plans" accordions — instead of a separate tab requiring an extra click.
 *
 * That native accordion itself is not reachable for a plugin's own ITIL type: it's gated by a
 * hardcoded `item.getType() in ['Problem', 'Change']` check in
 * `templates/components/itilobject/fields_panel.html.twig`, confirmed by reading it, with no
 * override point. `Hooks::POST_ITIL_INFO_SECTION` is a real, documented extension point in that
 * same template (`call_plugin_hook(...POST_ITIL_INFO_SECTION..., {"item": item, "options":
 * params})`, fired for every itemtype's own form) — this renders nothing unless `$params['item']`
 * is actually one of this plugin's own incidents.
 *
 * The rendered fields are genuine children of the surrounding item form (this hook fires from
 * inside it), so they save together with the rest of the incident when the main "Save" button is
 * clicked — no separate submit button, matching `Change`'s own UX exactly.
 */
function plugin_securityincidents_post_itil_info_section(array $params): void {
    $item = $params['item'] ?? null;
   if (!($item instanceof PluginSecurityincidentsSecurityIncident)) {
       return;
   }

    Glpi\Application\View\TemplateRenderer::getInstance()->display('@securityincidents/itil_analysis_section.html.twig', [
        'item' => $item,
        'rand' => mt_rand(),
    ]);
}

/**
 * `Plugin::doHookFunction(Hooks::DASHBOARD_CARDS)` chains every registered plugin's callback as an
 * accumulator (`$ret = call_user_func($function, $ret)` for each in turn), never merging results
 * itself — confirmed by reading Grid.php, and already the cause of a real bug on the sibling
 * assetsign-glpi plugin (its cards silently discarded another plugin's own cards by not accepting/
 * forwarding `$cards`). `?array $cards = null` (not `array $cards = []`): the first plugin in the
 * chain is called with an explicit `null`, which does not fall through to a default value (PHP
 * only applies a parameter default when the argument is omitted, not when null is passed for a
 * nullable type) — `$cards ??= []` handles that explicitly instead.
 */
function plugin_securityincidents_dashboard_cards(?array $cards = null): array {
    $cards ??= [];

    $itemtype = PluginSecurityincidentsSecurityIncident::class;
    $table = $itemtype::getTable();
    $group = $itemtype::getTypeName(2);
    $filters = \Glpi\Dashboard\Filter::getAppliableFilters($table);

    return $cards + [
        'securityincidents_bn_total' => [
            'widgettype' => ['bigNumber'],
            'itemtype'   => "\\$itemtype",
            'group'      => $group,
            // Not sprintf(__('Number of %s'), ...): composing a generic core phrase with an
            // inserted type name reads badly in French for a name starting with a vowel ("Nombre
            // de Incidents" instead of "Nombre d'incidents") — confirmed live. A complete,
            // properly-elided string in this plugin's own domain avoids that entirely, in every
            // language, not just a French-specific patch.
            'label'      => __('Number of security incidents', 'securityincidents'),
            // Core's own generic provider (Glpi\Dashboard\Provider::__callStatic()) — already
            // handles entity restriction and is_deleted for any CommonDBTM, confirmed by reading
            // it, no need to reimplement this one.
            'provider'   => 'Glpi\\Dashboard\\Provider::bigNumber' . $itemtype,
            'filters'    => $filters,
        ],
        'securityincidents_bn_open' => [
            'widgettype' => ['bigNumber'],
            'itemtype'   => "\\$itemtype",
            'group'      => $group,
            'label'      => _x('security incidents', 'Open', 'securityincidents'),
            'provider'   => CardProvider::class . '::open',
            'filters'    => $filters,
        ],
        'securityincidents_by_entity' => [
            'widgettype' => ['summaryNumbers', 'multipleNumber', 'pie', 'donut', 'halfpie', 'halfdonut', 'bar', 'hbar'],
            'itemtype'   => "\\$itemtype",
            'group'      => $group,
            'label'      => __('Security incidents by entity', 'securityincidents'),
            'provider'   => 'Glpi\\Dashboard\\Provider::multipleNumber' . $itemtype . 'ByEntity',
            'filters'    => $filters,
        ],
        'securityincidents_by_category' => [
            'widgettype' => ['summaryNumbers', 'multipleNumber', 'pie', 'donut', 'halfpie', 'halfdonut', 'bar', 'hbar'],
            'itemtype'   => "\\$itemtype",
            'group'      => $group,
            'label'      => __('Security incidents by category', 'securityincidents'),
            'provider'   => 'Glpi\\Dashboard\\Provider::multipleNumber' . $itemtype . 'ByITILCategory',
            'filters'    => $filters,
        ],
    ];
}
