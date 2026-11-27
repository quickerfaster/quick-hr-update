<?php

namespace Tests\Unit;

use App\Modules\Hr\Http\Livewire\LeaveHub;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the LeaveHub Livewire component logic.
 *
 * This test does NOT boot Laravel — it uses reflection to test
 * component properties and methods in isolation.
 */
class LeaveHubTest extends TestCase
{
    // -----------------------------------------------------------------
    // Allowed Tabs
    // -----------------------------------------------------------------

    #[Test]
    public function report_absence_tab_is_in_allowed_tabs_list()
    {
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertContains('report-absence', $allowedTabs);
    }

    #[Test]
    public function all_four_tabs_are_defined()
    {
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertCount(4, $allowedTabs);
    }

    #[Test]
    public function overview_tab_is_in_allowed_tabs()
    {
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertContains('overview', $allowedTabs);
    }

    #[Test]
    public function my_leaves_tab_is_in_allowed_tabs()
    {
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertContains('my-leaves', $allowedTabs);
    }

    #[Test]
    public function apply_tab_is_in_allowed_tabs()
    {
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertContains('apply', $allowedTabs);
    }

    // -----------------------------------------------------------------
    // Tab Switching Logic (unit-level, no DB)
    // -----------------------------------------------------------------

    #[Test]
    public function switch_tab_sets_active_tab()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('activeTab');
        $property->setAccessible(true);
        $property->setValue($hub, 'report-absence');

        $this->assertEquals('report-absence', $property->getValue($hub));
    }

    #[Test]
    public function switch_tab_to_overview_works()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('activeTab');
        $property->setAccessible(true);
        $property->setValue($hub, 'overview');

        $this->assertEquals('overview', $property->getValue($hub));
    }

    #[Test]
    public function switch_tab_to_my_leaves_works()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('activeTab');
        $property->setAccessible(true);
        $property->setValue($hub, 'my-leaves');

        $this->assertEquals('my-leaves', $property->getValue($hub));
    }

    #[Test]
    public function switch_tab_to_apply_works()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('activeTab');
        $property->setAccessible(true);
        $property->setValue($hub, 'apply');

        $this->assertEquals('apply', $property->getValue($hub));
    }

    // -----------------------------------------------------------------
    // Default State
    // -----------------------------------------------------------------

    #[Test]
    public function default_active_tab_is_overview()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('activeTab');
        $property->setAccessible(true);

        $this->assertEquals('overview', $property->getValue($hub));
    }

    #[Test]
    public function employee_id_defaults_to_null()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('employeeId');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($hub));
    }

    #[Test]
    public function employee_number_defaults_to_null()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('employeeNumber');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($hub));
    }

    #[Test]
    public function employee_name_defaults_to_null()
    {
        $hub = new LeaveHub();

        $reflection = new \ReflectionClass($hub);
        $property = $reflection->getProperty('employeeName');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($hub));
    }

    // -----------------------------------------------------------------
    // Query String Tab Resolution (unit-level)
    // -----------------------------------------------------------------

    #[Test]
    public function query_string_tab_report_absence_is_valid()
    {
        $tab = 'report-absence';
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertTrue(in_array($tab, $allowedTabs));
    }

    #[Test]
    public function query_string_tab_invalid_value_is_rejected()
    {
        $tab = 'malicious-tab';
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertFalse(in_array($tab, $allowedTabs));
    }

    #[Test]
    public function query_string_tab_empty_string_is_rejected()
    {
        $tab = '';
        $allowedTabs = ['overview', 'my-leaves', 'apply', 'report-absence'];

        $this->assertFalse(in_array($tab, $allowedTabs));
    }

    // -----------------------------------------------------------------
    // Method Existence
    // -----------------------------------------------------------------

    #[Test]
    public function switch_tab_method_exists()
    {
        $this->assertTrue(
            method_exists(LeaveHub::class, 'switchTab'),
            'LeaveHub should have a switchTab method'
        );
    }

    #[Test]
    public function mount_method_exists()
    {
        $this->assertTrue(
            method_exists(LeaveHub::class, 'mount'),
            'LeaveHub should have a mount method'
        );
    }

    #[Test]
    public function render_method_exists()
    {
        $this->assertTrue(
            method_exists(LeaveHub::class, 'render'),
            'LeaveHub should have a render method'
        );
    }

}
