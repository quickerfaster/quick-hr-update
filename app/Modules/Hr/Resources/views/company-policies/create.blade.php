<x-qf::navigation-layout configKey="policies.company_policy" context="policies" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="policies.company_policy" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>
