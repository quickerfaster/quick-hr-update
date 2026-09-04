<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleDashboardAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Let the 'auth' middleware handle unauthenticated users
        if (!$user) {
            return $next($request);
        }

        // super_admin and admin can access everything — skip all checks
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $next($request);
        }

        $path = $request->path();

        // /home is accessible to any authenticated user,
        // but ESS-only users get redirected to their portal
        if ($path === 'home') {
            if ($user->hasRole('employee') && !$user->hasAnyRole(['manager', 'hr_manager', 'payroll_officer', 'company_admin'])) {
                return redirect('/hr/my-portal')
                    ->with('warning', 'Access restricted. Redirected to your portal.');
            }

            return $next($request);
        }

        // Read from config with fallback to hardcoded defaults (defensive coding)
        $moduleRoleMap = config('ui-library.module_access', []) ?: [
            'hr/my-'       => ['employee', 'manager'],
            'hr'           => ['hr_manager', 'admin', 'super_admin'],
            'leave'        => ['hr_manager', 'admin', 'super_admin'],
            'holiday'      => ['hr_manager', 'admin', 'super_admin'],
            'attendance'   => ['hr_manager', 'admin', 'super_admin'],
            'payroll'      => ['payroll_officer', 'admin', 'super_admin'],
            'organization' => ['admin', 'super_admin'],
            'system'       => ['admin', 'super_admin'],
            'admin'        => ['admin', 'super_admin'],
        ];

        // Check module prefix → role mappings
        foreach ($moduleRoleMap as $prefix => $allowedRoles) {
            if (str_starts_with($path, $prefix)) {
                if (!$user->hasAnyRole($allowedRoles)) {
                    return $this->redirectToAppropriateDashboard($user);
                }

                // Matched a prefix and user is authorized — stop checking
                return $next($request);
            }
        }

        // Route doesn't match any known prefix — allow through
        return $next($request);
    }

    /**
     * Redirect the user to their role-appropriate landing page.
     */
    protected function redirectToAppropriateDashboard($user)
    {
        if ($user->hasRole('employee')) {
            return redirect('/hr/my-portal')
                ->with('warning', 'Access restricted. Redirected to your portal.');
        }

        if ($user->hasRole('payroll_officer')) {
            return redirect('/payroll/dashboard-processing-overview')
                ->with('warning', 'Access restricted.');
        }

        if ($user->hasRole('hr_manager')) {
            return redirect('/hr/dashboard-people-overview')
                ->with('warning', 'Access restricted.');
        }

        return redirect('/home')
            ->with('warning', 'Access restricted.');
    }
}