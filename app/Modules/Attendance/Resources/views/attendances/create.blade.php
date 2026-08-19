<x-qf::navigation-layout configKey="attendance.attendance" context="time" moduleName="attendance" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
        @php
        $prefilled = collect(request()->query())
            ->except(['page', 'perPage', 'search', 'sort', 'activeFilters'])
            ->toArray();
    @endphp
    <livewire:qf.data-table-form :inline="true" configKey="attendance.attendance" :prefilledData="$prefilled" :returnParams="$returnParams" />
</x-qf::navigation-layout>