<?php

namespace GlpiPlugin\Securityincidents\Tests;

use GlpiPlugin\Securityincidents\Dashboard\CardProvider;
use Glpi\Dashboard\Grid;
use PluginSecurityincidentsSecurityIncident;

/**
 * Exercises the actual GLPI core dashboard machinery (`Glpi\Dashboard\Grid`), not just this
 * plugin's own hook function in isolation — `plugin_securityincidents_dashboard_cards()` returning
 * the right shape is necessary but not sufficient; `Plugin::doHookFunction(Hooks::DASHBOARD_CARDS)`
 * chains every registered plugin's callback as an accumulator (see hook.php's own docblock), so
 * this also guards against a future regression breaking that chain (e.g. reverting to
 * `array $cards = []` instead of `?array $cards = null`, which would silently drop every plugin
 * registered before this one whenever the hook order changes).
 */
final class DashboardCardsTest extends SecurityIncidentsTestCase
{
   public function testAllFourCardsAreRegisteredWithCoreDashboard(): void {
       $cards = (new Grid('central'))->getAllDasboardCards(true);

       foreach ([
           'securityincidents_bn_total',
           'securityincidents_bn_open',
           'securityincidents_by_entity',
           'securityincidents_by_category',
       ] as $cardId) {
           $this->assertArrayHasKey($cardId, $cards, "Card \"$cardId\" is missing from the dashboard's card registry.");
       }
   }

   public function testOpenIncidentsCardProviderReturnsARealCountAndUrl(): void {
       $entityId = $this->createTestEntity(0, 'PHPUnit Dashboard Entity');
       $incident = new PluginSecurityincidentsSecurityIncident();
       $incident->add(['name' => 'Test — dashboard open count', 'entities_id' => $entityId, 'content' => 'x']);

       $result = CardProvider::open();

       $this->assertGreaterThan(0, $result['number']);
       $this->assertStringContainsString('criteria[0][value]=notold', $result['url']);
   }
}
