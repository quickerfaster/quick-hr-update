<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dashboard_my_portal.php config structure.
 *
 * This test does NOT boot Laravel — it loads the config file directly.
 */
class MyPortalDashboardConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/app/Modules/Hr/Data/dashboards/dashboard_my_portal.php';
        $this->config = require $path;
    }

    // -----------------------------------------------------------------
    // Top-Level Structure
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_title()
    {
        $this->assertArrayHasKey('title', $this->config);
        $this->assertEquals('My Portal', $this->config['title']);
    }

    #[Test]
    public function config_has_roles_for_employee_and_manager()
    {
        $this->assertArrayHasKey('roles', $this->config);
        $this->assertContains('employee', $this->config['roles']);
        $this->assertContains('manager', $this->config['roles']);
    }

    // -----------------------------------------------------------------
    // Report Absence Action Card
    // -----------------------------------------------------------------

    #[Test]
    public function report_absence_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertNotNull($widget, 'Report Absence action card not found in My Portal');
    }

    #[Test]
    public function report_absence_is_action_card_type()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals('action_card', $widget['type']);
    }

    #[Test]
    public function report_absence_has_warning_color()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals('warning', $widget['color']);
    }

    #[Test]
    public function report_absence_has_phone_icon()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals('fas fa-phone-alt', $widget['icon']);
    }

    #[Test]
    public function report_absence_has_width_three()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function report_absence_opens_drawer_with_wizard()
    {
        $widget = $this->findWidgetByTitle('Report Absence');
        $action = $widget['actions'][0];

        $this->assertEquals('Report', $action['label']);
        $this->assertEquals('openDrawer', $action['event']);
        $this->assertEquals('qf.wizard', $action['params']['component']);
        $this->assertEquals(
            'attendance.wizards.absence_report',
            $action['params']['params']['configKey']
        );
        $this->assertEquals(
            '{{ employee_id }}',
            $action['params']['params']['presetData']['employee_id']
        );
        $this->assertEquals('Report Absence', $action['params']['title']);
        $this->assertEquals('warning', $action['style']);
    }

    // -----------------------------------------------------------------
    // Row 3: Four Action Cards Fill 12 Columns (4 × w3)
    // -----------------------------------------------------------------

    #[Test]
    public function row_three_has_four_action_cards()
    {
        $actionCards = array_filter($this->config['widgets'], function ($w) {
            return ($w['type'] ?? '') === 'action_card';
        });

        $this->assertCount(4, $actionCards);
    }

    #[Test]
    public function row_three_action_cards_fill_twelve_columns()
    {
        $actionCards = array_filter($this->config['widgets'], function ($w) {
            return ($w['type'] ?? '') === 'action_card';
        });

        $totalWidth = array_sum(array_column($actionCards, 'width'));

        $this->assertEquals(12, $totalWidth);
    }

    #[Test]
    public function all_action_cards_have_width_three()
    {
        $actionCards = array_filter($this->config['widgets'], function ($w) {
            return ($w['type'] ?? '') === 'action_card';
        });

        foreach ($actionCards as $card) {
            $this->assertEquals(3, $card['width'], "Card '{$card['title']}' should be w3");
        }
    }

    // -----------------------------------------------------------------
    // Existing Action Cards Still Present
    // -----------------------------------------------------------------

    #[Test]
    public function request_leave_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('Request Leave');

        $this->assertNotNull($widget);
        $this->assertEquals('action_card', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function view_payslip_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('View Payslip');

        $this->assertNotNull($widget);
        $this->assertEquals('action_card', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function update_my_info_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('Update My Info');

        $this->assertNotNull($widget);
        $this->assertEquals('action_card', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function update_my_info_opens_drawer_with_employee_form()
    {
        $widget = $this->findWidgetByTitle('Update My Info');
        $action = $widget['actions'][0];

        $this->assertEquals('Edit', $action['label']);
        $this->assertEquals('openDrawer', $action['event']);
        $this->assertEquals('qf.data-table-form', $action['params']['component']);
        $this->assertEquals(
            'hr.employee_self_service_edit',
            $action['params']['params']['configKey']
        );
        $this->assertEquals(
            '{{ employee_id }}',
            $action['params']['params']['recordId']
        );
        $this->assertEquals('Update My Info', $action['params']['title']);
        $this->assertEquals('warning', $action['style']);
    }

    // -----------------------------------------------------------------
    // Stat Cards
    // -----------------------------------------------------------------

    #[Test]
    public function leave_balance_stat_card_exists()
    {
        $widget = $this->findWidgetByTitle('Leave Balance');

        $this->assertNotNull($widget);
        $this->assertEquals('stat', $widget['type']);
    }

    #[Test]
    public function hours_this_week_stat_card_exists()
    {
        $widget = $this->findWidgetByTitle('Hours This Week');

        $this->assertNotNull($widget);
        $this->assertEquals('stat', $widget['type']);
    }

    // -----------------------------------------------------------------
    // Quick Actions Widget
    // -----------------------------------------------------------------

    #[Test]
    public function quick_actions_widget_exists()
    {
        $widget = $this->findWidgetByTitle('Quick Actions');

        $this->assertNotNull($widget);
        $this->assertEquals('quick_actions', $widget['type']);
        $this->assertEquals('Self Service', $widget['category']);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function findWidgetByTitle(string $title): ?array
    {
        foreach ($this->config['widgets'] as $widget) {
            if (($widget['title'] ?? '') === $title) {
                return $widget;
            }
        }

        return null;
    }
}
