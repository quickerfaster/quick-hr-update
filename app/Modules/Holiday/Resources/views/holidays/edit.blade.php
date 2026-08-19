<x-qf::navigation-layout configKey="holiday.holiday" context="time" moduleName="holiday" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="holiday.holiday" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>