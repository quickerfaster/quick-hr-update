<?php

namespace App\Modules\Hr\Database\Factories;

use App\Modules\Hr\Models\JobTitle;
use App\Modules\Hr\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobTitleFactory extends Factory
{
    protected $model = JobTitle::class;

    public function definition()
    {
        return [
            'title' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'company_id' => null,
            // 'editable' => $this->faker->randomElement(['Yes', 'No']),
            'created_at' => now(),
            'updated_at' => now(),
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

    /**
     * Indicate that the job title is editable.
     */
    public function editable()
    {
        return $this->state(fn (array $attributes) => [
            'editable' => 'Yes',
        ]);
    }

    /**
     * Indicate that the job title is non-editable (system-protected).
     */
    public function nonEditable()
    {
        return $this->state(fn (array $attributes) => [
            'editable' => 'No',
        ]);
    }

    /**
     * Create a specific job title.
     */
    public function withTitle(string $title)
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
        ]);
    }
}
