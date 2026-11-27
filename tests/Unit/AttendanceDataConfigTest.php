<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the attendance.php data config structure.
 *
 * This test does NOT boot Laravel — it loads the config file directly
 * to avoid memory exhaustion from cumulative framework boots.
 */
class AttendanceDataConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/app/Modules/Attendance/Data/attendance.php';
        $this->config = require $path;
    }

    // -----------------------------------------------------------------
    // Top-Level Structure
    // -----------------------------------------------------------------

    #[Test]
    public function config_has_model()
    {
        $this->assertArrayHasKey('model', $this->config);
        $this->assertEquals(
            'App\\Modules\\Attendance\\Models\\Attendance',
            $this->config['model']
        );
    }

    #[Test]
    public function config_has_field_definitions()
    {
        $this->assertArrayHasKey('fieldDefinitions', $this->config);
        $this->assertIsArray($this->config['fieldDefinitions']);
    }

    // -----------------------------------------------------------------
    // is_paid_absence — Not Employee-Editable
    // -----------------------------------------------------------------

    #[Test]
    public function is_paid_absence_field_exists()
    {
        $this->assertArrayHasKey('is_paid_absence', $this->config['fieldDefinitions']);
    }

    #[Test]
    public function is_paid_absence_is_not_editable()
    {
        $field = $this->config['fieldDefinitions']['is_paid_absence'];

        $this->assertArrayHasKey('editable', $field);
        $this->assertFalse($field['editable']);
    }

    #[Test]
    public function is_paid_absence_has_help_text_about_auto_set()
    {
        $field = $this->config['fieldDefinitions']['is_paid_absence'];

        $this->assertArrayHasKey('help_text', $field);
        $this->assertStringContainsString('not employee-editable', $field['help_text']);
    }

    #[Test]
    public function is_paid_absence_is_checkbox_type()
    {
        $field = $this->config['fieldDefinitions']['is_paid_absence'];

        $this->assertEquals('checkbox', $field['field_type']);
    }

    #[Test]
    public function is_paid_absence_is_boolean_validation()
    {
        $field = $this->config['fieldDefinitions']['is_paid_absence'];

        $this->assertStringContainsString('boolean', $field['validation']);
    }

    // -----------------------------------------------------------------
    // hours_deducted — Not Employee-Editable
    // -----------------------------------------------------------------

    #[Test]
    public function hours_deducted_field_exists()
    {
        $this->assertArrayHasKey('hours_deducted', $this->config['fieldDefinitions']);
    }

    #[Test]
    public function hours_deducted_is_not_editable()
    {
        $field = $this->config['fieldDefinitions']['hours_deducted'];

        $this->assertArrayHasKey('editable', $field);
        $this->assertFalse($field['editable']);
    }

    #[Test]
    public function hours_deducted_has_help_text_about_auto_calculation()
    {
        $field = $this->config['fieldDefinitions']['hours_deducted'];

        $this->assertArrayHasKey('help_text', $field);
        $this->assertStringContainsString('not employee-editable', $field['help_text']);
    }

    #[Test]
    public function hours_deducted_is_number_type()
    {
        $field = $this->config['fieldDefinitions']['hours_deducted'];

        $this->assertEquals('number', $field['field_type']);
    }

    #[Test]
    public function hours_deducted_has_min_zero_max_twenty_four()
    {
        $field = $this->config['fieldDefinitions']['hours_deducted'];

        $this->assertStringContainsString('min:0', $field['validation']);
        $this->assertStringContainsString('max:24', $field['validation']);
    }

    // -----------------------------------------------------------------
    // Absence FieldGroup — Contains Both Fields
    // -----------------------------------------------------------------

    #[Test]
    public function absence_field_group_exists()
    {
        $this->assertArrayHasKey('fieldGroups', $this->config);
        $this->assertArrayHasKey('absence_details', $this->config['fieldGroups']);
    }

    #[Test]
    public function absence_field_group_includes_is_paid_absence()
    {
        $groupFields = $this->config['fieldGroups']['absence_details']['fields'];

        $this->assertContains('is_paid_absence', $groupFields);
    }

    #[Test]
    public function absence_field_group_includes_hours_deducted()
    {
        $groupFields = $this->config['fieldGroups']['absence_details']['fields'];

        $this->assertContains('hours_deducted', $groupFields);
    }

    // -----------------------------------------------------------------
    // Model Reference — Attendance Module Only
    // -----------------------------------------------------------------

    #[Test]
    public function model_is_in_attendance_module()
    {
        $this->assertStringStartsWith(
            'App\\Modules\\Attendance\\',
            $this->config['model']
        );
    }

    // -----------------------------------------------------------------
    // Absence Type Field
    // -----------------------------------------------------------------

    #[Test]
    public function absence_type_field_exists()
    {
        $this->assertArrayHasKey('absence_type', $this->config['fieldDefinitions']);
    }

    #[Test]
    public function absence_type_has_select_field_type()
    {
        $field = $this->config['fieldDefinitions']['absence_type'];

        $this->assertEquals('select', $field['field_type']);
    }

    #[Test]
    public function absence_type_has_options()
    {
        $field = $this->config['fieldDefinitions']['absence_type'];

        $this->assertArrayHasKey('options', $field);
        $this->assertIsArray($field['options']);
    }

    // -----------------------------------------------------------------
    // Employee Relationship — References Hr Module (allowed for data configs)
    // -----------------------------------------------------------------

    #[Test]
    public function employee_id_has_relationship_to_hr_employee_model()
    {
        $field = $this->config['fieldDefinitions']['employee_id'];

        $this->assertArrayHasKey('relationship', $field);
        $this->assertEquals(
            'App\\Modules\\Hr\\Models\\Employee',
            $field['relationship']['model']
        );
    }
}
