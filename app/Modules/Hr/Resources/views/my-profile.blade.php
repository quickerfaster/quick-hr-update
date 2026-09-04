<x-qf::navigation-layout
    context="my-portal"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    @livewire('qf.employee-detail', [
        'configKey' => 'hr.employee',
        'recordId' => \App\Modules\Hr\Models\Employee::where('user_id', auth()->id())->value('id'),
        'inline' => true,
    ])
</x-qf::navigation-layout>
