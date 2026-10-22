<?php

namespace App\Modules\Loans\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Company as HrCompany;
use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanType;
use App\Modules\Loans\Models\LoanRepayment;
use App\Models\User;
use QuickerFaster\UILibrary\Models\Role;

class LoanModelTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HrCompany $company;
    protected Department $department;
    protected Employee $employee;
    protected User $user;
    protected LoanType $loanType;

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
            'email' => 'loantest@example.com',
        ]);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $this->user->assignRole($employeeRole);

        $this->employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'Loan',
            'last_name' => 'Tester',
            'email' => 'loantest@example.com',
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
            'name' => 'Salary Advance',
            'description' => 'Short-term salary advance',
            'min_amount' => 100,
            'max_amount' => 10000,
            'default_interest_rate' => 5.0,
            'max_repayment_months' => 12,
            'requires_approval' => true,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // LoanType Model Tests
    // =========================================================================

    #[Test]
    public function it_creates_a_loan_type_with_defaults(): void
    {
        $type = LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'Emergency Loan',
        ]);

        $this->assertNotNull($type->id);
        $this->assertEquals('Emergency Loan', $type->name);
        $this->assertTrue($type->is_active);
        $this->assertTrue($type->requires_approval);
        $this->assertEquals(0.0, $type->default_interest_rate);
        $this->assertEquals(12, $type->max_repayment_months);
    }

    #[Test]
    public function it_casts_loan_type_fields(): void
    {
        $type = LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'Test Loan',
            'min_amount' => 500.50,
            'max_amount' => 5000.75,
            'default_interest_rate' => 7.5,
            'requires_approval' => false,
            'is_active' => true,
            'eligibility_rules' => ['min_tenure_months' => 6, 'max_salary_percent' => 30],
        ]);

        $this->assertEquals(500.50, $type->min_amount);
        $this->assertEquals(5000.75, $type->max_amount);
        $this->assertEquals(7.5, $type->default_interest_rate);
        $this->assertFalse($type->requires_approval);
        $this->assertIsArray($type->eligibility_rules);
        $this->assertEquals(6, $type->eligibility_rules['min_tenure_months']);
    }

    #[Test]
    public function it_filters_active_loan_types(): void
    {
        LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'Active Type',
            'is_active' => true,
        ]);

        LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'Inactive Type',
            'is_active' => false,
        ]);

        $active = LoanType::active()->get();
        $this->assertCount(2, $active); // includes setUp loanType + Active Type
        $this->assertTrue($active->contains('name', 'Active Type'));
        $this->assertFalse($active->contains('name', 'Inactive Type'));
    }

    #[Test]
    public function it_soft_deletes_loan_types(): void
    {
        $type = LoanType::create([
            'company_id' => $this->company->id,
            'name' => 'To Delete',
        ]);

        $type->delete();
        $this->assertSoftDeleted('loan_types', ['id' => $type->id]);
    }

    // =========================================================================
    // Loan Model Tests
    // =========================================================================

    #[Test]
    public function it_creates_a_loan_with_defaults(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00001',
            'amount' => 5000,
            'repayment_months' => 6,
            'monthly_installment' => 845.60,
            'total_repayable' => 5073.60,
            'balance' => 5073.60,
        ]);

        $this->assertNotNull($loan->id);
        $this->assertEquals('LN-2026-00001', $loan->loan_number);
        $this->assertEquals('draft', $loan->status);
        $this->assertEquals(0.0, $loan->paid_amount);
        $this->assertEquals(0.0, $loan->interest_rate);
    }

    #[Test]
    public function it_casts_loan_fields(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00002',
            'amount' => 3000.75,
            'interest_rate' => 5.5,
            'repayment_months' => 3,
            'monthly_installment' => 1010.25,
            'total_repayable' => 3030.75,
            'balance' => 3030.75,
            'request_date' => '2026-10-15',
            'approval_date' => '2026-10-16',
            'start_date' => '2026-11-01',
            'expected_end_date' => '2027-02-01',
            'repayment_schedule' => [['installment_number' => 1, 'due_date' => '2026-11-01']],
        ]);

        $this->assertEquals(3000.75, $loan->amount);
        $this->assertEquals(5.5, $loan->interest_rate);
        $this->assertInstanceOf(Carbon::class, $loan->request_date);
        $this->assertEquals('2026-10-15', $loan->request_date->format('Y-m-d'));
        $this->assertInstanceOf(Carbon::class, $loan->start_date);
        $this->assertIsArray($loan->repayment_schedule);
        $this->assertEquals(1, $loan->repayment_schedule[0]['installment_number']);
    }

    #[Test]
    public function it_belongs_to_employee(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00003',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
        ]);

        $this->assertNotNull($loan->employee);
        $this->assertEquals($this->employee->id, $loan->employee->id);
        $this->assertEquals('Loan', $loan->employee->first_name);
    }

    #[Test]
    public function it_belongs_to_loan_type(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00004',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
        ]);

        $this->assertNotNull($loan->loanType);
        $this->assertEquals('Salary Advance', $loan->loanType->name);
    }

    #[Test]
    public function it_has_many_repayments(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00005',
            'amount' => 3000,
            'repayment_months' => 3,
            'monthly_installment' => 1000,
            'total_repayable' => 3000,
            'balance' => 3000,
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-11-01',
            'status' => 'pending',
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-12-01',
            'status' => 'pending',
        ]);

        $this->assertCount(2, $loan->repayments);
    }

    #[Test]
    public function it_filters_active_loans(): void
    {
        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00006',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
            'status' => 'active',
        ]);

        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00007',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
            'status' => 'completed',
        ]);

        $active = Loan::active()->get();
        $this->assertCount(1, $active);
        $this->assertEquals('LN-2026-00006', $active->first()->loan_number);
    }

    #[Test]
    public function it_filters_pending_approval_loans(): void
    {
        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00008',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
            'status' => 'pending_approval',
        ]);

        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00009',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
            'status' => 'draft',
        ]);

        $pending = Loan::pendingApproval()->get();
        $this->assertCount(1, $pending);
        $this->assertEquals('LN-2026-00008', $pending->first()->loan_number);
    }

    #[Test]
    public function it_filters_loans_for_employee(): void
    {
        $otherEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Other',
            'last_name' => 'Employee',
            'email' => 'other@example.com',
        ]);

        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00010',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $otherEmployee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00011',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
        ]);

        $myLoans = Loan::forEmployee($this->employee->id)->get();
        $this->assertCount(1, $myLoans);
        $this->assertEquals('LN-2026-00010', $myLoans->first()->loan_number);
    }

    #[Test]
    public function it_implements_workflowable_contract(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00012',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $this->assertEquals($loan->id, $loan->getWorkflowableId());
        $this->assertEquals('loan_request', $loan->getWorkflowDefinitionKey());
        $this->assertIsArray($loan->getWorkflowContext());
        $this->assertEquals($this->company->id, $loan->getWorkflowContext()['company_id']);
        $this->assertEquals($this->employee->id, $loan->getWorkflowContext()['employee_id']);
    }

    #[Test]
    public function it_implements_documentable_contract(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00013',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $this->assertEquals($loan->id, $loan->getDocumentableId());
        $this->assertEquals('loan_request', $loan->getDocumentType());
        $this->assertEquals('loans/' . $loan->id, $loan->getDocumentStoragePath());
        $this->assertIsArray($loan->getDocumentTemplateData());
        $this->assertEquals('LN-2026-00013', $loan->getDocumentTemplateData()['loan_number']);
    }

    #[Test]
    public function it_soft_deletes_loans(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00014',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $loan->delete();
        $this->assertSoftDeleted('loans', ['id' => $loan->id]);
    }

    // =========================================================================
    // LoanRepayment Model Tests
    // =========================================================================

    #[Test]
    public function it_creates_a_loan_repayment(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00015',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 950,
            'interest' => 50,
            'due_date' => '2026-11-01',
            'status' => 'pending',
        ]);

        $this->assertNotNull($repayment->id);
        $this->assertEquals('pending', $repayment->status);
        $this->assertEquals(950.0, $repayment->principal);
        $this->assertEquals(50.0, $repayment->interest);
    }

    #[Test]
    public function it_casts_repayment_dates(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00016',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-11-15',
            'paid_date' => '2026-11-10',
        ]);

        $this->assertInstanceOf(Carbon::class, $repayment->due_date);
        $this->assertInstanceOf(Carbon::class, $repayment->paid_date);
        $this->assertEquals('2026-11-15', $repayment->due_date->format('Y-m-d'));
    }

    #[Test]
    public function it_belongs_to_loan(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00017',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-11-01',
        ]);

        $this->assertNotNull($repayment->loan);
        $this->assertEquals('LN-2026-00017', $repayment->loan->loan_number);
    }

    #[Test]
    public function it_filters_pending_repayments(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00018',
            'amount' => 2000,
            'repayment_months' => 2,
            'monthly_installment' => 1000,
            'total_repayable' => 2000,
            'balance' => 2000,
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-11-01',
            'status' => 'pending',
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-12-01',
            'status' => 'paid',
            'paid_date' => '2026-12-01',
        ]);

        $pending = LoanRepayment::pending()->get();
        $this->assertCount(1, $pending);
        $this->assertEquals('pending', $pending->first()->status);
    }

    #[Test]
    public function it_filters_overdue_repayments(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00019',
            'amount' => 1000,
            'repayment_months' => 1,
            'monthly_installment' => 1000,
            'total_repayable' => 1000,
            'balance' => 1000,
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2026-11-01',
            'status' => 'overdue',
        ]);

        $overdue = LoanRepayment::overdue()->get();
        $this->assertCount(1, $overdue);
    }

    #[Test]
    public function it_filters_repayments_due_by_date(): void
    {
        $loan = Loan::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'loan_type_id' => $this->loanType->id,
            'loan_number' => 'LN-2026-00020',
            'amount' => 3000,
            'repayment_months' => 3,
            'monthly_installment' => 1000,
            'total_repayable' => 3000,
            'balance' => 3000,
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2020-01-01',
            'status' => 'pending',
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal' => 1000,
            'interest' => 0,
            'due_date' => '2099-12-31',
            'status' => 'pending',
        ]);

        $due = LoanRepayment::dueBy('2025-01-01')->get();
        $this->assertCount(1, $due);
        $this->assertEquals('2020-01-01', $due->first()->due_date->format('Y-m-d'));
    }
}
