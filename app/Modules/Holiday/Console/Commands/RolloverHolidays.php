<?php

namespace App\Modules\Holiday\Console\Commands;

use App\Modules\Holiday\Services\HolidayYearRollover;
use Illuminate\Console\Command;

/**
 * Automatically roll over recurring holidays to the next year.
 *
 * Scheduled to run annually on December 1st. Can also be run manually:
 *   php artisan holiday:rollover
 *   php artisan holiday:rollover --year=2026 --target-year=2027
 */
class RolloverHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:rollover
                            {--year= : Source year (default: current year)}
                            {--target-year= : Target year (default: current year + 1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll over recurring holidays from one year to the next';

    /**
     * Execute the console command.
     */
    public function handle(HolidayYearRollover $rollover): int
    {
        $sourceYear = $this->option('year')
            ? (int) $this->option('year')
            : (int) now()->year;

        $targetYear = $this->option('target-year')
            ? (int) $this->option('target-year')
            : $sourceYear + 1;

        $this->info("Rolling over recurring holidays from {$sourceYear} to {$targetYear}...");

        $result = $rollover->rollover($sourceYear, $targetYear);

        $this->info("Calendars processed: {$result['calendars_processed']}");
        $this->info("Holidays copied:    {$result['holidays_copied']}");
        $this->info("Holidays skipped:   {$result['holidays_skipped']}");

        if (!empty($result['errors'])) {
            $this->error('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->error("  - Calendar '{$error['calendar_name']}' (#{$error['calendar_id']}): {$error['error']}");
            }
            return self::FAILURE;
        }

        $this->info('Holiday year rollover completed successfully.');

        return self::SUCCESS;
    }
}
