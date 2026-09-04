{{--
    Location Reports page for the Organization Reports context group.
    
    This view resolves the /organization/reports/locations route.
    Future implementation will include domain-specific widgets:
    - Location count by country/region
    - Locations per company chart
    - Active vs. inactive locations
    - Recently added locations
    - Location type distribution (office, remote, warehouse, etc.)
--}}
<x-qf::navigation-layout configKey="organization.dashboards.dashboard_reports_locations" context="reports" moduleName="organization" :overrides="[]">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Location Reports</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Reports and analytics scoped to locations, including geographic distribution, status breakdowns, and location type analysis. Full location reporting functionality will be available in a future update.</p>
        </div>
    </div>
</x-qf::navigation-layout>