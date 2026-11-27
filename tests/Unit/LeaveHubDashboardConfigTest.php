<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dashboard_leave_hub.php config structure.
 *
 * This test does NOT boot Laravel — it loads the config file directly.
 */
class LeaveHubDashboardConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/app/Modules/Hr/Data/dashboards/dashboard_leave_hub.php';
        $this->config = require $path;
    }

    // -----------------------------------------------------------------
    // Top-Level Structure
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_title()
    {
        $this->assertArrayHasKey('title', $this->config);
        $this->assertEquals('Leave Overview', $this->config['title']);
    }

    #[Test]
    public function config_has_description()
    {
        $this->assertArrayHasKey('description', $this->config);
        $this->assertNotEmpty($this->config['description']);
    }

    #[Test]
    public function config_has_widgets()
    {
        $this->assertArrayHasKey('widgets', $this->config);
        $this->assertIsArray($this->config['widgets']);
    }

    // -----------------------------------------------------------------
    // Report Absence Action Card
    // -----------------------------------------------------------------

    #[Test]
    public function report_absence_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertNotNull($widget, 'Report Absence action card not found');
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
    public function report_absence_has_description()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals(
            'Notify your manager of an unplanned absence',
            $widget['description']
        );
    }

    #[Test]
    public function report_absence_has_width_six()
    {
        $widget = $this->findWidgetByTitle('Report Absence');

        $this->assertEquals(6, $widget['width']);
    }

    #[Test]
    public function report_absence_action_opens_drawer_with_wizard()
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
    // Apply for Leave Action Card
    // -----------------------------------------------------------------

    #[Test]
    public function apply_for_leave_action_card_exists()
    {
        $widget = $this->findWidgetByTitle('Apply for Leave');

        $this->assertNotNull($widget, 'Apply for Leave action card not found');
    }

    #[Test]
    public function apply_for_leave_is_action_card_type()
    {
        $widget = $this->findWidgetByTitle('Apply for Leave');

        $this->assertEquals('action_card', $widget['type']);
    }

    #[Test]
    public function apply_for_leave_has_width_six()
    {
        $widget = $this->findWidgetByTitle('Apply for Leave');

        $this->assertEquals(6, $widget['width']);
    }

    #[Test]
    public function apply_for_leave_has_primary_color()
    {
        $widget = $this->findWidgetByTitle('Apply for Leave');

        $this->assertEquals('primary', $widget['color']);
    }

    // -----------------------------------------------------------------
    // Row Layout — Both Action Cards Side by Side (w6 + w6 = 12)
    // -----------------------------------------------------------------

    #[Test]
    public function report_absence_and_apply_for_leave_are_side_by_side()
    {
        $reportWidget = $this->findWidgetByTitle('Report Absence');
        $applyWidget = $this->findWidgetByTitle('Apply for Leave');

        $this->assertEquals(6, $reportWidget['width']);
        $this->assertEquals(6, $applyWidget['width']);
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
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function pending_approvals_stat_card_exists()
    {
        $widget = $this->findWidgetByTitle('Pending Approvals');

        $this->assertNotNull($widget);
        $this->assertEquals('stat', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function approved_this_year_stat_card_exists()
    {
        $widget = $this->findWidgetByTitle('Approved This Year');

        $this->assertNotNull($widget);
        $this->assertEquals('stat', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    #[Test]
    public function total_requests_stat_card_exists()
    {
        $widget = $this->findWidgetByTitle('Total Requests');

        $this->assertNotNull($widget);
        $this->assertEquals('stat', $widget['type']);
        $this->assertEquals(3, $widget['width']);
    }

    // -----------------------------------------------------------------
    // Row 1: Four Stat Cards Fill 12 Columns
    // -----------------------------------------------------------------

    #[Test]
    public function row_one_stat_cards_fill_twelve_columns()
    {
        $statWidgets = array_filter($this->config['widgets'], function ($w) {
            return ($w['type'] ?? '') === 'stat';
        });

        $totalWidth = array_sum(array_column($statWidgets, 'width'));

        $this->assertEquals(12, $totalWidth);
    }

    // -----------------------------------------------------------------
    // List Widgets
    // -----------------------------------------------------------------

    #[Test]
    public function upcoming_time_off_list_exists()
    {
        $widget = $this->findWidgetByTitle('Upcoming Time Off');

        $this->assertNotNull($widget);
        $this->assertEquals('list', $widget['type']);
        $this->assertEquals(6, $widget['width']);
    }

    #[Test]
    public function recent_leave_requests_list_exists()
    {
        $widget = $this->findWidgetByTitle('Recent Leave Requests');

        $this->assertNotNull($widget);
        $this->assertEquals('list', $widget['type']);
        $this->assertEquals(6, $widget['width']);
    }

    // -----------------------------------------------------------------
    // All Models Reference Correct Modules
    // -----------------------------------------------------------------

    #[Test]
    public function all_models_are_in_leave_or_hr_module()
    {
        foreach ($this->config['widgets'] as $widget) {
            if (isset($widget['model'])) {
                $this->assertTrue(
                    str_starts_with($widget['model'], 'App\\Modules\\Leave\\') ||
                    str_starts_with($widget['model'], 'App\\Modules\\Hr\\'),
                    "Model {$widget['model']} should be in Leave or Hr module"
                );
            }
        }
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
