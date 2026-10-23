<?php

namespace App\Modules\Holiday\Services;

use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Models\HolidayBatchDraft;
use App\Modules\Holiday\Http\Livewire\HolidayBatchDefaults;
use App\Modules\Holiday\Http\Livewire\HolidayBatchStepOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bulk-creates Holiday records from the batch creation wizard's
 * session data on wizard completion.
 *
 * Called by the HolidayBatchPreview Livewire component when the
 * user clicks "Complete Setup" on the review step.
 */
class HolidayBatchSubmissionService
{
    /**
     * Create Holiday records from the wizard's selected templates
     * and dates.
     *
     * @param string $wizardId    The wizard's unique session ID
     * @param array  $presetData  Step 1 data: calendar_id, target_year, source_option
     * @param array  $previewItems The final preview items (after user edits)
     * @return array{copied: int, errors: array}
     */
    public function submit(string $wizardId, array $presetData, array $previewItems): array
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($wizardId);
        $calendarId = $stepOne['calendarId'] ?? $presetData['calendar_id'] ?? null;
        $targetYear = (int) ($stepOne['targetYear'] ?? $presetData['target_year'] ?? now()->year);
        $companyId  = $this->resolveCompanyId($calendarId);

        // Load batch-level defaults (if user enabled them)
        $batchDefaults = HolidayBatchDefaults::getDefaults($wizardId);

        if (empty($previewItems)) {
            Log::warning('Holiday batch submission: no items to create.', [
                'wizardId' => $wizardId,
            ]);
            return ['copied' => 0, 'errors' => []];
        }

        if (!$calendarId) {
            return ['copied' => 0, 'errors' => ['No calendar selected.']];
        }

        $copied = 0;
        $errors = [];

        DB::transaction(function () use ($previewItems, $calendarId, $targetYear, $companyId, $batchDefaults, &$copied, &$errors) {
            foreach ($previewItems as $item) {
                try {
                    $date = $item['date'] ?? null;
                    if (empty($date)) {
                        $errors[] = "Skipped '{$item['name']}': no date.";
                        continue;
                    }

                    // Idempotency: skip if a holiday with the same name+date+calendar already exists
                    $exists = Holiday::where('calendar_id', $calendarId)
                        ->where('date', $date)
                        ->where('name', $item['name'])
                        ->where('year', $targetYear)
                        ->exists();

                    if ($exists) {
                        Log::info("Holiday batch: skipping duplicate '{$item['name']}' on {$date}");
                        continue;
                    }

                    // Merge item values with batch defaults (item takes precedence)
                    $payRate        = (float) ($item['pay_rate'] ?? $batchDefaults['defaultPayRate'] ?? 1.0);
                    $businessImpact = $item['business_impact'] ?? $batchDefaults['defaultBusinessImpact'] ?? $this->defaultBusinessImpact($item['type'] ?? 'company');
                    $isPaid         = (bool) ($item['is_paid'] ?? $batchDefaults['defaultIsPaid'] ?? true);
                    $isRecurring    = (bool) ($item['is_recurring'] ?? $batchDefaults['defaultIsRecurring'] ?? true);
                    $eligibleTypes  = $item['eligible_employee_types'] ?? $batchDefaults['defaultEligibleEmployeeTypes'] ?? ['full_time', 'part_time'];

                    Holiday::create([
                        'company_id'              => $companyId,
                        'calendar_id'             => $calendarId,
                        'name'                    => $item['name'],
                        'description'             => $item['description'] ?? null,
                        'date'                    => $date,
                        'observed_date'           => null,
                        'is_recurring'            => $isRecurring,
                        'recurrence_pattern'      => $isRecurring ? 'yearly_fixed' : 'none',
                        'holiday_type'            => $item['type'] ?? 'company',
                        'is_paid_holiday'         => $isPaid,
                        'affects_payroll'         => true,
                        'business_impact'         => $businessImpact,
                        'eligible_employee_types' => is_array($eligibleTypes) ? json_encode($eligibleTypes) : $eligibleTypes,
                        'holiday_pay_rate'        => $payRate,
                        'minimum_hours_for_pay'   => 8,
                        'country_code'            => $this->resolveCountryCode($calendarId),
                        'is_half_day'             => false,
                        'is_active'               => true,
                        'year'                    => $targetYear,
                        'generated_from_template' => true,
                    ]);

                    $copied++;

                    Log::info("Holiday batch: created '{$item['name']}' for {$date}", [
                        'calendar_id' => $calendarId,
                        'year'        => $targetYear,
                    ]);
                } catch (\Throwable $e) {
                    $errors[] = "Failed to create '{$item['name']}': {$e->getMessage()}";
                    Log::error("Holiday batch: failed to create '{$item['name']}'", [
                        'error' => $e->getMessage(),
                        'item'  => $item,
                    ]);
                }
            }
        });

        // Update the calendar's holiday_count
        if ($calendarId && $copied > 0) {
            $count = Holiday::where('calendar_id', $calendarId)
                ->where('year', $targetYear)
                ->where('is_active', true)
                ->count();

            HolidayCalendar::where('id', $calendarId)->update([
                'holiday_count' => $count,
                'last_updated'  => now(),
            ]);
        }

        Log::info("Holiday batch submission complete", [
            'wizardId' => $wizardId,
            'calendar_id' => $calendarId,
            'target_year' => $targetYear,
            'copied'      => $copied,
            'errors'      => count($errors),
        ]);

        // Clean up the draft after successful submission
        if ($copied > 0) {
            HolidayBatchDraft::where('wizard_id', $wizardId)->delete();
        }

        return [
            'copied' => $copied,
            'errors' => $errors,
        ];
    }

    /**
     * Resolve the company_id from the calendar.
     */
    protected function resolveCompanyId(?int $calendarId): ?int
    {
        if (!$calendarId) {
            return session('current_company_id');
        }

        $calendar = HolidayCalendar::find($calendarId);
        return $calendar?->company_id ?? session('current_company_id');
    }

    /**
     * Resolve the country_code from the calendar.
     */
    protected function resolveCountryCode(?int $calendarId): ?string
    {
        if (!$calendarId) {
            return null;
        }

        $calendar = HolidayCalendar::find($calendarId);
        return $calendar?->country_code;
    }

    /**
     * Smart default for business_impact based on holiday type.
     */
    protected function defaultBusinessImpact(string $type): string
    {
        return match ($type) {
            'public'     => 'office_closed',
            'company'    => 'reduced_staff',
            'regional'   => 'office_closed',
            'observance' => 'normal_operations',
            'optional'   => 'normal_operations',
            default      => 'office_closed',
        };
    }
}
