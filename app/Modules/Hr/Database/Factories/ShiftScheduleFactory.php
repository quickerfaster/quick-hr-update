<?php
namespace App\Modules\Hr\Database\Factories;

use App\Modules\Hr\Models\ShiftSchedule;
use App\Modules\Hr\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftScheduleFactory extends Factory
{
    protected $model = ShiftSchedule::class;

    public function definition()
    {
        return [
            'start_time_override' => '08:00:00',
            'end_time_override' => '17:00:00',
            'status' => 'scheduled',

            'schedule_type' => 'regular',
            'is_published' => true,
            'company_id' => null,

            // actual_start_time'
            // 'actual_end_time'
        ];

    }

    /**
     * Attach a specific company.
     */
    public function forCompany($company)
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company instanceof Company ? $company->id : $company,
        ]);
    }
}
