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

namespace GlpiPlugin\Securityincidents\Install;

use DBConnection;
use GlpiPlugin\Securityincidents\Profile;
use Migration;
use Notification;
use NotificationTemplate;
use PluginSecurityincidentsSecurityIncident;
use Toolbox;

/**
 * Handles plugin install/uninstall. Table shapes mirror GLPI core's own `glpi_changes` and its
 * satellite tables (`glpi_changes_users`, `glpi_changes_items`, `glpi_changetasks`) — the closest
 * native analogue, read directly from a live GLPI 11 instance's own schema before writing this,
 * not guessed. `CREATE TABLE` guarded by `tableExists()` (idempotent on re-install), `addField()`/
 * `addKey()` guarded by `fieldExists()` for future evolutions — same pattern as every sibling
 * plugin by this author.
 */
final class Installer
{
   private const INCIDENTS_TABLE = 'glpi_plugin_securityincidents_securityincidents';

   private const INCIDENTS_USERS_TABLE = 'glpi_plugin_securityincidents_securityincidents_users';

   private const INCIDENTS_GROUPS_TABLE = 'glpi_plugin_securityincidents_securityincidents_groups';

   private const INCIDENTS_SUPPLIERS_TABLE = 'glpi_plugin_securityincidents_securityincidents_suppliers';

   private const INCIDENTS_ITEMS_TABLE = 'glpi_plugin_securityincidents_securityincidents_items';

   private const TASKS_TABLE = 'glpi_plugin_securityincidents_securityincidenttasks';

   private const CVES_TABLE = 'glpi_plugin_securityincidents_securityincidentcves';

   private const COSTS_TABLE = 'glpi_plugin_securityincidents_securityincidentcosts';

    // Shortened (not mirroring the class names) — the class-name-derived default exceeds MySQL's
    // 64-character table name limit, see SecurityIncidentTemplate::getTable()'s own docblock.
   private const TEMPLATES_TABLE = 'glpi_plugin_securityincidents_templates';

   private const TEMPLATES_PREDEFINED_FIELDS_TABLE = 'glpi_plugin_securityincidents_templates_predefinedfields';

   private const TEMPLATES_HIDDEN_FIELDS_TABLE = 'glpi_plugin_securityincidents_templates_hiddenfields';

   private const TEMPLATES_MANDATORY_FIELDS_TABLE = 'glpi_plugin_securityincidents_templates_mandatoryfields';

   private const TEMPLATES_READONLY_FIELDS_TABLE = 'glpi_plugin_securityincidents_templates_readonlyfields';

   public function install(Migration $migration): bool {
       global $DB;

       $migration->setVersion(PLUGIN_SECURITYINCIDENTS_VERSION);

       $charset = DBConnection::getDefaultCharset();
       $collation = DBConnection::getDefaultCollation();
       $keySign = DBConnection::getDefaultPrimaryKeySignOption();

      if (!$DB->tableExists(self::INCIDENTS_TABLE)) {
          $query = "CREATE TABLE `" . self::INCIDENTS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `entities_id` int {$keySign} NOT NULL DEFAULT 0,
                `is_recursive` tinyint NOT NULL DEFAULT 0,
                `is_deleted` tinyint NOT NULL DEFAULT 0,
                `status` int NOT NULL DEFAULT 1,
                `content` longtext,
                `date_mod` timestamp NULL DEFAULT NULL,
                `date` timestamp NULL DEFAULT NULL,
                `solvedate` timestamp NULL DEFAULT NULL,
                `closedate` timestamp NULL DEFAULT NULL,
                `time_to_resolve` timestamp NULL DEFAULT NULL,
                `users_id_recipient` int {$keySign} NOT NULL DEFAULT 0,
                `users_id_lastupdater` int {$keySign} NOT NULL DEFAULT 0,
                `urgency` int NOT NULL DEFAULT 1,
                `impact` int NOT NULL DEFAULT 1,
                `priority` int NOT NULL DEFAULT 1,
                `itilcategories_id` int {$keySign} NOT NULL DEFAULT 0,
                `impact_content` longtext,
                `control_list_content` longtext,
                `rollback_plan_content` longtext,
                `actiontime` int NOT NULL DEFAULT 0,
                `begin_waiting_date` timestamp NULL DEFAULT NULL,
                `waiting_duration` int NOT NULL DEFAULT 0,
                `close_delay_stat` int NOT NULL DEFAULT 0,
                `solve_delay_stat` int NOT NULL DEFAULT 0,
                `date_creation` timestamp NULL DEFAULT NULL,
                `locations_id` int {$keySign} NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`),
                KEY `is_recursive` (`is_recursive`),
                KEY `is_deleted` (`is_deleted`),
                KEY `status` (`status`),
                KEY `itilcategories_id` (`itilcategories_id`),
                KEY `date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
            Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::INCIDENTS_TABLE, $DB->error()));

            return false;
         }
      }

      if (!$DB->tableExists(self::INCIDENTS_USERS_TABLE)) {
          $query = "CREATE TABLE `" . self::INCIDENTS_USERS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `users_id` int {$keySign} NOT NULL DEFAULT 0,
                `type` int NOT NULL DEFAULT 1,
                `use_notification` tinyint NOT NULL DEFAULT 0,
                `alternative_email` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `users_id` (`users_id`),
                KEY `type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::INCIDENTS_USERS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::INCIDENTS_GROUPS_TABLE)) {
          $query = "CREATE TABLE `" . self::INCIDENTS_GROUPS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `groups_id` int {$keySign} NOT NULL DEFAULT 0,
                `type` int NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `groups_id` (`groups_id`),
                KEY `type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::INCIDENTS_GROUPS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::INCIDENTS_SUPPLIERS_TABLE)) {
          $query = "CREATE TABLE `" . self::INCIDENTS_SUPPLIERS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `suppliers_id` int {$keySign} NOT NULL DEFAULT 0,
                `type` int NOT NULL DEFAULT 1,
                `use_notification` tinyint NOT NULL DEFAULT 0,
                `alternative_email` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `suppliers_id` (`suppliers_id`),
                KEY `type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::INCIDENTS_SUPPLIERS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::INCIDENTS_ITEMS_TABLE)) {
          $query = "CREATE TABLE `" . self::INCIDENTS_ITEMS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `itemtype` varchar(100) DEFAULT NULL,
                `items_id` int {$keySign} NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `item` (`itemtype`,`items_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::INCIDENTS_ITEMS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::TASKS_TABLE)) {
          $query = "CREATE TABLE `" . self::TASKS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `uuid` varchar(255) DEFAULT NULL,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `taskcategories_id` int {$keySign} NOT NULL DEFAULT 0,
                `state` int NOT NULL DEFAULT 0,
                `date` timestamp NULL DEFAULT NULL,
                `begin` timestamp NULL DEFAULT NULL,
                `end` timestamp NULL DEFAULT NULL,
                `users_id` int {$keySign} NOT NULL DEFAULT 0,
                `users_id_editor` int {$keySign} NOT NULL DEFAULT 0,
                `users_id_tech` int {$keySign} NOT NULL DEFAULT 0,
                `groups_id_tech` int {$keySign} NOT NULL DEFAULT 0,
                `content` longtext,
                `actiontime` int NOT NULL DEFAULT 0,
                `date_mod` timestamp NULL DEFAULT NULL,
                `date_creation` timestamp NULL DEFAULT NULL,
                `tasktemplates_id` int {$keySign} NOT NULL DEFAULT 0,
                `timeline_position` tinyint NOT NULL DEFAULT 0,
                `is_private` tinyint NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `users_id` (`users_id`),
                KEY `users_id_tech` (`users_id_tech`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::TASKS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::CVES_TABLE)) {
          $query = "CREATE TABLE `" . self::CVES_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `cve_id` varchar(20) NOT NULL DEFAULT '',
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unicity` (`plugin_securityincidents_securityincidents_id`,`cve_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::CVES_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::COSTS_TABLE)) {
          $query = "CREATE TABLE `" . self::COSTS_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `plugin_securityincidents_securityincidents_id` int {$keySign} NOT NULL DEFAULT 0,
                `name` varchar(255) DEFAULT NULL,
                `comment` text,
                `begin_date` date DEFAULT NULL,
                `end_date` date DEFAULT NULL,
                `actiontime` int NOT NULL DEFAULT 0,
                `cost_time` decimal(20,4) NOT NULL DEFAULT 0.0000,
                `cost_fixed` decimal(20,4) NOT NULL DEFAULT 0.0000,
                `cost_material` decimal(20,4) NOT NULL DEFAULT 0.0000,
                `budgets_id` int {$keySign} NOT NULL DEFAULT 0,
                `entities_id` int {$keySign} NOT NULL DEFAULT 0,
                `is_recursive` tinyint NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `plugin_securityincidents_securityincidents_id` (`plugin_securityincidents_securityincidents_id`),
                KEY `entities_id` (`entities_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::COSTS_TABLE, $DB->error()));

             return false;
         }
      }

      if (!$DB->tableExists(self::TEMPLATES_TABLE)) {
          $query = "CREATE TABLE `" . self::TEMPLATES_TABLE . "` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `entities_id` int {$keySign} NOT NULL DEFAULT 0,
                `is_recursive` tinyint NOT NULL DEFAULT 0,
                `comment` text,
                `allowed_statuses` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", self::TEMPLATES_TABLE, $DB->error()));

             return false;
         }
      }

      foreach ([
           self::TEMPLATES_PREDEFINED_FIELDS_TABLE => true,
           self::TEMPLATES_HIDDEN_FIELDS_TABLE => false,
           self::TEMPLATES_MANDATORY_FIELDS_TABLE => false,
           self::TEMPLATES_READONLY_FIELDS_TABLE => false,
       ] as $table => $hasValueColumn) {
         if ($DB->tableExists($table)) {
             continue;
         }

          $valueColumn = $hasValueColumn ? "`value` longtext,\n                " : '';
          $query = "CREATE TABLE `{$table}` (
                `id` int {$keySign} NOT NULL AUTO_INCREMENT,
                `securityincidenttemplates_id` int {$keySign} NOT NULL DEFAULT 0,
                `num` int NOT NULL DEFAULT 0,
                {$valueColumn}PRIMARY KEY (`id`),
                KEY `securityincidenttemplates_id` (`securityincidenttemplates_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}";

         if (!$DB->doQuery($query)) {
             Toolbox::logInFile('sql-errors', sprintf("[securityincidents] %s: %s\n", $table, $DB->error()));

             return false;
         }
      }

       // Required by GLPI core: CommonITILObject::getITILTemplateToUse() / Entity::getUsedConfig()
       // derive these column names directly from strtolower(PluginSecurityincidentsSecurityIncident::class)
       // + 'templates_strategy'/'templates_id' — confirmed live (every page fataled with "Unknown
       // column" until these were added), not a guess. Same shape as the native
       // changetemplates_strategy/_id columns this mirrors (checked against a real GLPI 11
       // instance's own glpi_entities schema).
      if (!$DB->fieldExists('glpi_entities', 'pluginsecurityincidentssecurityincidenttemplates_strategy')) {
          $migration->addField(
              'glpi_entities',
              'pluginsecurityincidentssecurityincidenttemplates_strategy',
              'integer',
              ['value' => -2, 'after' => 'entities_id']
          );
      }
      if (!$DB->fieldExists('glpi_entities', 'pluginsecurityincidentssecurityincidenttemplates_id')) {
          $migration->addField(
              'glpi_entities',
              'pluginsecurityincidentssecurityincidenttemplates_id',
              'integer',
              ['value' => 0, 'after' => 'pluginsecurityincidentssecurityincidenttemplates_strategy']
          );
      }

       // Same convention, two more places: `CommonITILObject::getTemplateFieldName()` derives
       // `strtolower($this->getType()) . 'templates_id'` and `getITILTemplateToUse()` reads it
       // straight off `$categ->fields[$field]` (an ITILCategory, no isset() guard — confirmed live,
       // this produced a real "Undefined array key" warning on every incident form) and off
       // `$_SESSION['glpiactiveprofile'][$field]` (populated verbatim from `glpi_profiles` columns).
       // Same shape as the native `changetemplates_id`/`problemtemplates_id` columns on both tables
       // (checked against a real GLPI 11 instance's own schema).
      if (!$DB->fieldExists('glpi_itilcategories', 'pluginsecurityincidentssecurityincidenttemplates_id')) {
          $migration->addField(
              'glpi_itilcategories',
              'pluginsecurityincidentssecurityincidenttemplates_id',
              'integer',
              ['value' => 0, 'after' => 'problemtemplates_id']
          );
          $migration->addKey('glpi_itilcategories', 'pluginsecurityincidentssecurityincidenttemplates_id');
      }
      if (!$DB->fieldExists('glpi_profiles', 'pluginsecurityincidentssecurityincidenttemplates_id')) {
          $migration->addField(
              'glpi_profiles',
              'pluginsecurityincidentssecurityincidenttemplates_id',
              'integer',
              ['value' => 0, 'after' => 'problemtemplates_id']
          );
          $migration->addKey('glpi_profiles', 'pluginsecurityincidentssecurityincidenttemplates_id');
      }

       Profile::install($migration);

       $migration->executeMigration();

       $this->seedNotifications();

       return true;
   }

    /**
     * Without this, `NotificationEvent::raiseEvent('new'|'update'|'solved'|'closed', $this)`
     * (called from `PluginSecurityincidentsSecurityIncident::post_addItem()`/`post_updateItem()`)
     * finds no active `Notification` row to fire and silently does nothing — confirmed live: an
     * incident could be created with no email ever queued. One shared `NotificationTemplate` for
     * all four events (same pattern as GLPI core's own native `Change` notifications, which all
     * point at the same template id and rely on the `##…action##`/`##…storestatus##` tags to
     * adapt wording per event — read directly from a real GLPI 11 install rather than guessed) and
     * four `Notification` rows. Target `items_id`/`type` values (1, 3, 21, and 1/2/3/4/21 for
     * "update") are copied verbatim from that same real install's own `glpi_notificationtargets`
     * rows for `Change` — `NotificationTargetCommonITILObject::getTargets()` (the shared parent
     * class both `Change`'s and this plugin's own notification target extend) interprets them
     * identically regardless of itemtype, so the exact values GLPI core itself seeds are the
     * correct ones to reuse, not re-derived from the `Notification`/`CommonITILActor` constant
     * lists by hand (the two enumerations look confusingly similar but are not the same list).
     */
   private function seedNotifications(): void {
       $itemtype = PluginSecurityincidentsSecurityIncident::class;
       $tag = strtolower($itemtype);

       $template = new NotificationTemplate();
      if (!$template->getFromDBByCrit(['itemtype' => $itemtype, 'name' => 'Security incident'])) {
          $templateId = $template->add([
              'name' => 'Security incident',
              'itemtype' => $itemtype,
              'comment' => 'Seeded at install by the Security Incidents plugin.',
          ]);

          (new \NotificationTemplateTranslation())->add([
              'notificationtemplates_id' => $templateId,
              'language' => '',
              'subject' => "##{$tag}.action## ##{$tag}.title##",
              'content_text' => "##{$tag}.action## ##{$tag}.title##\n\n"
                  . "##{$tag}.url##\n\n"
                  . "##{$tag}.content##",
              'content_html' => "<p>##{$tag}.action## ##{$tag}.title##</p>"
                  . "<p><a href=\"##{$tag}.url##\">##{$tag}.url##</a></p>"
                  . "<p>##{$tag}.content##</p>",
          ]);
      } else {
          $templateId = (int) $template->getID();
      }

       $eventsAndTargets = [
           'new' => [1, 3, 21],
           'update' => [1, 2, 3, 4, 21],
           'solved' => [1, 3, 21],
           'closed' => [1, 3, 21],
       ];

       foreach ($eventsAndTargets as $event => $targetItemsIds) {
           $notification = new Notification();
          if ($notification->getFromDBByCrit(['itemtype' => $itemtype, 'event' => $event])) {
              continue;
          }

           $notificationId = $notification->add([
               'name' => 'Security incident — ' . $event,
               'entities_id' => 0,
               'is_recursive' => 1,
               'is_active' => 1,
               'itemtype' => $itemtype,
               'event' => $event,
           ]);

           (new \Notification_NotificationTemplate())->add([
               'notifications_id' => $notificationId,
               'notificationtemplates_id' => $templateId,
               'mode' => 'mailing',
           ]);

          foreach ($targetItemsIds as $targetItemsId) {
             (new \NotificationTarget())->add([
                 'notifications_id' => $notificationId,
                 'items_id' => $targetItemsId,
                 'type' => Notification::USER_TYPE,
             ]);
          }
       }
   }

   public function uninstall(Migration $migration): bool {
       global $DB;

       $migration->dropField('glpi_entities', 'pluginsecurityincidentssecurityincidenttemplates_id');
       $migration->dropField('glpi_entities', 'pluginsecurityincidentssecurityincidenttemplates_strategy');
       $migration->dropField('glpi_itilcategories', 'pluginsecurityincidentssecurityincidenttemplates_id');
       $migration->dropField('glpi_profiles', 'pluginsecurityincidentssecurityincidenttemplates_id');
       $migration->executeMigration();

        // Notification::cleanDBonPurge()/NotificationTemplate::cleanDBonPurge() (confirmed by
        // reading GLPI core) already cascade to glpi_notifications_notificationtemplates and
        // glpi_notificationtargets themselves — deleting the Notification/NotificationTemplate
        // rows is enough, no separate cleanup of the join tables needed.
        $itemtype = \PluginSecurityincidentsSecurityIncident::class;
      foreach ((new Notification())->find(['itemtype' => $itemtype]) as $row) {
          (new Notification())->delete(['id' => $row['id']], true);
      }
      foreach ((new NotificationTemplate())->find(['itemtype' => $itemtype]) as $row) {
          (new NotificationTemplate())->delete(['id' => $row['id']], true);
      }

      foreach ([
           self::TEMPLATES_PREDEFINED_FIELDS_TABLE,
           self::TEMPLATES_HIDDEN_FIELDS_TABLE,
           self::TEMPLATES_MANDATORY_FIELDS_TABLE,
           self::TEMPLATES_READONLY_FIELDS_TABLE,
           self::TEMPLATES_TABLE,
           self::COSTS_TABLE,
           self::CVES_TABLE,
           self::TASKS_TABLE,
           self::INCIDENTS_ITEMS_TABLE,
           self::INCIDENTS_SUPPLIERS_TABLE,
           self::INCIDENTS_GROUPS_TABLE,
           self::INCIDENTS_USERS_TABLE,
           self::INCIDENTS_TABLE,
       ] as $table) {
          $DB->doQuery("DROP TABLE IF EXISTS `{$table}`");
      }

       Profile::uninstall();

       return true;
   }
}
