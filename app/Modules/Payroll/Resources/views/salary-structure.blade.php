<x-qf::navigation-layout configKey="payroll.employee_payroll_profile" context="configuration" moduleName="payroll" :overrides=[]>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Salary Structure — {{ $employee->first_name }} {{ $employee->last_name }}</h5>
                            <small class="text-muted">{{ $employee->employee_number }}</small>
                        </div>
                        <a href="{{ url('/payroll/employee-payroll-profiles') }}" class="btn btn-sm btn-outline-secondary" onclick="if(document.referrer.includes('employee-payroll-profiles')){history.back();return false;}">
                            <i class="fas fa-arrow-left me-1"></i> Go Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:payroll.salary-components-editor :employeeId="$employee->id" />
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> About Salary Components (BHT)</h6>
                </div>
                <div class="card-body">
                    <p class="text-sm mb-2">Under the <strong>Pension Reform Act 2014</strong>, pension contributions are calculated on your <strong>BHT</strong> (Basic + Housing + Transport).</p>
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr><th>Component</th><th>Part of BHT?</th><th>Used For</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>Basic Salary</strong></td><td><span class="badge bg-success">Yes</span></td><td>Pension (8%/10%), NHF (2.5%), NHIS (5%/10%), PAYE base</td></tr>
                            <tr><td><strong>Housing Allowance</strong></td><td><span class="badge bg-success">Yes</span></td><td>Pension base only</td></tr>
                            <tr><td><strong>Transport Allowance</strong></td><td><span class="badge bg-success">Yes</span></td><td>Pension base only</td></tr>
                            <tr><td><strong>Other Allowance</strong></td><td><span class="badge bg-secondary">No</span></td><td>Gross pay only (not pensionable)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-qf::navigation-layout>
