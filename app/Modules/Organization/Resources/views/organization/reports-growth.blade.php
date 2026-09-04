{{--
    Growth Reports page for the Organization Reports context group.
    
    This view resolves the /organization/reports/growth route.
    Future implementation will include domain-specific widgets:
    - Company creation trend (monthly/quarterly)
    - Department creation trend
    - Location expansion trend
    - Headcount growth overlay
    - Year-over-year comparison metrics
--}}
<x-qf::navigation-layout configKey="organization.dashboards.dashboard_reports_growth" context="reports" moduleName="organization" :overrides="[]">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Growth Reports</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Trend and growth analytics across the organization over time, including creation trends, expansion patterns, and year-over-year comparisons. Full growth reporting functionality will be available in a future update.</p>
        </div>
    </div>
</x-qf::navigation-layout>