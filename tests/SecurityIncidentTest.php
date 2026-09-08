<?php

namespace GlpiPlugin\Securityincidents\Tests;

use PluginSecurityincidentsSecurityIncident;
use PluginSecurityincidentsSecurityIncident_Item;
use PluginSecurityincidentsSecurityIncidentCve;

final class SecurityIncidentTest extends SecurityIncidentsTestCase
{
    public function testCanBeAddedAndReadBackFromItsOwnRealTable(): void
    {
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

    public function testGetSectorizedDetailsPlacesItInTheHelpdeskSector(): void
    {
        $this->assertSame(
            ['helpdesk', PluginSecurityincidentsSecurityIncident::class],
            PluginSecurityincidentsSecurityIncident::getSectorizedDetails()
        );
    }

    public function testGetTypeNameUsesTheRealTranslationDomainForSingularAndPlural(): void
    {
        $this->assertSame(_n('Security incident', 'Security incidents', 1, 'securityincidents'), PluginSecurityincidentsSecurityIncident::getTypeName(1));
        $this->assertSame(_n('Security incident', 'Security incidents', 2, 'securityincidents'), PluginSecurityincidentsSecurityIncident::getTypeName(2));
        $this->assertNotSame(PluginSecurityincidentsSecurityIncident::getTypeName(1), PluginSecurityincidentsSecurityIncident::getTypeName(2));
    }

    public function testGetItemLinkClassReturnsSecurityIncidentItem(): void
    {
        $this->assertSame(PluginSecurityincidentsSecurityIncident_Item::class, PluginSecurityincidentsSecurityIncident::getItemLinkClass());
    }

    public function testAnalysisFieldsCanBeUpdated(): void
    {
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

    public function testCveReferenceCanBeAddedAndIsNormalizedToUppercase(): void
    {
        $entityId = $this->createTestEntity(0, 'PHPUnit CVE Entity');
        $incident = new PluginSecurityincidentsSecurityIncident();
        $id = $incident->add(['name' => 'Test — CVE', 'entities_id' => $entityId, 'content' => 'x']);

        $cve = new PluginSecurityincidentsSecurityIncidentCve();
        $cveId = $cve->add(['securityincidents_id' => $id, 'cve_id' => 'cve-2026-12345']);

        $this->assertGreaterThan(0, $cveId);
        $reloaded = new PluginSecurityincidentsSecurityIncidentCve();
        $reloaded->getFromDB($cveId);
        $this->assertSame('CVE-2026-12345', $reloaded->fields['cve_id']);
    }

    public function testCveReferenceRejectsAMalformedIdentifier(): void
    {
        $entityId = $this->createTestEntity(0, 'PHPUnit CVE Invalid Entity');
        $incident = new PluginSecurityincidentsSecurityIncident();
        $id = $incident->add(['name' => 'Test — bad CVE', 'entities_id' => $entityId, 'content' => 'x']);

        $cve = new PluginSecurityincidentsSecurityIncidentCve();
        $cveId = $cve->add(['securityincidents_id' => $id, 'cve_id' => 'not-a-cve']);

        $this->assertFalse($cveId);
    }
}
