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

namespace GlpiPlugin\Securityincidents\Parameters;

use Glpi\ContentTemplates\Parameters\CommonITILObjectParameters;
use PluginSecurityincidentsSecurityIncident;

/**
 * Content-template parameters for PluginSecurityincidentsSecurityIncident items — same minimal
 * shape as GLPI core's own `ChangeParameters`, required by
 * `PluginSecurityincidentsSecurityIncident::getContentTemplatesParametersClassInstance()`.
 */
class SecurityIncidentParameters extends CommonITILObjectParameters
{
   public static function getDefaultNodeName(): string {
       return 'securityincident';
   }

   public static function getObjectLabel(): string {
       return PluginSecurityincidentsSecurityIncident::getTypeName(1);
   }

   protected function getTargetClasses(): array {
       return [PluginSecurityincidentsSecurityIncident::class];
   }
}
