<x-qf::navigation-layout configKey="admin.invitation" context="onboarding" moduleName="hr" :overrides="[]">
    {{-- Custom "Invite Employee" button that opens the HR invitation form with employee selector --}}
    <div class="mb-3 d-flex justify-content-end">
        <button type="button"
            class="btn btn-primary"
            onclick="Livewire.dispatch('openDrawer', {
                component: 'qf.hr-invitation-form',
                params: { configKey: 'admin.invitation', inline: true, crudType: 'drawers' },
                title: 'Invite Employee'
            })">
            <i class="fas fa-user-plus me-1"></i> Invite Employee
        </button>
    </div>

    <livewire:qf.invitation-data-table configKey="admin.invitation" />
</x-qf::navigation-layout>
