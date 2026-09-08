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

use GlpiPlugin\Securityincidents\Services\GithubVersionChecker;

include('../../../inc/includes.php');

Session::checkRight(PluginSecurityincidentsSecurityIncident::$rightname, UPDATE);

Html::header(
    __('Configuration'),
    $_SERVER['PHP_SELF'],
    'helpdesk',
    PluginSecurityincidentsSecurityIncident::class
);

Glpi\Application\View\TemplateRenderer::getInstance()->display('@securityincidents/config.html.twig', [
    'installed_version'     => PLUGIN_SECURITYINCIDENTS_VERSION,
    'latest_github_version' => GithubVersionChecker::getLatestGithubVersion(),
]);

Html::footer();
