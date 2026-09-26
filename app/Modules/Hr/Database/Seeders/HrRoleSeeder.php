<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * HrRoleSeeder — seeds all HR domain roles and assigns a complete,
 * consistent set of permissions aligned with the UI library's
 * {action}_{resource} convention.
 *
 * Roles created:
 *   - super_admin     (bypasses all checks — created by library RoleSeeder)
 *   - company_admin   (bypasses all checks — created by library RoleSeeder)
 *   - hr_manager      (full HR module access + manage employees)
 *   - hr_officer      (operational HR — view/manage employees, attendance, leave)
 *   - payroll_officer (payroll processing + payslip management)
 *   - accountant      (view-only financial data + reports)
 *   - manager         (team management — view team data, approve leave)
 *   - supervisor      (team oversight — view team attendance, approve leave)
 *   - recruiter       (onboarding + invitations + candidate management)
 *   - employee        (self-service — view own data, request leave, clock in/out)
 *
 * Permission categories follow the library's auto-generated CRUD pattern:
 *   view_{resource}, create_{resource}, edit_{resource}, delete_{resource},
 *   print_{resource}, export_{resource}, import_{resource}
 *
 * Plus domain-specific extras declared in each module's Config/permissions.php.
 */
class HrRoleSeeder extends Seeder
{
    /**
     * All HR roles in the system.
     */
    protected array $roles = [
        'super_admin',
        'company_admin',
        'hr_manager',
        'hr_officer',
        'payroll_officer',
        'accountant',
        'manager',
        'supervisor',
        'recruiter',
        'employee',
    ];

    /**
     * Permission assignments per role.
     *
     * Keys are role names. Values are arrays of permission name strings.
     * The 'super_admin' and 'company_admin' roles are created by the
     * library RoleSeeder with all permissions — we only ensure they exist.
     *
     * Pattern: {action}_{resource} where resource is the snake_case model name.
     */
    protected array $rolePermissions = [

        // ─── hr_manager — Full HR module access ─────────────────────
        'hr_manager' => [
            // HR Module — Employee Management
            'view_employee', 'create_employee', 'edit_employee', 'delete_employee',
            'print_employee', 'export_employee', 'import_employee',
            'view_employee_profile', 'create_employee_profile', 'edit_employee_profile', 'delete_employee_profile',
            'view_employee_position', 'create_employee_position', 'edit_employee_position', 'delete_employee_position',
            'view_employee_job_history', 'create_employee_job_history', 'edit_employee_job_history', 'delete_employee_job_history',
            'view_employee_group', 'create_employee_group', 'edit_employee_group', 'delete_employee_group',
            'view_job_title', 'create_job_title', 'edit_job_title', 'delete_job_title',
            'view_tag', 'create_tag', 'edit_tag', 'delete_tag',
            'view_team', 'create_team', 'edit_team', 'delete_team',
            'view_document', 'create_document', 'edit_document', 'delete_document',

            // HR Module — Organization
            'view_company', 'create_company', 'edit_company',
            'view_department', 'create_department', 'edit_department', 'delete_department',
            'view_location', 'create_location', 'edit_location', 'delete_location',

            // HR Module — Onboarding & Invitations
            'view_invitation', 'create_invitation', 'edit_invitation', 'delete_invitation',
            'manage_user_company_assignments',

            // HR Module — Dashboards
            'view_my_portal', 'view_leave_hub', 'view_team_calendar',
            'view_people_overview', 'view_manage_overview', 'view_organization_overview',

            // Attendance Module
            'view_attendance', 'create_attendance', 'edit_attendance', 'delete_attendance',
            'export_attendance', 'print_attendance',
            'view_clock_event', 'create_clock_event', 'edit_clock_event',
            'view_attendance_session', 'edit_attendance_session',
            'view_attendance_adjustment', 'create_attendance_adjustment', 'edit_attendance_adjustment',
            'view_attendance_policy', 'create_attendance_policy', 'edit_attendance_policy', 'delete_attendance_policy',
            'view_shift', 'create_shift', 'edit_shift', 'delete_shift',
            'view_shift_schedule', 'create_shift_schedule', 'edit_shift_schedule', 'delete_shift_schedule',
            'view_work_pattern', 'create_work_pattern', 'edit_work_pattern', 'delete_work_pattern',
            'view_employee_work_pattern', 'create_employee_work_pattern', 'edit_employee_work_pattern',
            'view_policy_assignment', 'create_policy_assignment', 'edit_policy_assignment',
            'recalculate_attendance',
            'view_attendance_overview', 'view_scheduling_overview', 'view_policy_overview',

            // Leave Module
            'view_leave_request', 'create_leave_request', 'edit_leave_request', 'delete_leave_request',
            'view_leave_type', 'create_leave_type', 'edit_leave_type', 'delete_leave_type',
            'view_leave_balance', 'create_leave_balance', 'edit_leave_balance',
            'view_leave_approver', 'create_leave_approver', 'edit_leave_approver', 'delete_leave_approver',
            'approve_leave_request', 'cancel_leave_request',

            // Holiday Module
            'view_holiday', 'create_holiday', 'edit_holiday', 'delete_holiday',
            'view_holiday_calendar', 'create_holiday_calendar', 'edit_holiday_calendar', 'delete_holiday_calendar',
            'create_holiday_batch',
            'view_holiday_overview',

            // Payroll Module — oversight only (processing is payroll_officer)
            'view_payroll_run', 'view_payroll_payslip', 'view_payroll_policy',
            'view_pay_schedule', 'view_employee_payroll_profile',
            'view_payslip_item', 'view_payroll_run_adjustment', 'view_employee_adjustment_profile',
            'view_payroll_policy_assignment',
            'view_processing_overview', 'view_configuration_overview',

            // Organization Module
            'view_branch', 'create_branch', 'edit_branch',
            'view_division', 'create_division', 'edit_division',
            'view_business_unit', 'create_business_unit', 'edit_business_unit',
            'view_organization_chart',
            'view_dashboard_overview', 'view_companies_overview', 'view_structure_overview',
            'view_teams_overview', 'view_locations_overview', 'view_classification_overview',
            'view_reports_overview', 'view_company_reports', 'view_department_reports',
            'view_location_reports', 'view_growth_reports',
            'view_organization_summary', 'view_growth', 'view_recent_changes',

            // Cross-cutting
            'view_all_companies',
        ],

        // ─── hr_officer — Operational HR ────────────────────────────
        'hr_officer' => [
            // HR Module — Employee Management (view + create/edit, no delete)
            'view_employee', 'create_employee', 'edit_employee',
            'print_employee', 'export_employee',
            'view_employee_profile', 'create_employee_profile', 'edit_employee_profile',
            'view_employee_position', 'create_employee_position', 'edit_employee_position',
            'view_employee_job_history', 'create_employee_job_history', 'edit_employee_job_history',
            'view_employee_group',
            'view_job_title', 'create_job_title', 'edit_job_title',
            'view_tag', 'create_tag', 'edit_tag',
            'view_team',
            'view_document', 'create_document',

            // HR Module — Organization (view only)
            'view_company', 'view_department', 'view_location',

            // HR Module — Onboarding
            'view_invitation', 'create_invitation', 'edit_invitation',

            // HR Module — Dashboards
            'view_my_portal', 'view_leave_hub', 'view_team_calendar',
            'view_people_overview', 'view_manage_overview', 'view_organization_overview',

            // Attendance Module (view + basic management)
            'view_attendance', 'create_attendance', 'edit_attendance',
            'export_attendance',
            'view_clock_event', 'create_clock_event',
            'view_attendance_session',
            'view_attendance_adjustment', 'create_attendance_adjustment',
            'view_attendance_policy',
            'view_shift', 'view_shift_schedule',
            'view_work_pattern', 'view_employee_work_pattern',
            'view_policy_assignment',
            'view_attendance_overview', 'view_scheduling_overview', 'view_policy_overview',

            // Leave Module (view + manage)
            'view_leave_request', 'create_leave_request', 'edit_leave_request',
            'view_leave_type',
            'view_leave_balance', 'create_leave_balance', 'edit_leave_balance',
            'view_leave_approver',
            'approve_leave_request',

            // Holiday Module (view only)
            'view_holiday', 'view_holiday_calendar',
            'view_holiday_overview',

            // Payroll Module (view only)
            'view_payroll_run', 'view_payroll_payslip',
            'view_pay_schedule', 'view_employee_payroll_profile',
            'view_processing_overview',

            // Organization Module (view only)
            'view_branch', 'view_division', 'view_business_unit',
            'view_organization_chart',
            'view_dashboard_overview', 'view_companies_overview', 'view_structure_overview',
            'view_teams_overview', 'view_locations_overview',
            'view_organization_summary',
        ],

        // ─── payroll_officer — Payroll processing ───────────────────
        'payroll_officer' => [
            // Payroll Module — Full access
            'view_payroll_run', 'create_payroll_run', 'edit_payroll_run', 'delete_payroll_run',
            'export_payroll_run', 'print_payroll_run',
            'view_payroll_payslip', 'create_payroll_payslip', 'edit_payroll_payslip',
            'export_payslip', 'print_payslip',
            'view_payroll_policy', 'create_payroll_policy', 'edit_payroll_policy', 'delete_payroll_policy',
            'view_pay_schedule', 'create_pay_schedule', 'edit_pay_schedule', 'delete_pay_schedule',
            'view_employee_payroll_profile', 'create_employee_payroll_profile', 'edit_employee_payroll_profile',
            'view_payslip_item', 'create_payslip_item', 'edit_payslip_item', 'delete_payslip_item',
            'view_payroll_run_adjustment', 'create_payroll_run_adjustment', 'edit_payroll_run_adjustment',
            'view_employee_adjustment_profile', 'create_employee_adjustment_profile', 'edit_employee_adjustment_profile',
            'view_payroll_policy_assignment', 'create_payroll_policy_assignment', 'edit_payroll_policy_assignment',
            'process_payroll_run', 'approve_payroll_run',
            'view_processing_overview', 'view_configuration_overview',

            // HR Module — View employees (needed for payroll)
            'view_employee', 'view_employee_profile', 'view_employee_position',
            'view_department', 'view_location', 'view_company',

            // Attendance Module — View (needed for hourly payroll)
            'view_attendance', 'view_clock_event',
            'view_attendance_overview',

            // Leave Module — View (needed for leave deductions)
            'view_leave_request', 'view_leave_type', 'view_leave_balance',
        ],

        // ─── accountant — Financial oversight ───────────────────────
        'accountant' => [
            // Payroll Module — View only
            'view_payroll_run', 'view_payroll_payslip',
            'export_payroll_run', 'export_payslip', 'print_payslip',
            'view_payroll_policy', 'view_pay_schedule',
            'view_employee_payroll_profile', 'view_payslip_item',
            'view_payroll_run_adjustment', 'view_employee_adjustment_profile',
            'view_payroll_policy_assignment',
            'view_processing_overview', 'view_configuration_overview',

            // HR Module — View employees
            'view_employee', 'view_employee_profile', 'view_employee_position',
            'view_department', 'view_company',

            // Attendance Module — View
            'view_attendance', 'view_attendance_overview',

            // Organization Module — View
            'view_dashboard_overview', 'view_companies_overview',
            'view_company_reports', 'view_department_reports', 'view_location_reports',
        ],

        // ─── manager — Team management ──────────────────────────────
        'manager' => [
            // HR Module — View team
            'view_employee', 'view_employee_profile', 'view_employee_position',
            'view_employee_job_history',
            'view_team', 'view_employee_group',
            'view_department', 'view_location',
            'view_my_portal', 'view_leave_hub', 'view_team_calendar',
            'view_people_overview',

            // Attendance Module — View team attendance
            'view_attendance', 'view_clock_event', 'view_attendance_session',
            'view_shift', 'view_shift_schedule', 'view_work_pattern',
            'view_employee_work_pattern',
            'view_attendance_overview', 'view_scheduling_overview',

            // Leave Module — Approve team leave
            'view_leave_request', 'create_leave_request',
            'view_leave_type', 'view_leave_balance',
            'approve_leave_request',

            // Holiday Module — View
            'view_holiday', 'view_holiday_calendar', 'view_holiday_overview',

            // Payroll Module — View team payslips
            'view_payroll_payslip',
        ],

        // ─── supervisor — Team oversight ────────────────────────────
        'supervisor' => [
            // HR Module — View team
            'view_employee', 'view_employee_profile', 'view_employee_position',
            'view_team',
            'view_my_portal', 'view_leave_hub', 'view_team_calendar',
            'view_people_overview',

            // Attendance Module — View team attendance
            'view_attendance', 'view_clock_event',
            'view_shift', 'view_work_pattern',
            'view_attendance_overview',

            // Leave Module — Approve team leave
            'view_leave_request', 'create_leave_request',
            'view_leave_type', 'view_leave_balance',
            'approve_leave_request',

            // Holiday Module — View
            'view_holiday', 'view_holiday_calendar',
        ],

        // ─── recruiter — Onboarding & hiring ────────────────────────
        'recruiter' => [
            // HR Module — Onboarding
            'view_invitation', 'create_invitation', 'edit_invitation', 'delete_invitation',
            'view_employee', 'create_employee', 'edit_employee',
            'view_employee_profile', 'create_employee_profile',
            'view_employee_position', 'create_employee_position',
            'view_job_title', 'view_department', 'view_location', 'view_company',
            'view_document', 'create_document',
            'view_my_portal', 'view_people_overview', 'view_manage_overview',
            'view_organization_overview',

            // Organization Module — View
            'view_dashboard_overview', 'view_companies_overview', 'view_structure_overview',
        ],

        // ─── employee — Self-service ────────────────────────────────
        'employee' => [
            // HR Module — Self-service
            'view_my_portal', 'view_leave_hub', 'view_team_calendar',

            // Attendance Module — Clock in/out + view own records
            'clock_in', 'clock_out',
            'view_attendance_overview',

            // Leave Module — Request leave + view own
            'view_leave_request', 'create_leave_request',
            'view_leave_type', 'view_leave_balance',

            // Holiday Module — View
            'view_holiday', 'view_holiday_calendar',

            // Payroll Module — View own payslips
            'view_payroll_payslip',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure all roles exist (idempotent)
        foreach ($this->roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. Ensure all referenced permissions exist before assigning.
        //    AccessControlPermissionService::seedPermissionNames() should
        //    have already run (called by DatabaseSeeder before this seeder).
        $this->ensurePermissionsExist();

        // 3. Assign permissions to each role.
        //    super_admin and company_admin already have all permissions
        //    from the library RoleSeeder — we skip them here to avoid
        //    accidentally narrowing their access.
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');

            // Only assign if the role doesn't already have all permissions
            // (protects super_admin / company_admin from being narrowed)
            if (in_array($roleName, ['super_admin', 'company_admin'], true)) {
                continue;
            }

            $role->syncPermissions($permissions);
        }
    }

    /**
     * Ensure every permission referenced in rolePermissions exists
     * in the database before we try to assign it.
     */
    protected function ensurePermissionsExist(): void
    {
        $allPermissions = [];

        foreach ($this->rolePermissions as $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[$permission] = true;
            }
        }

        foreach (array_keys($allPermissions) as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
    }
}
