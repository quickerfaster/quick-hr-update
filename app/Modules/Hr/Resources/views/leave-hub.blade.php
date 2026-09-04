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
    @livewire('qf.leave-hub')
</x-qf::navigation-layout>