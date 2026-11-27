<?php

namespace Tests\Unit;

use App\Modules\Attendance\Http\Livewire\AbsenceReportForm;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbsenceReportFormTest extends TestCase
{
    // -----------------------------------------------------------------
    // Rendering
    // -----------------------------------------------------------------

    #[Test]
    public function component_renders_all_six_fields()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->assertSee('Absence Type')
            ->assertSee('Start Date')
            ->assertSee('Expected Duration')
            ->assertSee('Reason')
            ->assertSee('Notify manager immediately')
            ->assertSee('Create draft leave request');
    }

    #[Test]
    public function component_renders_absence_type_options()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->assertSee('Sick Leave')
            ->assertSee('Family Emergency')
            ->assertSee('Personal Matter')
            ->assertSee('Official Assignment / Company Duty')
            ->assertSee('Other');
    }

    #[Test]
    public function component_renders_expected_duration_options()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->assertSee('1 day')
            ->assertSee('2–3 days')
            ->assertSee('4–5 days')
            ->assertSee('1–2 weeks')
            ->assertSee('3–4 weeks')
            ->assertSee('1–2 months')
            ->assertSee('Uncertain');
    }

    // -----------------------------------------------------------------
    // Default Values
    // -----------------------------------------------------------------

    #[Test]
    public function default_absence_type_is_sick_leave()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertEquals('sick_leave', $component->get('absenceType'));
    }

    #[Test]
    public function default_start_date_is_today()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertEquals(date('Y-m-d'), $component->get('startDate'));
    }

    #[Test]
    public function default_expected_duration_is_one_day()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertEquals('1', $component->get('expectedDuration'));
    }

    #[Test]
    public function default_create_leave_request_is_true()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertTrue($component->get('createLeaveRequest'));
    }

    #[Test]
    public function default_notify_manager_is_true()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertTrue($component->get('notifyManager'));
    }

    #[Test]
    public function default_absence_reason_is_empty()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertEquals('', $component->get('absenceReason'));
    }

    // -----------------------------------------------------------------
    // Validation — Rejections
    // -----------------------------------------------------------------

    #[Test]
    public function validation_rejects_empty_absence_type()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceType', '')
            ->call('save', 0)
            ->assertHasErrors(['absenceType' => 'required']);
    }

    #[Test]
    public function validation_rejects_future_start_date()
    {
        $futureDate = date('Y-m-d', strtotime('+1 day'));

        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('startDate', $futureDate)
            ->call('save', 0)
            ->assertHasErrors(['startDate' => 'before_or_equal']);
    }

    #[Test]
    public function validation_rejects_reason_shorter_than_10_characters()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', 'Too short')
            ->call('save', 0)
            ->assertHasErrors(['absenceReason' => 'min']);
    }

    #[Test]
    public function validation_rejects_empty_reason()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', '')
            ->call('save', 0)
            ->assertHasErrors(['absenceReason' => 'required']);
    }

    #[Test]
    public function validation_rejects_empty_start_date()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('startDate', '')
            ->call('save', 0)
            ->assertHasErrors(['startDate' => 'required']);
    }

    // -----------------------------------------------------------------
    // Validation — Acceptances
    // -----------------------------------------------------------------

    #[Test]
    public function validation_accepts_reason_of_exactly_10_characters()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', '1234567890') // exactly 10 chars
            ->call('save', 0)
            ->assertHasNoErrors(['absenceReason']);
    }

    #[Test]
    public function validation_accepts_reason_longer_than_10_characters()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', 'This is a valid reason for absence that is long enough.')
            ->call('save', 0)
            ->assertHasNoErrors(['absenceReason']);
    }

    #[Test]
    public function validation_accepts_today_as_start_date()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('startDate', date('Y-m-d'))
            ->call('save', 0)
            ->assertHasNoErrors(['startDate']);
    }

    #[Test]
    public function validation_accepts_past_start_date()
    {
        $pastDate = date('Y-m-d', strtotime('-3 days'));

        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('startDate', $pastDate)
            ->call('save', 0)
            ->assertHasNoErrors(['startDate']);
    }

    #[Test]
    public function validation_accepts_valid_absence_type()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceType', 'emergency')
            ->call('save', 0)
            ->assertHasNoErrors(['absenceType']);
    }

    // -----------------------------------------------------------------
    // Event Dispatching
    // -----------------------------------------------------------------

    #[Test]
    public function save_step_form_event_triggers_validation()
    {
        // When called with empty reason, validation should fail
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', '')
            ->call('save', 0)
            ->assertHasErrors(['absenceReason']);
    }

    #[Test]
    public function step_form_saved_dispatched_on_successful_save()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceType', 'sick_leave')
            ->set('startDate', date('Y-m-d'))
            ->set('absenceReason', 'Feeling unwell and need to rest today.')
            ->call('save', 0)
            ->assertDispatched('stepFormSaved', recordId: 0, stepIndex: 0);
    }

    #[Test]
    public function save_ignores_wrong_step_index()
    {
        // Calling save with a different stepIndex should be a no-op
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', '') // would normally fail validation
            ->call('save', 1)          // but stepIndex mismatch means it returns early
            ->assertHasNoErrors();
    }

    // -----------------------------------------------------------------
    // Session Storage
    // -----------------------------------------------------------------

    #[Test]
    public function session_contains_form_data_after_save()
    {
        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceType', 'emergency')
            ->set('startDate', '2026-11-20')
            ->set('expectedDuration', '5')
            ->set('absenceReason', 'Family emergency requiring my attention.')
            ->set('createLeaveRequest', false)
            ->set('notifyManager', true)
            ->call('save', 0);

        $sessionData = session()->get('absence_report_0');

        $this->assertNotNull($sessionData);
        $this->assertEquals('emergency', $sessionData['absence_type']);
        $this->assertEquals('2026-11-20', $sessionData['start_date']);
        $this->assertEquals('5', $sessionData['expected_duration']);
        $this->assertEquals('Family emergency requiring my attention.', $sessionData['absence_reason']);
        $this->assertFalse($sessionData['create_leave_request']);
        $this->assertTrue($sessionData['notify_manager']);
    }

    // -----------------------------------------------------------------
    // Property Types
    // -----------------------------------------------------------------

    #[Test]
    public function step_index_is_integer()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertIsInt($component->get('stepIndex'));
    }

    #[Test]
    public function create_leave_request_is_boolean()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertIsBool($component->get('createLeaveRequest'));
    }

    #[Test]
    public function notify_manager_is_boolean()
    {
        $component = Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0]);

        $this->assertIsBool($component->get('notifyManager'));
    }

    // -----------------------------------------------------------------
    // Edge Cases
    // -----------------------------------------------------------------

    #[Test]
    public function reason_at_max_length_is_accepted()
    {
        $reason = str_repeat('A', 500);

        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', $reason)
            ->call('save', 0)
            ->assertHasNoErrors(['absenceReason']);
    }

    #[Test]
    public function reason_exceeding_max_length_is_rejected()
    {
        $reason = str_repeat('A', 501);

        Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
            ->set('absenceReason', $reason)
            ->call('save', 0)
            ->assertHasErrors(['absenceReason' => 'max']);
    }

    #[Test]
    public function all_absence_types_are_accepted()
    {
        $validTypes = ['sick_leave', 'emergency', 'personal', 'official_assignment', 'other'];

        foreach ($validTypes as $type) {
            Livewire::test(AbsenceReportForm::class, ['stepIndex' => 0])
                ->set('absenceType', $type)
                ->set('absenceReason', 'Valid reason text for testing purposes.')
                ->call('save', 0)
                ->assertHasNoErrors(['absenceType']);
        }
    }

    #[Test]
    public function employee_id_can_be_set_via_preset_data()
    {
        $component = Livewire::test(AbsenceReportForm::class, [
            'stepIndex' => 0,
            'presetData' => ['employee_id' => 42],
        ]);

        $this->assertEquals(42, $component->get('employeeId'));
    }

    #[Test]
    public function employee_id_can_be_set_directly()
    {
        $component = Livewire::test(AbsenceReportForm::class, [
            'stepIndex' => 0,
            'employeeId' => 99,
        ]);

        $this->assertEquals(99, $component->get('employeeId'));
    }

    #[Test]
    public function direct_employee_id_takes_precedence_over_preset_data()
    {
        $component = Livewire::test(AbsenceReportForm::class, [
            'stepIndex' => 0,
            'employeeId' => 99,
            'presetData' => ['employee_id' => 42],
        ]);

        $this->assertEquals(99, $component->get('employeeId'));
    }
}
