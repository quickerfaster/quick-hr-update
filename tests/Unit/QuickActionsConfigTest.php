<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the quick-actions.php config structure.
 *
 * This test does NOT boot Laravel — it loads the config file directly.
 */
class QuickActionsConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/app/Modules/Hr/Config/quick-actions.php';
        $this->config = require $path;
    }

    // -----------------------------------------------------------------
    // Top-Level Structure
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_quick_actions_key()
    {
        $this->assertArrayHasKey('quick_actions', $this->config);
        $this->assertIsArray($this->config['quick_actions']);
    }

    // -----------------------------------------------------------------
    // Report Absence Quick Action
    // -----------------------------------------------------------------

    #[Test]
    public function report_absence_quick_action_exists()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertNotNull($action, 'Report Absence quick action not found');
    }

    #[Test]
    public function report_absence_has_correct_label()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('Report Absence', $action['label']);
    }

    #[Test]
    public function report_absence_has_correct_description()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals(
            'Notify your manager of an unplanned absence',
            $action['description']
        );
    }

    #[Test]
    public function report_absence_has_phone_icon()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('fas fa-phone-alt', $action['icon']);
    }

    #[Test]
    public function report_absence_is_navigate_action()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('navigate', $action['action']);
    }

    #[Test]
    public function report_absence_is_in_hr_module()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('hr', $action['module']);
    }

    #[Test]
    public function report_absence_is_in_self_service_category()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('Self Service', $action['category']);
    }

    #[Test]
    public function report_absence_url_points_to_report_absence_tab()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertEquals('/hr/leave-hub?tab=report-absence', $action['url']);
    }

    #[Test]
    public function report_absence_is_available_to_employee_and_manager()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertContains('employee', $action['roles']);
        $this->assertContains('manager', $action['roles']);
    }

    #[Test]
    public function report_absence_has_relevant_keywords()
    {
        $action = $this->findActionById('ess.report_absence');

        $this->assertContains('sick', $action['keywords']);
        $this->assertContains('absence', $action['keywords']);
        $this->assertContains('report', $action['keywords']);
        $this->assertContains('emergency', $action['keywords']);
        $this->assertContains('unplanned', $action['keywords']);
    }

    // -----------------------------------------------------------------
    // Self Service Category — All Actions
    // -----------------------------------------------------------------

    #[Test]
    public function self_service_category_has_multiple_actions()
    {
        $selfServiceActions = array_filter($this->config['quick_actions'], function ($a) {
            return ($a['category'] ?? '') === 'Self Service';
        });

        $this->assertGreaterThan(5, count($selfServiceActions));
    }

    #[Test]
    public function all_self_service_actions_have_employee_role()
    {
        $selfServiceActions = array_filter($this->config['quick_actions'], function ($a) {
            return ($a['category'] ?? '') === 'Self Service';
        });

        foreach ($selfServiceActions as $action) {
            $this->assertContains(
                'employee',
                $action['roles'] ?? [],
                "Action '{$action['id']}' should include 'employee' role"
            );
        }
    }

    // -----------------------------------------------------------------
    // Leave-Related Quick Actions
    // -----------------------------------------------------------------

    #[Test]
    public function request_leave_quick_action_exists()
    {
        $action = $this->findActionById('ess.request_leave');

        $this->assertNotNull($action);
        $this->assertEquals('Self Service', $action['category']);
    }

    #[Test]
    public function leave_overview_quick_action_exists()
    {
        $action = $this->findActionById('ess.leave_overview');

        $this->assertNotNull($action);
        $this->assertEquals('Self Service', $action['category']);
    }

    #[Test]
    public function my_leaves_quick_action_exists()
    {
        $action = $this->findActionById('ess.my_leaves');

        $this->assertNotNull($action);
        $this->assertEquals('Self Service', $action['category']);
    }

    // -----------------------------------------------------------------
    // All Quick Actions Have Required Fields
    // -----------------------------------------------------------------

    #[Test]
    public function all_quick_actions_have_id()
    {
        foreach ($this->config['quick_actions'] as $action) {
            $this->assertArrayHasKey('id', $action, 'Every quick action must have an id');
            $this->assertNotEmpty($action['id']);
        }
    }

    #[Test]
    public function all_quick_actions_have_label()
    {
        foreach ($this->config['quick_actions'] as $action) {
            $this->assertArrayHasKey('label', $action, "Action '{$action['id']}' must have a label");
            $this->assertNotEmpty($action['label']);
        }
    }

    #[Test]
    public function all_quick_actions_have_category()
    {
        foreach ($this->config['quick_actions'] as $action) {
            $this->assertArrayHasKey('category', $action, "Action '{$action['id']}' must have a category");
            $this->assertNotEmpty($action['category']);
        }
    }

    #[Test]
    public function all_quick_actions_have_roles()
    {
        foreach ($this->config['quick_actions'] as $action) {
            $this->assertArrayHasKey('roles', $action, "Action '{$action['id']}' must have roles");
            $this->assertNotEmpty($action['roles']);
        }
    }

    // -----------------------------------------------------------------
    // No Duplicate IDs
    // -----------------------------------------------------------------

    #[Test]
    public function no_duplicate_quick_action_ids()
    {
        $ids = array_column($this->config['quick_actions'], 'id');
        $uniqueIds = array_unique($ids);

        $this->assertCount(
            count($ids),
            $uniqueIds,
            'Quick action IDs must be unique. Duplicates: ' .
            implode(', ', array_diff_assoc($ids, $uniqueIds))
        );
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function findActionById(string $id): ?array
    {
        foreach ($this->config['quick_actions'] as $action) {
            if (($action['id'] ?? '') === $id) {
                return $action;
            }
        }

        return null;
    }
}
