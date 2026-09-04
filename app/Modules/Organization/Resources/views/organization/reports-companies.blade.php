{{--
    Company Reports page for the Organization Reports context group.
    
    This view resolves the /organization/reports/companies route.
    Future implementation will include domain-specific widgets:
    - Company count by status (active/inactive)
    - Companies by type/industry chart
    - Companies created over time trend
    - Top companies by employee count
    - Company geographic distribution
--}}
<x-qf::navigation-layout configKey="organization.dashboards.dashboard_reports_companies" context="reports" moduleName="organization" :overrides="[]">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Company Reports</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Reports and analytics scoped to companies, including status breakdowns, industry distribution, creation trends, and geographic insights. Full company reporting functionality will be available in a future update.</p>
        </div>
    </div>
</x-qf::navigation-layout>