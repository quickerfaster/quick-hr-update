<?php

namespace App\Modules\Policies\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Company as HrCompany;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Models\PolicyAcknowledgment;
use App\Modules\Policies\Services\PolicyDistributionService;
use App\Models\User;
use QuickerFaster\UILibrary\Models\Role;

class CompanyPolicyTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected HrCompany $company;
    protected Department $department;
    protected Employee $employee;
    protected User $user;

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
            'email' => 'test@example.com',
        ]);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $this->user->assignRole($employeeRole);

        $this->employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'test@example.com',
        ]);

        EmployeePosition::factory()->create([
            'employee_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'company_id' => $this->company->id,
        ]);
    }

    // =========================================================================
    // CompanyPolicy Model Tests
    // =========================================================================

    #[Test]
    public function it_creates_a_company_policy_with_defaults(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Code of Conduct',
            'category' => 'code_of_conduct',
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($policy->id);
        $this->assertEquals('Code of Conduct', $policy->title);
        $this->assertEquals('code_of_conduct', $policy->category);
        $this->assertEquals('draft', $policy->status);
        $this->assertEquals('1.0', $policy->version);
        $this->assertEquals('medium', $policy->priority);
        $this->assertFalse($policy->requires_acknowledgment);
    }

    #[Test]
    public function it_casts_boolean_and_date_fields(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'IT Policy',
            'category' => 'it_policy',
            'requires_acknowledgment' => true,
            'effective_date' => '2026-10-15',
            'review_date' => '2027-10-15',
            'acknowledgment_required_by' => '2026-11-01',
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($policy->requires_acknowledgment);
        $this->assertInstanceOf(Carbon::class, $policy->effective_date);
        $this->assertEquals('2026-10-15', $policy->effective_date->format('Y-m-d'));
        $this->assertInstanceOf(Carbon::class, $policy->acknowledgment_required_by);
    }

    #[Test]
    public function it_casts_distribution_scope_as_array(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Engineering Policy',
            'category' => 'other',
            'distribution_scope' => ['departments' => [1, 2], 'roles' => ['manager']],
            'created_by' => $this->user->id,
        ]);

        $this->assertIsArray($policy->distribution_scope);
        $this->assertEquals([1, 2], $policy->distribution_scope['departments']);
        $this->assertEquals(['manager'], $policy->distribution_scope['roles']);
    }

    #[Test]
    public function it_filters_published_policies(): void
    {
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Draft Policy',
            'category' => 'other',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Published Policy',
            'category' => 'other',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Archived Policy',
            'category' => 'other',
            'status' => 'archived',
            'created_by' => $this->user->id,
        ]);

        $published = CompanyPolicy::published()->get();
        $this->assertCount(1, $published);
        $this->assertEquals('Published Policy', $published->first()->title);
    }

    #[Test]
    public function it_filters_policies_requiring_acknowledgment(): void
    {
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'No Ack Required',
            'category' => 'other',
            'status' => 'published',
            'requires_acknowledgment' => false,
            'created_by' => $this->user->id,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Ack Required',
            'category' => 'other',
            'status' => 'published',
            'requires_acknowledgment' => true,
            'created_by' => $this->user->id,
        ]);

        $requiring = CompanyPolicy::published()->requiringAcknowledgment()->get();
        $this->assertCount(1, $requiring);
        $this->assertEquals('Ack Required', $requiring->first()->title);
    }

    #[Test]
    public function it_implements_documentable_contract(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Test Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals($policy->id, $policy->getDocumentableId());
        $this->assertEquals('company_policy', $policy->getDocumentType());
        $this->assertEquals('policies/' . $policy->id, $policy->getDocumentStoragePath());
        $this->assertIsArray($policy->getDocumentTemplateData());
        $this->assertEquals('Test Policy', $policy->getDocumentTemplateData()['policy_title']);
    }

    #[Test]
    public function it_soft_deletes_policies(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'To Be Deleted',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        $policy->delete();

        $this->assertSoftDeleted('company_policies', ['id' => $policy->id]);
        $this->assertNull(CompanyPolicy::find($policy->id));
        $this->assertNotNull(CompanyPolicy::withTrashed()->find($policy->id));
    }

    // =========================================================================
    // PolicyAcknowledgment Model Tests
    // =========================================================================

    #[Test]
    public function it_creates_an_acknowledgment(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Ack Policy',
            'category' => 'other',
            'status' => 'published',
            'requires_acknowledgment' => true,
            'created_by' => $this->user->id,
        ]);

        $ack = PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'ip_address' => '127.0.0.1',
            'signature' => 'Test Employee',
        ]);

        $this->assertNotNull($ack->id);
        $this->assertEquals('acknowledged', $ack->status);
        $this->assertEquals('Test Employee', $ack->signature);
    }

    #[Test]
    public function it_defaults_to_pending_status(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Pending Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        $ack = PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
        ]);

        $this->assertEquals('pending', $ack->status);
    }

    #[Test]
    public function it_enforces_unique_employee_policy_constraint(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Unique Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
            'status' => 'acknowledged',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
            'status' => 'acknowledged',
        ]);
    }

    #[Test]
    public function it_belongs_to_company_policy(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Relation Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        $ack = PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
        ]);

        $this->assertNotNull($ack->companyPolicy);
        $this->assertEquals('Relation Policy', $ack->companyPolicy->title);
    }

    #[Test]
    public function it_belongs_to_employee(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Employee Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        $ack = PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
        ]);

        $this->assertNotNull($ack->employee);
        $this->assertEquals('Test', $ack->employee->first_name);
    }

    #[Test]
    public function it_scopes_pending_acknowledgments(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Scope Policy',
            'category' => 'other',
            'created_by' => $this->user->id,
        ]);

        PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        // Create a second employee for the acknowledged record
        $user2 = User::factory()->create(['email' => 'test2@example.com']);
        $employee2 = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user2->id,
            'email' => 'test2@example.com',
        ]);

        PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $employee2->id,
            'status' => 'acknowledged',
        ]);

        $pending = PolicyAcknowledgment::pending()->get();
        $this->assertCount(1, $pending);
        $this->assertEquals('pending', $pending->first()->status);
    }

    // =========================================================================
    // PolicyDistributionService Tests
    // =========================================================================

    #[Test]
    public function it_applies_null_scope_to_all_employees(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Everyone Policy',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => null,
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertTrue($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_applies_empty_scope_to_all_employees(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Empty Scope Policy',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => [],
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertTrue($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_matches_department_scope(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Engineering Only',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['departments' => [$this->department->id]],
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertTrue($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_rejects_non_matching_department_scope(): void
    {
        $otherDepartment = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Marketing',
        ]);

        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Marketing Only',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['departments' => [$otherDepartment->id]],
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertFalse($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_matches_role_scope(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Employee Role Only',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['roles' => ['employee']],
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertTrue($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_rejects_non_matching_role_scope(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Manager Only',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['roles' => ['manager']],
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertFalse($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_rejects_policy_from_different_company(): void
    {
        $otherCompany = HrCompany::factory()->create(['name' => 'Other Corp', 'is_active' => true]);

        $policy = CompanyPolicy::create([
            'company_id' => $otherCompany->id,
            'title' => 'Other Company Policy',
            'category' => 'other',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertFalse($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_rejects_non_published_policies(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Draft Policy',
            'category' => 'other',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertFalse($service->policyAppliesToEmployee($policy, $this->employee));
    }

    #[Test]
    public function it_returns_policies_for_employee(): void
    {
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Policy A',
            'category' => 'other',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Policy B',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['departments' => [$this->department->id]],
            'created_by' => $this->user->id,
        ]);

        // This one should NOT be returned (different department)
        $otherDept = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'HR',
        ]);
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Policy C (HR Only)',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => ['departments' => [$otherDept->id]],
            'created_by' => $this->user->id,
        ]);

        // Draft should not be returned
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Draft Policy',
            'category' => 'other',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $policies = $service->getPoliciesForEmployee($this->employee);

        $this->assertCount(2, $policies);
        $titles = $policies->pluck('title')->toArray();
        $this->assertContains('Policy A', $titles);
        $this->assertContains('Policy B', $titles);
        $this->assertNotContains('Policy C (HR Only)', $titles);
        $this->assertNotContains('Draft Policy', $titles);
    }

    #[Test]
    public function it_handles_employee_without_position(): void
    {
        $employeeNoPosition = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => User::factory()->create(['email' => 'nopos@example.com'])->id,
            'email' => 'nopos@example.com',
        ]);

        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'All Employees',
            'category' => 'other',
            'status' => 'published',
            'distribution_scope' => null,
            'created_by' => $this->user->id,
        ]);

        $service = app(PolicyDistributionService::class);
        $this->assertTrue($service->policyAppliesToEmployee($policy, $employeeNoPosition));
    }

    // =========================================================================
    // PoliciesAcknowledged Condition Tests
    // =========================================================================

    #[Test]
    public function it_returns_true_when_no_mandatory_policies_exist(): void
    {
        $condition = new \App\Modules\Policies\Conditions\PoliciesAcknowledged();
        $result = $condition($this->user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_false_when_mandatory_policies_are_unacknowledged(): void
    {
        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Mandatory Policy',
            'category' => 'other',
            'status' => 'published',
            'requires_acknowledgment' => true,
            'created_by' => $this->user->id,
        ]);

        $condition = new \App\Modules\Policies\Conditions\PoliciesAcknowledged();
        $result = $condition($this->user);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_true_when_all_mandatory_policies_are_acknowledged(): void
    {
        $policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'title' => 'Acknowledged Policy',
            'category' => 'other',
            'status' => 'published',
            'requires_acknowledgment' => true,
            'created_by' => $this->user->id,
        ]);

        PolicyAcknowledgment::create([
            'company_policy_id' => $policy->id,
            'employee_id' => $this->employee->id,
            'status' => 'acknowledged',
        ]);

        $condition = new \App\Modules\Policies\Conditions\PoliciesAcknowledged();
        $result = $condition($this->user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_true_when_employee_does_not_exist(): void
    {
        $userWithoutEmployee = User::factory()->create(['email' => 'noemp@example.com']);

        $condition = new \App\Modules\Policies\Conditions\PoliciesAcknowledged();
        $result = $condition($userWithoutEmployee);

        $this->assertTrue($result);
    }
}
