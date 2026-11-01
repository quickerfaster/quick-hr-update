<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });

        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();

                    return match (true) {
                        // ESS employees → My Portal
                        $user->hasRole('employee') && !$user->hasAnyRole(['super_admin', 'admin', 'company_admin'])
                            => redirect()->intended('/hr/my-portal'),

                        // Admins → Admin dashboard (user/role/permission management)
                        $user->hasAnyRole(['super_admin', 'admin', 'company_admin'])
                            => redirect()->intended('/admin/dashboard'),

                        // Payroll officers → Payroll dashboard (full config + processing view)
                        // Phase 1-3: payroll module is disabled; falls through to default
                        $user->hasRole('payroll_officer') && config('ui-library.modules.payroll.enabled', false)
                            => redirect()->intended('/payroll/dashboard'),

                        // HR managers → HR dashboard (cross-module workforce summary)
                        $user->hasRole('hr_manager')
                            => redirect()->intended('/hr/dashboard'),

                        // Everyone else → My Portal (universal landing page with full navigation)
                        default
                            => redirect()->intended('/hr/my-portal'),
                    };
                }
            };
        });
    }
}
