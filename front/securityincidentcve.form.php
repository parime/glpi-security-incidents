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

// A tab-embedded child, not a standalone item with its own form page — add/purge only, always
// redirecting back to the parent SecurityIncident's own "CVE" tab.
$item = new PluginSecurityincidentsSecurityIncidentCve();

if (isset($_POST['add'])) {
    $item->check(-1, CREATE, $_POST);
    $item->add($_POST);
} else if (isset($_POST['purge'])) {
    $item->check($_POST['id'], PURGE);
    $item->delete($_POST, true);
}

Html::back();
