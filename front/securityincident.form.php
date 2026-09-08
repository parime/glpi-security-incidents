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

// Same shape as GLPI core's own front/change.form.php (the closest native analogue) — add/update/
// delete/restore/purge plus the two "add myself as actor" shortcuts, trimmed of Change-specific
// branches (Kanban, cross-linking from an existing Ticket/Problem) not needed by this plugin yet.

if (empty($_GET['id'])) {
    $_GET['id'] = '';
}

if (isset($_POST['_actors'])) {
    $_POST['_actors'] = json_decode((string) $_POST['_actors'], true);
    $_REQUEST['_actors'] = $_POST['_actors'];
}

$securityIncident = new PluginSecurityincidentsSecurityIncident();

if (isset($_POST['add'])) {
    $securityIncident->check(-1, CREATE, $_POST);
    $newId = $securityIncident->add($_POST);
   if ($_SESSION['glpibackcreated']) {
       Html::redirect($securityIncident->getLinkURL());
   } else {
       Html::back();
   }
} else if (isset($_POST['delete'])) {
    $securityIncident->check($_POST['id'], DELETE);
    $securityIncident->delete($_POST);
    $securityIncident->redirectToList();
} else if (isset($_POST['restore'])) {
    $securityIncident->check($_POST['id'], DELETE);
    $securityIncident->restore($_POST);
    $securityIncident->redirectToList();
} else if (isset($_POST['purge'])) {
    $securityIncident->check($_POST['id'], PURGE);
    $securityIncident->delete($_POST, true);
    $securityIncident->redirectToList();
} else if (isset($_POST['update'])) {
    $securityIncident->check($_POST['id'], UPDATE);
    $securityIncident->update($_POST);
    Html::back();
} else if (isset($_POST['addme_observer'])) {
    $securityIncident->check($_POST['securityincidents_id'], READ);
    $securityIncident->update(array_merge($securityIncident->fields, [
        'id' => $_POST['securityincidents_id'],
        '_itil_observer' => [
            '_type' => 'user',
            'users_id' => Session::getLoginUserID(),
            'use_notification' => 1,
        ],
    ]));
    Html::redirect(PluginSecurityincidentsSecurityIncident::getFormURLWithID($_POST['securityincidents_id']));
} else if (isset($_POST['addme_assign'])) {
    $securityIncident->check($_POST['securityincidents_id'], READ);
    (new PluginSecurityincidentsSecurityIncident_User())->add([
        'securityincidents_id' => $_POST['securityincidents_id'],
        'users_id' => Session::getLoginUserID(),
        'use_notification' => 1,
        'type' => CommonITILActor::ASSIGN,
    ]);
    Html::redirect(PluginSecurityincidentsSecurityIncident::getFormURLWithID($_POST['securityincidents_id']));
} else {
    $menus = ['helpdesk', PluginSecurityincidentsSecurityIncident::class];
    PluginSecurityincidentsSecurityIncident::displayFullPageForItem((int) ($_REQUEST['id'] ?? 0), $menus, $_REQUEST);
    Html::footer();
}
