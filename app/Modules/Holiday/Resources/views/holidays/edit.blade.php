<x-qf::navigation-layout configKey="hr.holiday" context="time" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="hr.holiday" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>