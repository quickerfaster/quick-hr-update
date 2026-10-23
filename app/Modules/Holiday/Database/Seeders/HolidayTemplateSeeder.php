<?php

namespace App\Modules\Holiday\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Holiday\Models\HolidayTemplate;

class HolidayTemplateSeeder extends Seeder
{
    /**
     * Curated holiday templates per country.
     *
     * Fixed-date holidays use month + day.
     * Floating holidays use recurrence_rule JSON (same format as
     * the RecurrenceRuleBuilder produces).
     */
    protected array $templates = [
        // =============================================================
        // United States
        // =============================================================
        ['country_code' => 'US', 'name' => "New Year's Day",           'month' => 1,  'day' => 1,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Martin Luther King Jr. Day', 'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"third","floating_day":"monday","floating_month":1}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => "Presidents' Day",          'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"third","floating_day":"monday","floating_month":2}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Memorial Day',             'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"last","floating_day":"monday","floating_month":5}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Juneteenth',               'month' => 6,  'day' => 19, 'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Independence Day',         'month' => 7,  'day' => 4,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Labor Day',                'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"monday","floating_month":9}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Columbus Day',             'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"second","floating_day":"monday","floating_month":10}', 'holiday_type' => 'public', 'default_pay_rate' => 1.5],
        ['country_code' => 'US', 'name' => 'Veterans Day',             'month' => 11, 'day' => 11, 'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'US', 'name' => 'Thanksgiving Day',         'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"fourth","floating_day":"thursday","floating_month":11}', 'holiday_type' => 'public', 'default_pay_rate' => 2.5],
        ['country_code' => 'US', 'name' => 'Christmas Day',            'month' => 12, 'day' => 25, 'holiday_type' => 'public',     'default_pay_rate' => 2.5],

        // =============================================================
        // United Kingdom
        // =============================================================
        ['country_code' => 'GB', 'name' => "New Year's Day",           'month' => 1,  'day' => 1,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'GB', 'name' => 'Good Friday',              'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"friday","floating_month":4}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0, 'is_paid' => true],
        ['country_code' => 'GB', 'name' => 'Easter Monday',            'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"monday","floating_month":4}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0, 'is_paid' => true],
        ['country_code' => 'GB', 'name' => 'Early May Bank Holiday',   'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"monday","floating_month":5}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'GB', 'name' => 'Spring Bank Holiday',      'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"last","floating_day":"monday","floating_month":5}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'GB', 'name' => 'Summer Bank Holiday',      'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"last","floating_day":"monday","floating_month":8}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'GB', 'name' => 'Christmas Day',            'month' => 12, 'day' => 25, 'holiday_type' => 'public',     'default_pay_rate' => 2.5],
        ['country_code' => 'GB', 'name' => 'Boxing Day',               'month' => 12, 'day' => 26, 'holiday_type' => 'public',     'default_pay_rate' => 2.0],

        // =============================================================
        // Nigeria
        // =============================================================
        ['country_code' => 'NG', 'name' => "New Year's Day",           'month' => 1,  'day' => 1,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => 'Good Friday',              'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"friday","floating_month":4}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => 'Easter Monday',            'recurrence_rule' => '{"frequency":"yearly","yearly_mode":"floating","floating_week":"first","floating_day":"monday","floating_month":4}', 'holiday_type' => 'public', 'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => "Workers' Day",             'month' => 5,  'day' => 1,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => 'Democracy Day',            'month' => 6,  'day' => 12, 'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => 'Independence Day',         'month' => 10, 'day' => 1,  'holiday_type' => 'public',     'default_pay_rate' => 2.0],
        ['country_code' => 'NG', 'name' => 'Christmas Day',            'month' => 12, 'day' => 25, 'holiday_type' => 'public',     'default_pay_rate' => 2.5],
        ['country_code' => 'NG', 'name' => 'Boxing Day',               'month' => 12, 'day' => 26, 'holiday_type' => 'public',     'default_pay_rate' => 2.0],
    ];

    public function run(): void
    {
        foreach ($this->templates as $template) {
            HolidayTemplate::firstOrCreate(
                [
                    'country_code' => $template['country_code'],
                    'name'         => $template['name'],
                ],
                $template
            );
        }
    }
}
