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
 * Required, not optional: `CommonITILObject::getAdditionalMenuLinks()` (called on every page's
 * menu rendering) resolves this class by naming CONVENTION (`static::class . 'Template'`) and
 * fatals with a `ClassNotFoundError` if it doesn't exist — confirmed the hard way. Mechanically
 * mirrors GLPI core's own `ChangeTemplate` (the closest native analogue).
 */
class PluginSecurityincidentsSecurityIncidentTemplate extends ITILTemplate
{
    // The default table name GLPI would derive from this class name
    // (`glpi_plugin_securityincidents_securityincidenttemplates`, and longer still for the four
    // satellite classes below) exceeds MySQL's 64-character identifier limit — shortened here and
    // on every satellite class.
   public static function getTable($classname = null) {
       return 'glpi_plugin_securityincidents_templates';
   }

   public static function getPredefinedFields(): ITILTemplatePredefinedField {
       return new PluginSecurityincidentsSecurityIncidentTemplatePredefinedField();
   }

   public static function getTypeName($nb = 0) {
       return _n('Security incident template', 'Security incident templates', $nb, 'securityincidents');
   }

   public static function getSectorizedDetails(): array {
       return ['helpdesk', PluginSecurityincidentsSecurityIncident::class, self::class];
   }

   public function cleanDBonPurge() {
       $this->deleteChildrenAndRelationsFromDb([
           PluginSecurityincidentsSecurityIncidentTemplateHiddenField::class,
           PluginSecurityincidentsSecurityIncidentTemplateMandatoryField::class,
           PluginSecurityincidentsSecurityIncidentTemplatePredefinedField::class,
           PluginSecurityincidentsSecurityIncidentTemplateReadonlyField::class,
       ]);
   }
}
