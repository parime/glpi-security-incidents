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
 * Relation between PluginSecurityincidentsSecurityIncident and Group — same minimal shape as GLPI
 * core's own `Change_Group`.
 */
class PluginSecurityincidentsSecurityIncident_Group extends CommonITILActor
{
   public static $itemtype_1 = PluginSecurityincidentsSecurityIncident::class;

   public static $items_id_1 = 'securityincidents_id';

   public static $itemtype_2 = Group::class;

   public static $items_id_2 = 'groups_id';
}
