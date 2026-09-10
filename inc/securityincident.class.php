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

use Glpi\ContentTemplates\Parameters\CommonITILObjectParameters;
use GlpiPlugin\Securityincidents\Parameters\SecurityIncidentParameters;

/**
 * A security incident (unauthorized access, data leak, malware, phishing...), as its own native
 * GLPI ITIL object alongside Ticket/Problem/Change — not a Ticket sub-type, since a security
 * incident has its own actors/workflow/notifications and belongs in a dedicated register rather
 * than mixed into the general helpdesk queue (the exact reasoning documented in this plugin's own
 * project plan). Deliberately independent of `glpi-vulnerability-manager`'s and
 * `glpi-iso27001-management`'s own, differently-scoped "security incident" concepts: this class is
 * the operational, day-to-day workflow object (assign, investigate, resolve), not a compliance
 * register entry.
 *
 * Legacy global-namespace `PluginXxxYyy` convention (not this plugin's own PSR-4
 * `GlpiPlugin\Securityincidents\*`, used everywhere else in this plugin) — deliberate, not an
 * oversight: several GLPI core mechanisms that a `CommonITILObject` subclass must hook into derive
 * internal identifiers straight from `strtolower(static::class)` with no namespace-awareness
 * (confirmed the hard way — `CommonITILObject::getITILTemplateToUse()` /
 * `Entity::getUsedConfig()` tried to `SELECT` a column literally named
 * `glpiplugin\securityincidents\securityincidenttemplates_strategy` when this class was
 * PSR-4-namespaced). Every other GLPI plugin extending a core ITIL/CommonDBTM convention this
 * deeply (this author's own glpi-grc-manager included) uses this same legacy convention for
 * exactly that reason — confirmed by reading, not assumed.
 *
 * Modeled directly on GLPI core's own `Change` class (the closest native analogue).
 */
class PluginSecurityincidentsSecurityIncident extends CommonITILObject
{
    // From CommonDBTM
    public $dohistory = true;

    // From CommonITIL
    public $userlinkclass = PluginSecurityincidentsSecurityIncident_User::class;
    public $grouplinkclass = PluginSecurityincidentsSecurityIncident_Group::class;
    public $supplierlinkclass = PluginSecurityincidentsSecurityIncident_Supplier::class;

   public static $rightname = 'plugin_securityincidents_securityincident';

    protected $usenotepad = true;

   public static function getTypeName($nb = 0) {
       return _n('Security incident', 'Security incidents', $nb, 'securityincidents');
   }

   public static function getIcon() {
       return 'ti ti-shield-exclamation';
   }

   public static function getSectorizedDetails(): array {
       return ['helpdesk', self::class];
   }

    /**
     * `CommonITILObject`'s own status-array methods explicitly say "to be overridden by class" and
     * default to an empty array — confirmed the hard way: `handleNewItemNotifications()` fataled
     * with "Empty IN are not allowed" (`getSolvedStatusArray()`/`getClosedStatusArray()` both `[]`,
     * merged into a `NOT IN ()` SQL clause) the first time a real incident was created after
     * notifications were wired up. Uses only the base, universally-shared lifecycle constants
     * (`INCOMING`/`ASSIGNED`/`PLANNED`/`WAITING`/`SOLVED`/`CLOSED`, defined on `CommonITILObject`
     * itself) rather than `Change`'s own richer set (`EVALUATION`/`APPROVAL`/`TEST`/`QUALIFICATION`/
     * `OBSERVED`/`CANCELED`/`REFUSED`) — a security incident's workflow doesn't need change-specific
     * approval/testing/rollback states.
     */
   public static function getAllStatusArray($withmetaforsearch = false) {
       $status = [
           self::INCOMING => _x('status', 'New'),
           self::ASSIGNED => __('Processing (assigned)'),
           self::PLANNED => __('Processing (planned)'),
           self::WAITING => __('Pending'),
           self::SOLVED => __('Solved'),
           self::CLOSED => _x('status', 'Closed'),
       ];

       if ($withmetaforsearch) {
           $status['notold'] = _x('status', 'Not solved');
           $status['notclosed'] = _x('status', 'Not closed');
           $status['process'] = __('Processing');
           $status['old'] = _x('status', 'Solved + Closed');
           $status['all'] = __('All');
       }

       return $status;
   }

   public static function getClosedStatusArray() {
       return [self::CLOSED];
   }

   public static function getSolvedStatusArray() {
       return [self::SOLVED];
   }

   public static function getNewStatusArray() {
       return [self::INCOMING];
   }

   public static function getProcessStatusArray() {
       return [self::ASSIGNED, self::PLANNED];
   }

   public static function getDefaultValues($entity = 0) {
       $usersId = is_numeric(Session::getLoginUserID(false)) ? Session::getLoginUserID() : 0;
       $defaultUseNotif = Entity::getUsedConfig('is_notif_enable_default', $_SESSION['glpiactive_entity'] ?? 0, '', 1);

       return [
           '_users_id_requester' => $usersId,
           '_users_id_requester_notif' => ['use_notification' => $defaultUseNotif, 'alternative_email' => ''],
           '_groups_id_requester' => 0,
           '_users_id_assign' => 0,
           '_users_id_assign_notif' => ['use_notification' => $defaultUseNotif, 'alternative_email' => ''],
           '_groups_id_assign' => 0,
           '_users_id_observer' => 0,
           '_users_id_observer_notif' => ['use_notification' => $defaultUseNotif, 'alternative_email' => ''],
           '_groups_id_observer' => 0,
           '_suppliers_id_assign' => 0,
           '_suppliers_id_assign_notif' => ['use_notification' => $defaultUseNotif, 'alternative_email' => ''],
           'priority' => 3,
           'urgency' => 3,
           'impact' => 3,
           'content' => '',
           'entities_id' => $_SESSION['glpiactive_entity'] ?? 0,
           'name' => '',
           'itilcategories_id' => 0,
           'actiontime' => 0,
           'date' => 'NULL',
           '_add_validation' => 0,
           '_validation_targets' => [],
           '_tasktemplates_id' => [],
           'impact_content' => '',
           'control_list_content' => '',
           'rollback_plan_content' => '',
           'items_id' => 0,
           '_actors' => [],
           'status' => self::INCOMING,
           'time_to_resolve' => 'NULL',
           'itemtype' => '',
           'locations_id' => 0,
       ];
   }

   public static function getItemLinkClass(): string {
       return PluginSecurityincidentsSecurityIncident_Item::class;
   }

    /**
     * Required, not optional — same class of surprise as `getRuleCollectionClassInstance()` (see
     * `RulePluginSecurityincidentsSecurityIncidentCollection`'s own docblock): unlike most
     * `CommonITILObject` extension points, this one is a hardcoded `switch` in core listing only
     * `Ticket`/`Change`/`Problem`, with no generic fallback and no way to register a plugin's own
     * itemtype from outside — confirmed live, opening any of the Template's own field-configuration
     * tabs (Mandatory/Hidden/Readonly/Predefined) fataled with "Unknown ITIL type
     * PluginSecurityincidentsSecurityIncident" (`ITILTemplate::getAllowedFields()` calls
     * `static::getItemsTable()` to know which assets can be referenced in a predefined/hidden
     * field). Not abstract, so overriding it here is enough — no core patch needed.
     */
   public static function getItemsTable() {
       return PluginSecurityincidentsSecurityIncident_Item::getTable();
   }

   public static function getContentTemplatesParametersClassInstance(): CommonITILObjectParameters {
       return new SecurityIncidentParameters();
   }

   public function getRights($interface = 'central') {
       $values = parent::getRights();
       unset($values[READ]);

       $values[self::READALL] = __('See all');
       $values[self::READMY] = __('See (author)');

       return $values;
   }

   public static function canView(): bool {
       return Session::haveRightsOr(self::$rightname, [self::READALL, self::READMY]);
   }

   public function canViewItem(): bool {
      if (!$this->checkEntity(true)) {
          return false;
      }

       return Session::haveRight(self::$rightname, self::READALL)
           || (Session::haveRight(self::$rightname, self::READMY)
               && ($this->isUser(CommonITILActor::REQUESTER, Session::getLoginUserID())
                   || $this->isUser(CommonITILActor::OBSERVER, Session::getLoginUserID())
                   || (isset($_SESSION['glpigroups'])
                       && ($this->haveAGroup(CommonITILActor::REQUESTER, $_SESSION['glpigroups'])
                           || $this->haveAGroup(CommonITILActor::OBSERVER, $_SESSION['glpigroups'])))
                   || $this->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                   || (isset($_SESSION['glpigroups'])
                       && $this->haveAGroup(CommonITILActor::ASSIGN, $_SESSION['glpigroups']))));
   }

   public function canCreateItem(): bool {
      if (!Session::haveAccessToEntity($this->getEntityID())) {
          return false;
      }

       return Session::haveRight(self::$rightname, CREATE);
   }

   public function canSolve() {
       return self::isAllowedStatus($this->fields['status'], self::SOLVED)
           && !in_array($this->fields['status'], static::getClosedStatusArray(), true)
           && (Session::haveRight(self::$rightname, UPDATE)
               || (Session::haveRight(self::$rightname, self::READMY)
                   && ($this->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                       || (isset($_SESSION['glpigroups'])
                           && $this->haveAGroup(CommonITILActor::ASSIGN, $_SESSION['glpigroups'])))));
   }

    /**
     * No separate "Analysis" tab (there used to be one, `impact_content`/`control_list_content`/
     * `rollback_plan_content` — removed once `Hooks::POST_ITIL_INFO_SECTION` turned out to let
     * these same fields render directly in the main field panel instead, exactly where `Change`'s
     * own native "Analysis" accordion lives — see `plugin_securityincidents_post_itil_info_section()`
     * in `hook.php`. Keeping both would have shown the same three fields editable in two different
     * places.
     */
   public function defineTabs($options = []) {
       $tabs = [];
       $this->addDefaultFormTab($tabs);
       $this->addStandardTab(PluginSecurityincidentsSecurityIncidentCve::class, $tabs, $options);
       $this->addStandardTab(PluginSecurityincidentsSecurityIncident_Item::class, $tabs, $options);
       $this->addStandardTab(PluginSecurityincidentsSecurityIncidentCost::class, $tabs, $options);
       $this->addStandardTab(KnowbaseItem_Item::class, $tabs, $options);
       $this->addStandardTab(Notepad::class, $tabs, $options);
       $this->addStandardTab(Log::class, $tabs, $options);

       return $tabs;
   }

   public function cleanDBonPurge() {
       $task = new PluginSecurityincidentsSecurityIncidentTask();
       $task->deleteByCriteria(['plugin_securityincidents_securityincidents_id' => $this->fields['id']]);

       $this->deleteChildrenAndRelationsFromDb([
           PluginSecurityincidentsSecurityIncident_Item::class,
           PluginSecurityincidentsSecurityIncidentCve::class,
           PluginSecurityincidentsSecurityIncidentCost::class,
       ]);

       parent::cleanDBonPurge();
   }

   public function post_addItem() {
       parent::post_addItem();

       $this->handleNewItemNotifications();
   }

   public function post_updateItem($history = true) {
       global $CFG_GLPI;

       parent::post_updateItem($history);

       $doNotif = count($this->updates) > 0;
      if (isset($this->input['_disablenotif'])) {
          $doNotif = false;
      }

      if ($doNotif && $CFG_GLPI['use_notifications']) {
          $mailType = 'update';
         if (isset($this->input['status']) && in_array('status', $this->updates, true)) {
            if (in_array($this->input['status'], static::getSolvedStatusArray(), true)) {
               $mailType = 'solved';
            } else if (in_array($this->input['status'], static::getClosedStatusArray(), true)) {
                $mailType = 'closed';
            }
         }

          $this->getFromDB($this->fields['id']);
          NotificationEvent::raiseEvent($mailType, $this);
      }
   }

   public function rawSearchOptions() {
       return $this->getSearchOptionsMain();
   }
}
