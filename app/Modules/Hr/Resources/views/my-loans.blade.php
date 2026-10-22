@php
    use App\Modules\Hr\Models\Employee;
    use Illuminate\Support\Facades\Auth;

    $employee = Employee::where('user_id', Auth::id())->first();

    if (!$employee) {
        abort(403, 'No employee record found for your account. Please contact HR.');
    }
@endphp

<x-qf::navigation-layout
    context="my-portal"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    @livewire('qf.loans.my-hub', ['employeeId' => $employee->id])
</x-qf::navigation-layout>
