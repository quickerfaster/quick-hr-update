<x-qf::navigation-layout configKey="loans.loan_type" context="loans" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-detail configKey="loans.loan_type" :recordId="$recordId" :returnParams="$returnParams" />
</x-qf::navigation-layout>
