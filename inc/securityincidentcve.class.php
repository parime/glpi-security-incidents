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
 * One CVE reference associated to a PluginSecurityincidentsSecurityIncident — several per
 * incident, native reference feature ported from the sibling plugin `glpi-vulnerability-manager`'s
 * own `cve_id varchar(20)` column precedent (see this plugin's own project plan). Deliberately a
 * standalone plugin table, no dependency on `glpi-vulnerability-manager` itself (that plugin is
 * being retired).
 *
 * Uses `PluginSecurityincidentsSecurityIncident`'s own right rather than a dedicated one: a CVE
 * reference has no meaningful access boundary of its own, separate from the incident it documents.
 */
class PluginSecurityincidentsSecurityIncidentCve extends CommonDBTM
{
    // Same literal as PluginSecurityincidentsSecurityIncident::$rightname — a static property of
    // another class cannot be used as a property default value (not a constant expression), so
    // this is duplicated rather than referenced.
   public static $rightname = 'plugin_securityincidents_securityincident';

   public static function getTypeName($nb = 0) {
       return _n('CVE reference', 'CVE references', $nb, 'securityincidents');
   }

   public static function getIcon() {
       return 'ti ti-bug';
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!($item instanceof PluginSecurityincidentsSecurityIncident) || !PluginSecurityincidentsSecurityIncident::canView()) {
          return '';
      }

       $count = $item->isNewID($item->getID()) ? 0 : countElementsInTable(
           self::getTable(),
           ['securityincidents_id' => $item->getID()]
       );

       return self::createTabEntry(_n('CVE', 'CVEs', $count, 'securityincidents'), $count, $item::class);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if (!($item instanceof PluginSecurityincidentsSecurityIncident)) {
          return false;
      }

       $cves = [];
      if (!$item->isNewID($item->getID())) {
          $cve = new self();
          $cves = $cve->find(['securityincidents_id' => $item->getID()], ['cve_id ASC']);
      }

       Glpi\Application\View\TemplateRenderer::getInstance()->display('@securityincidents/tabs/cve.html.twig', [
           'item' => $item,
           'cves' => $cves,
           'can_edit' => $item->canUpdateItem(),
       ]);

       return true;
   }

    /**
     * Same "trust nothing free-text beyond a shape check" reasoning as every sibling plugin in
     * this author's own ecosystem: a CVE ID has a fixed, well-known shape (`CVE-YYYY-NNNN...`),
     * reject anything else rather than storing an arbitrary string under a field whose whole
     * purpose is cross-referencing a real, externally-verifiable identifier.
     */
   public function prepareInputForAdd($input) {
       return $this->prepareInput($input);
   }

   public function prepareInputForUpdate($input) {
       return $this->prepareInput($input);
   }

    /**
     * @return array<string, mixed>|false
     */
   private function prepareInput(array $input) {
      if (isset($input['cve_id'])) {
          $cveId = strtoupper(trim((string) $input['cve_id']));
         if (!preg_match('/^CVE-\d{4}-\d{4,}$/', $cveId)) {
            Session::addMessageAfterRedirect(
                sprintf(__('"%s" is not a valid CVE identifier (expected format: CVE-YYYY-NNNN).', 'securityincidents'), $input['cve_id']),
                false,
                ERROR
            );

            return false;
         }
          $input['cve_id'] = $cveId;
      }

       return $input;
   }
}
