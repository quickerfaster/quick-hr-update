<?php

use App\Modules\Hr\Providers\HrsServiceProvider;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    HrsServiceProvider::class,
    OrganizationServiceProvider::class,
];
