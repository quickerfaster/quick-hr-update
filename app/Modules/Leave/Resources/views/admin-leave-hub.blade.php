<x-qf::navigation-layout
    context="leave"
    moduleName="leave"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    @livewire('qf.admin-leave-hub')
</x-qf::navigation-layout>
