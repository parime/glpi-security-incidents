<?php

namespace GlpiPlugin\Securityincidents\Tests;

use PluginSecurityincidentsSecurityIncident;
use PluginSecurityincidentsSecurityIncidentTemplate;
use PluginSecurityincidentsSecurityIncidentTemplateMandatoryField;
use PluginSecurityincidentsSecurityIncidentTemplatePredefinedField;

/**
 * Two more real, live-only bugs found by actually opening the Template's own field-configuration
 * tabs (Mandatory/Hidden/Readonly/Predefined) — not by re-reading code. Both are hardcoded
 * `switch (itil_class) { case Ticket::class... case Change::class... default: throw }` statements
 * in GLPI core with no generic fallback and no plugin-registration mechanism:
 *
 * 1. `CommonITILObject::getItemsTable()` — fixed by overriding it on
 *    `PluginSecurityincidentsSecurityIncident` itself (a normal, non-abstract static method, so a
 *    plugin subclass overriding it is enough).
 * 2. `ITILTemplatePredefinedField::getMultiplePredefinedValues()` — the switch lives directly
 *    inside this method rather than delegating to `$itiltype::getItemsTable()`, so overriding (1)
 *    alone does not fix this one; `PluginSecurityincidentsSecurityIncidentTemplatePredefinedField`
 *    overrides it too, using the same table (1) now supplies.
 */
final class SecurityIncidentTemplateTest extends SecurityIncidentsTestCase
{
   public function testGetItemsTableReturnsTheRealAssetLinkTable(): void {
       $this->assertSame(
           'glpi_plugin_securityincidents_securityincidents_items',
           PluginSecurityincidentsSecurityIncident::getItemsTable()
       );
   }

    /**
     * Regression guard for the "Unknown ITIL type PluginSecurityincidentsSecurityIncident" crash:
     * `getAllowedFields()` is what every one of the Template's own field-configuration tabs
     * (Mandatory/Hidden/Readonly) calls to build their own field picker, and it fatals outright
     * without `getItemsTable()`'s override.
     */
   public function testTemplateAllowedFieldsIncludesRealSearchOptions(): void {
       // `getAllowedFields()` maps searchOptionId => raw DB field name (e.g. "status", "content"),
       // not a translated label — asserting on the field name keeps this test locale-independent
       // (this plugin's own test suite runs in English in CI but French in the shared dev
       // instance).
       $fields = PluginSecurityincidentsSecurityIncidentTemplate::getAllowedFields();

       $this->assertNotEmpty($fields);
       $this->assertContains('status', $fields);
   }

    /**
     * Regression guard for the second, separate crash (same error message, different call site —
     * see this class's own docblock): the Predefined Fields tab has its own hardcoded switch that
     * overriding `getItemsTable()` alone does not fix.
     */
   public function testPredefinedFieldMultipleValuesDoesNotThrow(): void {
       $values = PluginSecurityincidentsSecurityIncidentTemplatePredefinedField::getMultiplePredefinedValues();

       $this->assertNotEmpty($values);
   }

   public function testAMandatoryFieldConfiguredOnATemplatePersists(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit Template Entity');
       $template = new PluginSecurityincidentsSecurityIncidentTemplate();
       $templateId = $template->add(['name' => 'Test — phishing template', 'entities_id' => $entityId]);
       $this->assertGreaterThan(0, $templateId);

       $mandatory = new PluginSecurityincidentsSecurityIncidentTemplateMandatoryField();
       $mandatoryId = $mandatory->add([
           'securityincidenttemplates_id' => $templateId,
           'num' => 21, // "Description" / content — same search option core uses for the same purpose.
       ]);

       $this->assertGreaterThan(0, $mandatoryId);
   }
}
