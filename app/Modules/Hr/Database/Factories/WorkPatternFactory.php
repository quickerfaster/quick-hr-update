<?php
namespace App\Modules\Hr\Database\Factories;

use App\Modules\Hr\Models\WorkPattern;
use App\Modules\Hr\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkPatternFactory extends Factory
{
    protected $model = WorkPattern::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => 'WOR-' . $this->faker->unique()->numberBetween(1000, 9999),
            'shift_id' => 1, // override in test
            'applicable_days' => "1,2,3,4,5",
            'pattern_type' => 'recurring',
            'effective_date' => now()->startOfYear(),
            'company_id' => null,
            'is_active' => true,
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