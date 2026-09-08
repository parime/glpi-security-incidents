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

namespace GlpiPlugin\Securityincidents;

/**
 * Declares `PluginSecurityincidentsSecurityIncident::$rightname` in GLPI's standard profile
 * matrix. Inserts only the missing profile/right pairs (idempotent, unlike a raw INSERT — same fix
 * already applied on every sibling plugin by this author) and grants full standard rights to
 * Super-Admin only, so the plugin is usable immediately after install without opening it up to
 * every profile by default.
 */
class Profile
{
    // Same literal as PluginSecurityincidentsSecurityIncident::$rightname (inc/, legacy
    // global-namespace convention — see that class's own docblock for why) — duplicated rather
    // than referenced, same reasoning as PluginSecurityincidentsSecurityIncidentCve.
   public const SECURITY_INCIDENT_RIGHT = 'plugin_securityincidents_securityincident';

   public static function install(\Migration $migration): void {
       global $DB;

       $right = self::SECURITY_INCIDENT_RIGHT;

       $existing = [];
      foreach ($DB->request(['FROM' => \ProfileRight::getTable(), 'WHERE' => ['name' => $right]]) as $row) {
          $existing[$row['profiles_id']] = true;
      }

      foreach ($DB->request(['FROM' => \Profile::getTable()]) as $profile) {
         if (!isset($existing[$profile['id']])) {
             $DB->insert(\ProfileRight::getTable(), [
                 'profiles_id' => $profile['id'],
                 'name' => $right,
                 'rights' => 0,
             ]);
         }
      }

       // ALLSTANDARDRIGHT alone (READ/UPDATE/CREATE/DELETE/PURGE = 31) is NOT enough for an ITIL
       // object: PluginSecurityincidentsSecurityIncident::getRights() adds READALL (bit 1024, a
       // separate value from the base READ/READMY bit it replaces) on top of the standard set —
       // confirmed the hard way, Super-Admin got a real 403 reading an incident they didn't
       // personally create/get assigned to, since READMY (implied by the standard bits) alone
       // requires being an actor on the item. Same "ALLSTANDARDRIGHT is not the full ITIL right
       // set" gap GLPI core itself avoids for Ticket/Change/Problem by granting a much larger
       // bitmask to Super-Admin at install (confirmed against a real instance: native `change`
       // right for Super-Admin is 132223, not 31).
       $rows = $DB->request(['FROM' => \Profile::getTable(), 'WHERE' => ['name' => 'Super-Admin']]);
      foreach ($rows as $row) {
          \ProfileRight::updateProfileRights((int) $row['id'], [
              $right => ALLSTANDARDRIGHT | \PluginSecurityincidentsSecurityIncident::READALL,
          ]);
      }
   }

   public static function uninstall(): void {
       \ProfileRight::deleteProfileRights([self::SECURITY_INCIDENT_RIGHT]);
   }
}
