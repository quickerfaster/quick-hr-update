<x-layout>


    @php
        $attendance_id = request()->get('attendance_id');
        $employee = null;
        $subPageTitle = 'Work Sessions';

        if ($attendance_id) {
            $attendance = \App\Modules\Attendance\Models\Attendance::where('id', $attendance_id)->first();
            if ($attendance && $attendance->employee_id) {
                $employee = \App\Modules\Hr\Models\Employee::find($attendance->employee_id);
            }
        }

        if ($employee) {
            $subPageTitle = 'For ' . $employee->first_name . ' ' . $employee->last_name . ' (' . $employee->employee_number . ')';
        } else {
            abort(404, 'Attendance record or associated employee not found.');
        }
    @endphp



    <a href="{{ url()->previous() }}" class="btn bg-gradient-secondary btn-sm my-0">
        <i class="bi bi-arrow-left"></i> &larr; Go Back
    </a>


    <livewire:qf.data-table
        configKey="attendance.attendance_session"
        :pageTitle="'Work Sessions — ' . $subPageTitle"
        :pageQueryFilters="[['attendance_id', '=', $attendance_id]]"
        :hiddenFields="[
            'attendance_id',
            'clock_in_event_id',
            'clock_out_event_id',
            'is_overnight',
            'adjusted_by',
            'adjusted_at',
            'calculated_duration',
            'validation_status',
            'validation_notes',
        ]" />
</x-layout>
