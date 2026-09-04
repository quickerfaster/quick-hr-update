<?php

use App\Modules\Attendance\Providers\AttendanceServiceProvider;
use App\Modules\Holiday\Providers\HolidayServiceProvider;
use App\Modules\Hr\Providers\HrsServiceProvider;
use App\Modules\Leave\Providers\LeaveServiceProvider;
use App\Modules\Payroll\Providers\PayrollServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AttendanceServiceProvider::class,
    HolidayServiceProvider::class,
    HrsServiceProvider::class,
    LeaveServiceProvider::class,
    PayrollServiceProvider::class,
];
