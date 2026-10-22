<?php

namespace App\Modules\Loans\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Company as HrCompany;
use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanType;
use App\Modules\Loans\Models\LoanRepayment;
use App\Modules\Loans\Services\LoanRepaymentService;
use App\Models\User;
use QuickerFaster\UILibrary\Models\Role;

class LoanRepaymentServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HrCompany $company;
    protected Department $department;
    protected Employee $employee;
    protected User $user;
    protected LoanType $loanType;
    protected Loan $loan;
    protected LoanRepaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = HrCompany::factory()->create(['name' => 'Test Corp', 'is_active' => true]);
        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Engineering',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Test Employee',
            'email' => 'repaymenttest@example.com',
        ]);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $this->user->assignRole($employeeRole);

        $this->employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'Repayment',
            'last_name' => 'Tester',
            'email' => 'repaymenttest@example.com',
            'hire_date' => '2026-01-01',
        ]);

        EmployeePosition::factory()->create([
            'employee_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'company_id' => $this->company->id,
            'base_salary' => 5000.00,
            'employment_status' => 'Active',
        ]);

        $this->loanType = LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'Test Loan Type',
            'min_amount' => 100,
            'max_amount' => 10000,
            'default_interest_rate' => 0,
            'max_repayment_months' => 12,
            'is_active' => true,
        ]);

        $this->loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-SVC-001',
            'amount' => 6000,
            'interest_rate' => 0,
            'repayment_months' => 6,
            'monthly_installment' => 1000,
            'total_repayable' => 6000,
            'paid_amount' => 0,
            'balance' => 6000,
            'status' => 'active',
        ]);

        $this->service = app(LoanRepaymentService::class);
    }

    // =========================================================================
    // Schedule Generation Tests
    // =========================================================================

    #[Test]
    public function it_generates_repayment_schedule(): void
    {
        $schedule = $this->service->generateSchedule($this->loan, '2026-11-01');

        $this->assertCount(6, $schedule);
        $this->assertEquals('2026-11-01', $schedule[0]['due_date']);
    }

    #[Test]
    public function it_persists_repayment_records(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');

        $repayments = LoanRepayment::where('loan_id', $this->loan->id)->get();
        $this->assertCount(6, $repayments);
        $this->assertEquals('pending', $repayments->first()->status);
    }

    #[Test]
    public function it_updates_loan_dates_on_schedule_generation(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');

        $this->loan->refresh();
        $this->assertEquals('2026-11-01', $this->loan->start_date->format('Y-m-d'));
        $this->assertEquals('2027-04-01', $this->loan->expected_end_date->format('Y-m-d'));
    }

    #[Test]
    public function it_caches_schedule_on_loan(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');

        $this->loan->refresh();
        $this->assertIsArray($this->loan->repayment_schedule);
        $this->assertCount(6, $this->loan->repayment_schedule);
    }

    #[Test]
    public function it_replaces_existing_schedule_on_regeneration(): void
    {
        // Generate first schedule
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $this->assertEquals(6, LoanRepayment::where('loan_id', $this->loan->id)->count());

        // Regenerate with different term
        $this->loan->update(['repayment_months' => 3, 'monthly_installment' => 2000, 'total_repayable' => 6000]);
        $this->service->generateSchedule($this->loan, '2026-12-01');

        $this->assertEquals(3, LoanRepayment::where('loan_id', $this->loan->id)->count());
    }

    // =========================================================================
    // Mark as Paid Tests
    // =========================================================================

    #[Test]
    public function it_marks_repayment_as_paid(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayment = LoanRepayment::where('loan_id', $this->loan->id)->first();

        $this->service->markAsPaid($repayment, 'manual', 'REF-001');

        $repayment->refresh();
        $this->assertEquals('paid', $repayment->status);
        $this->assertEquals('manual', $repayment->payment_method);
        $this->assertEquals('REF-001', $repayment->reference);
        $this->assertNotNull($repayment->paid_date);
    }

    #[Test]
    public function it_updates_loan_balance_on_payment(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayment = LoanRepayment::where('loan_id', $this->loan->id)->first();

        $this->service->markAsPaid($repayment);

        $this->loan->refresh();
        $this->assertEquals(1000.0, $this->loan->paid_amount);
        $this->assertEquals(5000.0, $this->loan->balance);
    }

    #[Test]
    public function it_auto_completes_loan_when_fully_paid(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayments = LoanRepayment::where('loan_id', $this->loan->id)->get();

        foreach ($repayments as $repayment) {
            $this->service->markAsPaid($repayment);
        }

        $this->loan->refresh();
        $this->assertEquals('completed', $this->loan->status);
        $this->assertEquals(6000.0, $this->loan->paid_amount);
        $this->assertEquals(0.0, $this->loan->balance);
    }

    #[Test]
    public function it_links_repayment_to_payroll_run(): void
    {
        // Create a payroll run so the FK constraint is satisfied
        $payrollRun = \App\Modules\Payroll\Models\PayrollRun::create([
            'title' => 'Test Payroll Run',
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'status' => 'draft',
            'calculation_status' => 'pending',
        ]);

        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayment = LoanRepayment::where('loan_id', $this->loan->id)->first();

        $this->service->markAsPaid(
            repayment: $repayment,
            paymentMethod: 'payroll_deduction',
            payrollRunId: $payrollRun->id
        );

        $repayment->refresh();
        $this->assertEquals('payroll_deduction', $repayment->payment_method);
        $this->assertEquals($payrollRun->id, $repayment->payroll_run_id);
    }

    // =========================================================================
    // Waive Tests
    // =========================================================================

    #[Test]
    public function it_waives_a_repayment(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayment = LoanRepayment::where('loan_id', $this->loan->id)->first();

        $this->service->waive($repayment);

        $repayment->refresh();
        $this->assertEquals('waived', $repayment->status);
    }

    #[Test]
    public function it_recalculates_balance_after_waive(): void
    {
        $this->service->generateSchedule($this->loan, '2026-11-01');
        $repayments = LoanRepayment::where('loan_id', $this->loan->id)->get();

        // Pay first, waive second
        $this->service->markAsPaid($repayments[0]);
        $this->service->waive($repayments[1]);

        $this->loan->refresh();
        // paid_amount should only count paid (not waived)
        $this->assertEquals(1000.0, $this->loan->paid_amount);
        // balance = total_repayable - paid = 6000 - 1000 = 5000
        $this->assertEquals(5000.0, $this->loan->balance);
    }

    // =========================================================================
    // Overdue Processing Tests
    // =========================================================================

    #[Test]
    public function it_marks_past_due_repayments_as_overdue(): void
    {
        $this->service->generateSchedule($this->loan, '2020-01-01'); // All in the past

        $count = $this->service->processOverdueRepayments();

        $this->assertEquals(6, $count);

        $overdue = LoanRepayment::where('loan_id', $this->loan->id)
            ->where('status', 'overdue')
            ->count();
        $this->assertEquals(6, $overdue);
    }

    #[Test]
    public function it_does_not_mark_future_repayments_as_overdue(): void
    {
        $this->service->generateSchedule($this->loan, '2099-01-01'); // All in the future

        $count = $this->service->processOverdueRepayments();

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function it_does_not_mark_already_paid_repayments(): void
    {
        $this->service->generateSchedule($this->loan, '2020-01-01');
        $repayment = LoanRepayment::where('loan_id', $this->loan->id)->first();
        $this->service->markAsPaid($repayment);

        $count = $this->service->processOverdueRepayments();

        // 5 pending past-due, 1 already paid
        $this->assertEquals(5, $count);
        $repayment->refresh();
        $this->assertEquals('paid', $repayment->status);
    }

    // =========================================================================
    // Upcoming Due Repayments Tests
    // =========================================================================

    #[Test]
    public function it_gets_upcoming_due_repayments(): void
    {
        // Generate schedule starting 2 days from now
        // Only the first installment falls within the 7-day lookahead
        // (subsequent installments are 1 month apart)
        $this->service->generateSchedule($this->loan, now()->addDays(2)->toDateString());

        $upcoming = $this->service->getUpcomingDueRepayments(7);

        // Only the first installment is within 7 days
        $this->assertCount(1, $upcoming);
    }

    #[Test]
    public function it_excludes_repayments_beyond_lookahead(): void
    {
        $this->service->generateSchedule($this->loan, now()->addDays(30)->toDateString());

        $upcoming = $this->service->getUpcomingDueRepayments(7);

        $this->assertCount(0, $upcoming);
    }
}
