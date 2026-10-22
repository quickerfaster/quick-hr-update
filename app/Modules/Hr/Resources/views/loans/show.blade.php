<x-qf::navigation-layout configKey="loans.loan" context="loans" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    @livewire('qf.loans.detail', ['recordId' => $recordId])
</x-qf::navigation-layout>
