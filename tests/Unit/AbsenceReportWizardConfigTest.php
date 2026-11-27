<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the absence_report.php wizard config structure.
 *
 * This test does NOT boot Laravel — it loads the config file directly
 * to avoid memory exhaustion from cumulative framework boots.
 */
class AbsenceReportWizardConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/app/Modules/Attendance/Data/wizards/absence_report.php';
        $this->config = require $path;
    }

    // -----------------------------------------------------------------
    // Top-Level Structure
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_id()
    {
        $this->assertArrayHasKey('id', $this->config);
        $this->assertEquals('absence_report', $this->config['id']);
    }

    #[Test]
    public function config_has_title()
    {
        $this->assertArrayHasKey('title', $this->config);
        $this->assertEquals('Report Absence', $this->config['title']);
    }

    #[Test]
    public function config_has_description()
    {
        $this->assertArrayHasKey('description', $this->config);
        $this->assertNotEmpty($this->config['description']);
    }

    #[Test]
    public function config_has_return_path()
    {
        $this->assertArrayHasKey('returnPath', $this->config);
        $this->assertEquals('/hr/leave-hub?tab=overview', $this->config['returnPath']);
    }

    #[Test]
    public function config_has_steps()
    {
        $this->assertArrayHasKey('steps', $this->config);
        $this->assertIsArray($this->config['steps']);
    }

    #[Test]
    public function config_has_completion()
    {
        $this->assertArrayHasKey('completion', $this->config);
        $this->assertIsArray($this->config['completion']);
    }

    #[Test]
    public function config_has_roles()
    {
        $this->assertArrayHasKey('roles', $this->config);
        $this->assertContains('employee', $this->config['roles']);
        $this->assertContains('manager', $this->config['roles']);
    }

    #[Test]
    public function config_has_models()
    {
        $this->assertArrayHasKey('models', $this->config);
        $this->assertArrayHasKey('primary', $this->config['models']);
        $this->assertEquals(
            'App\\Modules\\Attendance\\Models\\Attendance',
            $this->config['models']['primary']
        );
    }

    // -----------------------------------------------------------------
    // Step 0 — Custom Component
    // -----------------------------------------------------------------

    #[Test]
    public function step_zero_has_custom_component()
    {
        $this->assertArrayHasKey(0, $this->config['steps']);
        $step = $this->config['steps'][0];

        $this->assertArrayHasKey('customComponent', $step);
        $this->assertEquals('attendance.absence-report-form', $step['customComponent']);
    }

    #[Test]
    public function step_zero_has_no_model_key()
    {
        $step = $this->config['steps'][0];

        // Pattern C: fully decoupled — no model or groups on the step
        $this->assertArrayNotHasKey('model', $step);
    }

    #[Test]
    public function step_zero_has_no_groups_key()
    {
        $step = $this->config['steps'][0];

        // Pattern C: fully decoupled — no groups
        $this->assertArrayNotHasKey('groups', $step);
    }

    #[Test]
    public function step_zero_has_title()
    {
        $step = $this->config['steps'][0];

        $this->assertArrayHasKey('title', $step);
        $this->assertEquals('Reason & Duration', $step['title']);
    }

    // -----------------------------------------------------------------
    // Step 0 — Fields
    // -----------------------------------------------------------------

    #[Test]
    public function step_zero_has_fields()
    {
        $step = $this->config['steps'][0];

        $this->assertArrayHasKey('fields', $step);
        $this->assertIsArray($step['fields']);
    }

    #[Test]
    public function step_zero_has_absence_type_field()
    {
        $fields = $this->config['steps'][0]['fields'];

        $this->assertArrayHasKey(0, $fields);
        $this->assertArrayHasKey('absence_type', $fields[0]);

        $field = $fields[0]['absence_type'];
        $this->assertEquals('select', $field['type']);
        $this->assertEquals('Absence Type', $field['label']);
        $this->assertEquals('sick_leave', $field['default']);
        $this->assertContains('required', $field['validation']);
    }

    #[Test]
    public function absence_type_has_all_five_options()
    {
        $options = $this->config['steps'][0]['fields'][0]['absence_type']['options'];

        $this->assertArrayHasKey('sick_leave', $options);
        $this->assertArrayHasKey('emergency', $options);
        $this->assertArrayHasKey('personal', $options);
        $this->assertArrayHasKey('official_assignment', $options);
        $this->assertArrayHasKey('other', $options);

        $this->assertCount(5, $options);
    }

    #[Test]
    public function absence_type_includes_official_assignment()
    {
        $options = $this->config['steps'][0]['fields'][0]['absence_type']['options'];

        $this->assertEquals(
            'Official Assignment / Company Duty',
            $options['official_assignment']
        );
    }

    #[Test]
    public function step_zero_has_start_date_field()
    {
        $fields = $this->config['steps'][0]['fields'];

        $this->assertArrayHasKey(1, $fields);
        $this->assertArrayHasKey('start_date', $fields[1]);

        $field = $fields[1]['start_date'];
        $this->assertEquals('datepicker', $field['type']);
        $this->assertEquals('Start Date', $field['label']);
        $this->assertEquals('today', $field['default']);
        $this->assertContains('required', $field['validation']);
        $this->assertContains('date', $field['validation']);
        $this->assertContains('before_or_equal:today', $field['validation']);
    }

    #[Test]
    public function step_zero_has_expected_duration_field()
    {
        $fields = $this->config['steps'][0]['fields'];

        $this->assertArrayHasKey(2, $fields);
        $this->assertArrayHasKey('expected_duration', $fields[2]);

        $field = $fields[2]['expected_duration'];
        $this->assertEquals('select', $field['type']);
        $this->assertEquals('Expected Duration', $field['label']);
        $this->assertEquals(1, $field['default']);
    }

    #[Test]
    public function expected_duration_has_seven_options()
    {
        $options = $this->config['steps'][0]['fields'][2]['expected_duration']['options'];

        $this->assertCount(7, $options);
    }

    #[Test]
    public function expected_duration_values_are_numeric()
    {
        $options = $this->config['steps'][0]['fields'][2]['expected_duration']['options'];

        foreach (array_keys($options) as $key) {
            $this->assertIsNumeric($key, "Duration key '{$key}' should be numeric");
        }
    }

    #[Test]
    public function expected_duration_includes_uncertain_as_zero()
    {
        $options = $this->config['steps'][0]['fields'][2]['expected_duration']['options'];

        $this->assertArrayHasKey(0, $options);
        $this->assertEquals('Uncertain', $options[0]);
    }

    #[Test]
    public function step_zero_has_absence_reason_field()
    {
        $fields = $this->config['steps'][0]['fields'];

        $this->assertArrayHasKey(3, $fields);
        $this->assertArrayHasKey('absence_reason', $fields[3]);

        $field = $fields[3]['absence_reason'];
        $this->assertEquals('textarea', $field['type']);
        $this->assertEquals('Reason', $field['label']);
        $this->assertContains('required', $field['validation']);
        $this->assertContains('string', $field['validation']);
        $this->assertContains('min:10', $field['validation']);
        $this->assertContains('max:500', $field['validation']);
    }

    // -----------------------------------------------------------------
    // Step 0 — Exactly 4 Fields
    // -----------------------------------------------------------------

    #[Test]
    public function step_zero_has_exactly_four_fields()
    {
        $fields = $this->config['steps'][0]['fields'];

        $this->assertCount(4, $fields);
    }

    // -----------------------------------------------------------------
    // Exactly 1 Step
    // -----------------------------------------------------------------

    #[Test]
    public function wizard_has_exactly_one_step()
    {
        $this->assertCount(1, $this->config['steps']);
    }

    // -----------------------------------------------------------------
    // Completion
    // -----------------------------------------------------------------

    #[Test]
    public function completion_has_title()
    {
        $this->assertArrayHasKey('title', $this->config['completion']);
        $this->assertEquals('Absence Reported Successfully', $this->config['completion']['title']);
    }

    #[Test]
    public function completion_has_message()
    {
        $this->assertArrayHasKey('message', $this->config['completion']);
        $this->assertNotEmpty($this->config['completion']['message']);
    }

    #[Test]
    public function completion_has_three_actions()
    {
        $this->assertArrayHasKey('actions', $this->config['completion']);
        $this->assertCount(3, $this->config['completion']['actions']);
    }

    #[Test]
    public function completion_first_action_is_primary()
    {
        $action = $this->config['completion']['actions'][0];

        $this->assertEquals('View My Attendance', $action['label']);
        $this->assertEquals('/hr/my-profile?tab=attendance', $action['url']);
        $this->assertTrue($action['primary']);
    }

    #[Test]
    public function completion_second_action_is_complete_leave_request()
    {
        $action = $this->config['completion']['actions'][1];

        $this->assertEquals('Complete Leave Request', $action['label']);
        $this->assertEquals('/hr/leave-hub?tab=my-leaves', $action['url']);
    }

    #[Test]
    public function completion_third_action_is_back_to_leave_hub()
    {
        $action = $this->config['completion']['actions'][2];

        $this->assertEquals('Back to Leave Hub', $action['label']);
        $this->assertEquals('/hr/leave-hub?tab=overview', $action['url']);
    }

    // -----------------------------------------------------------------
    // Link Fields
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_link_fields()
    {
        $this->assertArrayHasKey('linkFields', $this->config);
        $this->assertArrayHasKey('userField', $this->config['linkFields']);
        $this->assertArrayHasKey('databaseField', $this->config['linkFields']);
        $this->assertEquals('employee_number', $this->config['linkFields']['userField']);
        $this->assertEquals('employee_id', $this->config['linkFields']['databaseField']);
    }

    // -----------------------------------------------------------------
    // No Cross-Module References
    // -----------------------------------------------------------------

    #[Test]
    public function primary_model_is_in_attendance_module()
    {
        $model = $this->config['models']['primary'];

        $this->assertStringStartsWith('App\\Modules\\Attendance\\', $model);
    }

    #[Test]
    public function related_models_is_empty_array()
    {
        $this->assertIsArray($this->config['models']['related']);
        $this->assertEmpty($this->config['models']['related']);
    }
}
