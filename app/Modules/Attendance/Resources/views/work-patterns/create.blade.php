<x-qf::navigation-layout configKey="attendance.work_pattern" context="policies" moduleName="attendance" :overrides="[
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
    <livewire:qf.data-table-form :inline="true" configKey="attendance.work_pattern" :prefilledData="$prefilled" :returnParams="$returnParams" />
</x-qf::navigation-layout>