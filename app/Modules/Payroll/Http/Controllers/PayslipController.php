<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Payroll\Models\PayrollPayslip;
use App\Modules\Payroll\Services\PayslipService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function view(PayrollPayslip $payslip)
    {
        $this->authorizeAccess($payslip);

        // Stream PDF in browser (VIEW)
        return $this->getPdf($payslip)->stream("payslip-{$payslip->payslip_number}.pdf");
    }

    public function download(PayrollPayslip $payslip)
    {
        $this->authorizeAccess($payslip);

        // Force download (DOWNLOAD)
        return $this->getPdf($payslip)->download("payslip-{$payslip->payslip_number}.pdf");
    }

    /**
     * Authorize access to a payslip.
     *
     * Allows access if the user:
     *   1. Has the 'view_payroll_payslip' permission (HR/payroll staff), OR
     *   2. Is the employee who owns the payslip (self-service).
     *
     * Uses the library's AuthorizationService for admin bypass
     * (super_admin, admin, company_admin always pass).
     */
    private function authorizeAccess(PayrollPayslip $payslip): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'You must be logged in to view payslips.');
        }

        // Admin bypass via library AuthorizationService
        if (\QuickerFaster\UILibrary\Services\AccessControl\AuthorizationService::isBypassAllowed($user)) {
            return;
        }

        // Payroll/HR staff with explicit permission
        if ($user->can('view_payroll_payslip')) {
            return;
        }

        // Employee viewing their own payslip (self-service)
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $user->id)->first();
        if ($employee && (int) $payslip->employee_id === (int) $employee->id) {
            return;
        }

        abort(403, 'You are not authorized to view this payslip.');
    }


    public function getPdf(PayrollPayslip $payslip)
    {
        $service = app(PayslipService::class);
        return $service->generatePdf($payslip);
    }


}
