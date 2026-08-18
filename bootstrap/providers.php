<?php

use App\Modules\Attendance\Providers\AttendanceServiceProvider;
use App\Modules\Hr\Providers\HrsServiceProvider;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AttendanceServiceProvider::class,
    HrsServiceProvider::class,
    OrganizationServiceProvider::class,
];
