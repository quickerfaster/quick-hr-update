{{--
    Department Reports page for the Organization Reports context group.
    
    This view resolves the /organization/reports/departments route.
    Future implementation will include domain-specific widgets:
    - Department count stat
    - Departments per company chart
    - Department hierarchy depth analysis
    - Departments with most sub-departments
    - Recently created/updated departments
--}}
<x-qf::navigation-layout configKey="organization.dashboards.dashboard_reports_departments" context="reports" moduleName="organization" :overrides="[]">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Department Reports</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Reports and analytics scoped to departments, including hierarchy analysis, distribution across companies, and departmental growth trends. Full department reporting functionality will be available in a future update.</p>
        </div>
    </div>
</x-qf::navigation-layout>