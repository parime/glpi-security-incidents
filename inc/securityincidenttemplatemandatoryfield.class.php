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

/**
 * Required by `ITILTemplate` (naming-convention resolved) — same minimal shape as GLPI core's own
 * `ChangeTemplateMandatoryField`.
 */
class PluginSecurityincidentsSecurityIncidentTemplateMandatoryField extends ITILTemplateMandatoryField
{
   public static function getTable($classname = null) {
       return 'glpi_plugin_securityincidents_templates_mandatoryfields';
   }

   public static $itemtype = PluginSecurityincidentsSecurityIncidentTemplate::class;

   public static $items_id = 'securityincidenttemplates_id';

   public static $itiltype = PluginSecurityincidentsSecurityIncident::class;
}
