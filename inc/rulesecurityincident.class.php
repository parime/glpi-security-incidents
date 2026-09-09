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
 * Paired with `RulePluginSecurityincidentsSecurityIncidentCollection` — see that class's own
 * docblock for why this exists. Minimal shape, mirroring GLPI core's own `RuleChange`.
 */
class RulePluginSecurityincidentsSecurityIncident extends RuleCommonITILObject
{
   public static $rightname = 'rule_securityincident';

   public function getTitle() {
       return __('Business rules for security incidents', 'securityincidents');
   }

   public function getTargetItilType(): CommonITILObject {
       return new PluginSecurityincidentsSecurityIncident();
   }
}
