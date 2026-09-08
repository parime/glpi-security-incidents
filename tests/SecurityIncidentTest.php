<?php

namespace GlpiPlugin\Securityincidents\Tests;

use PluginSecurityincidentsSecurityIncident;
use PluginSecurityincidentsSecurityIncident_Item;
use PluginSecurityincidentsSecurityIncidentCve;

final class SecurityIncidentTest extends SecurityIncidentsTestCase
{
   public function testCanBeAddedAndReadBackFromItsOwnRealTable(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit SecurityIncident Entity');

       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add([
           'name' => 'Test — phishing campaign',
           'entities_id' => $entityId,
           'content' => 'A phishing email was reported by several users.',
       ]);

       $this->assertGreaterThan(0, $id);

       $reloaded = new PluginSecurityincidentsSecurityIncident();
       $this->assertTrue($reloaded->getFromDB($id));
       $this->assertSame('Test — phishing campaign', $reloaded->fields['name']);
       $this->assertSame($entityId, (int) $reloaded->fields['entities_id']);
       $this->assertSame(PluginSecurityincidentsSecurityIncident::INCOMING, (int) $reloaded->fields['status']);
   }

   public function testGetSectorizedDetailsPlacesItInTheHelpdeskSector(): void {
       $this->assertSame(
           ['helpdesk', PluginSecurityincidentsSecurityIncident::class],
           PluginSecurityincidentsSecurityIncident::getSectorizedDetails()
       );
   }

   public function testGetTypeNameUsesTheRealTranslationDomainForSingularAndPlural(): void {
       $this->assertSame(_n('Security incident', 'Security incidents', 1, 'securityincidents'), PluginSecurityincidentsSecurityIncident::getTypeName(1));
       $this->assertSame(_n('Security incident', 'Security incidents', 2, 'securityincidents'), PluginSecurityincidentsSecurityIncident::getTypeName(2));
       $this->assertNotSame(PluginSecurityincidentsSecurityIncident::getTypeName(1), PluginSecurityincidentsSecurityIncident::getTypeName(2));
   }

   public function testGetItemLinkClassReturnsSecurityIncidentItem(): void {
       $this->assertSame(PluginSecurityincidentsSecurityIncident_Item::class, PluginSecurityincidentsSecurityIncident::getItemLinkClass());
   }

   public function testAnalysisFieldsCanBeUpdated(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit Analysis Entity');
       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add([
           'name' => 'Test — analysis fields',
           'entities_id' => $entityId,
           'content' => 'Incident content.',
       ]);

       $incident->update([
           'id' => $id,
           'impact_content' => 'Two workstations compromised.',
           'control_list_content' => 'Reset credentials, isolate hosts.',
           'rollback_plan_content' => 'Restore from backup if needed.',
       ]);

       $reloaded = new PluginSecurityincidentsSecurityIncident();
       $reloaded->getFromDB($id);
       $this->assertSame('Two workstations compromised.', $reloaded->fields['impact_content']);
       $this->assertSame('Reset credentials, isolate hosts.', $reloaded->fields['control_list_content']);
       $this->assertSame('Restore from backup if needed.', $reloaded->fields['rollback_plan_content']);
   }

   public function testCveReferenceCanBeAddedAndIsNormalizedToUppercase(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit CVE Entity');
       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add(['name' => 'Test — CVE', 'entities_id' => $entityId, 'content' => 'x']);

       $cve = new PluginSecurityincidentsSecurityIncidentCve();
       $cveId = $cve->add(['plugin_securityincidents_securityincidents_id' => $id, 'cve_id' => 'cve-2026-12345']);

       $this->assertGreaterThan(0, $cveId);
       $reloaded = new PluginSecurityincidentsSecurityIncidentCve();
       $reloaded->getFromDB($cveId);
       $this->assertSame('CVE-2026-12345', $reloaded->fields['cve_id']);
   }

   public function testCveReferenceRejectsAMalformedIdentifier(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit CVE Invalid Entity');
       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add(['name' => 'Test — bad CVE', 'entities_id' => $entityId, 'content' => 'x']);

       $cve = new PluginSecurityincidentsSecurityIncidentCve();
       $cveId = $cve->add(['plugin_securityincidents_securityincidents_id' => $id, 'cve_id' => 'not-a-cve']);

       $this->assertFalse($cveId);
   }

    /**
     * Regression guard: `CommonITILObject::getSolvedStatusArray()`/`getClosedStatusArray()` both
     * default to an empty array ("to be overridden by class") — left unoverridden, any code path
     * that merges them into a SQL `NOT IN (...)` clause (e.g.
     * `NotificationTargetCommonITILObject::getDataForObject()`, exercised for real by
     * `testCreatingAnIncidentQueuesANewNotification()` below) fatals with "Empty IN are not
     * allowed". Asserted directly here too so a future regression is caught even if the
     * notification-specific test is ever skipped/mocked.
     */
   public function testStatusArraysAreNeverEmpty(): void {
       $this->assertNotEmpty(PluginSecurityincidentsSecurityIncident::getSolvedStatusArray());
       $this->assertNotEmpty(PluginSecurityincidentsSecurityIncident::getClosedStatusArray());
       $this->assertContains(PluginSecurityincidentsSecurityIncident::SOLVED, PluginSecurityincidentsSecurityIncident::getSolvedStatusArray());
       $this->assertContains(PluginSecurityincidentsSecurityIncident::CLOSED, PluginSecurityincidentsSecurityIncident::getClosedStatusArray());
   }

    /**
     * End-to-end regression guard for the notification-seeding gap fixed in
     * `Install\Installer::seedNotifications()`: without an active `Notification` row for the
     * itemtype/event pair, `NotificationEvent::raiseEvent('new', $this)` (called from
     * `post_addItem()`) silently does nothing. This plugin is installed and active on the real
     * instance this test suite runs against (see tests/bootstrap.php), so a real queued
     * notification here proves the whole chain — seeded `Notification`/`NotificationTemplate`,
     * the status-array fix, and the `PluginSecurityincidentsSecurityIncidentCost`/`Template`
     * classes `NotificationTargetCommonITILObject::getDataForObject()` unconditionally
     * instantiates by naming convention — all actually work together, not just in isolation.
     */
   public function testCreatingAnIncidentQueuesANewNotification(): void {
       global $DB;

       $entityId = $this->createTestEntity(0, 'PHPUnit Notification Entity');
       $countBefore = $DB->request(['FROM' => 'glpi_queuednotifications'])->count();

       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add(['name' => 'Test — notification', 'entities_id' => $entityId, 'content' => 'x']);
       $this->assertGreaterThan(0, $id);

       $countAfter = $DB->request(['FROM' => 'glpi_queuednotifications'])->count();
       $this->assertGreaterThan($countBefore, $countAfter, 'Creating an incident must queue at least one notification.');

       $latest = $DB->request(['FROM' => 'glpi_queuednotifications', 'ORDER' => 'id DESC', 'LIMIT' => 1])->current();
       $this->assertStringContainsString('New security incident', $latest['name']);
       $this->assertStringContainsString('Test — notification', $latest['name']);
   }

   public function testSolvingAnIncidentQueuesASolvedNotification(): void {
       global $DB;

       $entityId = $this->createTestEntity(0, 'PHPUnit Solved Notification Entity');
       $incident = new PluginSecurityincidentsSecurityIncident();
       $id = $incident->add(['name' => 'Test — solved notification', 'entities_id' => $entityId, 'content' => 'x']);

       $incident->update(['id' => $id, 'status' => PluginSecurityincidentsSecurityIncident::SOLVED]);

       $latest = $DB->request(['FROM' => 'glpi_queuednotifications', 'ORDER' => 'id DESC', 'LIMIT' => 1])->current();
       $this->assertStringContainsString('Security incident solved', $latest['name']);
   }

    /**
     * Regression guard: `ALLSTANDARDRIGHT` alone (READ/UPDATE/CREATE/DELETE/PURGE) does NOT
     * include `self::READALL` (a separate bit, 1024, added by this class's own `getRights()`
     * override) — confirmed the hard way, Super-Admin got a real 403 viewing an incident they
     * hadn't personally created after install granted only `ALLSTANDARDRIGHT`.
     */
   public function testSuperAdminProfileRightIncludesReadAll(): void {
       global $DB;

       $superAdmin = $DB->request(['FROM' => 'glpi_profiles', 'WHERE' => ['name' => 'Super-Admin']])->current();
       $right = $DB->request([
           'FROM' => 'glpi_profilerights',
           'WHERE' => ['name' => 'plugin_securityincidents_securityincident', 'profiles_id' => $superAdmin['id']],
       ])->current();

       $this->assertNotNull($right);
       $this->assertSame(
           PluginSecurityincidentsSecurityIncident::READALL,
           (int) $right['rights'] & PluginSecurityincidentsSecurityIncident::READALL,
           'Super-Admin must hold READALL, not just the standard CRUD bits.'
       );
   }
}
