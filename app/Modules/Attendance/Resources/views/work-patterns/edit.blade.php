<x-qf::navigation-layout configKey="attendance.work_pattern" context="policies" moduleName="attendance" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="attendance.work_pattern" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>