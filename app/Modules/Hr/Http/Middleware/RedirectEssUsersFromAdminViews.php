<?php

namespace App\Modules\Hr\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectEssUsersFromAdminViews
{
    /**
     * Admin URL patterns that ESS-only users should not access directly.
     * These are the original admin views that now have ESS wrapper equivalents.
     */
    protected array $adminPatterns = [
        'attendance/attendances',
        'attendance/clock-events',
        'leave/leave-requests',
        'hr/documents',
        'admin/activity-logs',
    ];

    /**
     * Mapping from admin URL patterns to their ESS wrapper equivalents.
     */
    protected array $essRedirectMap = [
        'attendance/attendances' => '/hr/my-attendance',
        'attendance/clock-events' => '/hr/my-clock-events',
        'leave/leave-requests' => '/hr/my-leave-requests',
        'hr/documents' => '/hr/my-documents-view',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Only apply to users who have the 'employee' role but NOT admin/super_admin roles
        if (!$user->hasRole('employee') || $user->hasRole('admin') || $user->hasRole('super_admin')) {
            return $next($request);
        }

        $path = $request->path();

        foreach ($this->adminPatterns as $pattern) {
            if (str_starts_with($path, $pattern)) {
                $essRedirect = $this->essRedirectMap[$pattern] ?? null;

                if ($essRedirect) {
                    return redirect($essRedirect)
                        ->with('warning', 'You are viewing your own records only.');
                }

                // For patterns without an ESS equivalent (e.g., admin/activity-logs),
                // redirect to the My Portal dashboard
                return redirect('/hr/my-portal')
                    ->with('warning', 'This section is not available for your account type.');
            }
        }

        return $next($request);
    }
}