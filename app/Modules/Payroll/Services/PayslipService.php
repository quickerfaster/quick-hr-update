<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\PayrollPayslip;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;
use PDF;

class PayslipService
{
    use HasCurrencySymbol;

    public function generatePdf(PayrollPayslip $payslip)
    {
        // Load employee, payroll run, and line items
        $payslip->load(['employee.company', 'payrollRun', 'items']);

        // Resolve currency symbol
        $emp = $payslip->employee;
        $currencyCode = $emp?->company?->currency_code
            ?? $payslip->currency_code
            ?? 'USD';
        $currencySymbol = $this->getCurrencySymbol($currencyCode);

        // Group items by type, filter out zero amounts
        $earnings = $payslip->items->filter(fn($i) => $i->type === 'earning' && $i->amount > 0);
        $deductions = $payslip->items->filter(fn($i) => in_array($i->type, ['deduction', 'tax']) && $i->amount > 0);
        $employerContributions = $payslip->items->filter(fn($i) => $i->type === 'employer_contribution' && $i->amount > 0);

        // Resolve company info from the employee's company
        $company = $emp?->company;
        $companyName = $company?->name ?? config('app.name', 'QuickHR');
        $companyAddress = $company
            ? implode(', ', array_filter([$company->address, $company->city, $company->state_code, $company->postal_code, $company->country_code]))
            : '';

        // Resolve signatory names from user IDs
        $run = $payslip->payrollRun;
        $preparedByName = null;
        if ($run->created_by) {
            $preparedByName = optional(\App\Models\User::find($run->created_by))->name;
        }
        // Fallback: show current authenticated user for prepared_by
        if (!$preparedByName && auth()->check()) {
            $preparedByName = auth()->user()->name;
        }

        $approvedByName = optional($run->approvedByUser)->name
            ?? ($run->approved_by ? optional(\App\Models\User::find($run->approved_by))->name : null)
            ?? ($run->processed_by ?: null);

        // Fallback: if run is approved/paid but no approver recorded, use current user
        if (!$approvedByName && in_array($run->status, ['approved', 'paid'])) {
            $approvedByName = auth()->user()?->name;
        }

        $data = [
            'company' => [
                'name' => $companyName,
                'address' => $companyAddress,
                'phone' => $company?->phone ?? '',
                'email' => $company?->email ?? '',
                'logo_path' => public_path('images/company-logo.png') // Optional
            ],
            'employee' => [
                'name' => $payslip->employee->first_name . ' ' . $payslip->employee->last_name,
                'id' => $payslip->employee->employee_number,
                'address' => $this->formatEmployeeAddress($payslip->employee),
            ],
            'payroll_run' => [
                'id' => $run->id,
                'title' => $run->title,
                'period_start' => $run->period_start?->format('M d, Y'),
                'period_end' => $run->period_end?->format('M d, Y'),
                'payment_date' => $run->payment_date?->format('M d, Y'),
                'prepared_by' => $preparedByName,
                'approved_by' => $approvedByName,
            ],
            'payslip' => [
                'currency_symbol' => $currencySymbol,
                'number' => $payslip->payslip_number,
                'gross_pay' => $payslip->gross_pay,
                'total_deductions' => $payslip->total_deductions,
                'net_pay' => $payslip->net_pay,
                'paid_at' => $payslip->paid_at,
                'earnings' => $earnings,
                'deductions' => $deductions,
                'employer_contributions' => $employerContributions,
            ]
        ];


        return PDF::loadView('payroll::components.livewire.bootstrap.payroll.payslips.payslip-pdf', $data);
    }

    private function formatEmployeeAddress($employee): string
    {
        $parts = [];
        if ($employee->address_street) $parts[] = $employee->address_street;
        if ($employee->address_city) $parts[] = $employee->address_city;
        if ($employee->address_state) $parts[] = $employee->address_state;
        if ($employee->address_postal_code) $parts[] = $employee->address_postal_code;
        if ($employee->address_country) $parts[] = $employee->address_country;

        return implode(', ', $parts);
    }
}
