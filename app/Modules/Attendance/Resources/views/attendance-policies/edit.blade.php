<x-qf::navigation-layout configKey="hr.attendance_policy" context="policies" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="hr.attendance_policy" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>