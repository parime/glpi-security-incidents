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

// The search/list page — PluginSecurityincidentsSecurityIncident::getSearchURL() resolves here
// generically (Toolbox::getItemTypeSearchURL(), same convention core uses for
// Ticket/Change/Problem), unlike PluginSecurityincidentsSecurityIncident::displayFullPageForItem()
// (front/securityincident.form.php with no id), which shows the "new item" creation form instead
// of a list.
Session::checkRightsOr(PluginSecurityincidentsSecurityIncident::$rightname, [PluginSecurityincidentsSecurityIncident::READALL, PluginSecurityincidentsSecurityIncident::READMY]);

Html::header(PluginSecurityincidentsSecurityIncident::getTypeName(2), $_SERVER['PHP_SELF'], 'helpdesk', PluginSecurityincidentsSecurityIncident::class);

Search::show(PluginSecurityincidentsSecurityIncident::class);

Html::footer();
