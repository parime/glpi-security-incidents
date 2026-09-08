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
 * Required, not optional — same class of surprise as `PluginSecurityincidentsSecurityIncidentTemplate`
 * (see its own docblock): `NotificationTargetCommonITILObject::getDataForObject()` unconditionally
 * builds `$item->getType() . 'Cost'` and calls `$costtype::getCostsSummary(...)` on it with no
 * existence check at all — confirmed the hard way, the first real notification fired after
 * `PluginSecurityincidentsSecurityIncidentTemplate` was added still fataled with
 * `Class "PluginSecurityincidentsSecurityIncidentCost" not found`. Same minimal shape as GLPI
 * core's own `ChangeCost`.
 */
class PluginSecurityincidentsSecurityIncidentCost extends CommonITILCost
{
   public static $itemtype = PluginSecurityincidentsSecurityIncident::class;

   public static $items_id = 'plugin_securityincidents_securityincidents_id';

   public static function canCreate(): bool {
       return Session::haveRight('plugin_securityincidents_securityincident', UPDATE);
   }

   public static function canView(): bool {
       return Session::haveRightsOr('plugin_securityincidents_securityincident', [
           PluginSecurityincidentsSecurityIncident::READALL,
           PluginSecurityincidentsSecurityIncident::READMY,
       ]);
   }

   public static function canUpdate(): bool {
       return Session::haveRight('plugin_securityincidents_securityincident', UPDATE);
   }
}
