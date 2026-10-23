<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Services\HolidayBatchSubmissionService;
use App\Modules\Holiday\Http\Livewire\HolidayBatchStepOne;
use App\Modules\Holiday\Traits\AdvancesWizardStep;

/**
 * Wizard review/preview step for the Holiday Batch Creation wizard.
 *
 * Shows a tabular preview of all holidays that will be created,
 * with inline editing of individual entries before confirmation.
 * Reads selections from the TemplateSelector's session data and
 * dates from the DateSelector's session data.
 *
 * On submission, calls HolidayBatchSubmissionService to bulk-create
 * the Holiday records, then emits stepFormSaved to advance the wizard.
 */
class HolidayBatchPreview extends Component
{
    use AdvancesWizardStep;

    public string $configKey;
    public int $stepIndex;
    public ?int $recordId = null;
    public ?int $primaryModelId = null;
    public array $presetData = [];
    public string $wizardId;

    public array $previewItems = [];
    public array $editItem = [];
    public ?int $editingIndex = null;

    public bool $submitting = false;
    public ?string $submitError = null;
    public int $submittedCount = 0;
    public bool $submitted = false;

    public function mount(
        string $configKey,
        int $stepIndex,
        ?int $recordId = null,
        ?int $primaryModelId = null,
        array $presetData = [],
        string $wizardId = ''
    ): void {
        $this->configKey      = $configKey;
        $this->stepIndex      = $stepIndex;
        $this->recordId       = $recordId;
        $this->primaryModelId = $primaryModelId;
        $this->presetData     = $presetData;
        $this->wizardId       = $wizardId;

        $this->buildPreview();
    }

    // -----------------------------------------------------------------
    // Build preview from session data
    // -----------------------------------------------------------------

    protected function buildPreview(): void
    {
        $templates = HolidayTemplateSelector::getSelectedTemplates($this->wizardId);
        $dates     = HolidayBatchDateSelector::getDatesForWizard($this->wizardId);

        $this->previewItems = [];

        // If we have specific dates AND templates, cross-join them
        if (!empty($dates) && !empty($templates)) {
            foreach ($dates as $date) {
                foreach ($templates as $template) {
                    $this->previewItems[] = [
                        'name'        => $template['name'],
                        'date'        => $date,
                        'type'        => $template['type'],
                        'pay_rate'    => $template['pay_rate'],
                        'is_paid'     => $template['is_paid'],
                        'is_recurring'=> $template['is_recurring'],
                        'description' => $template['description'] ?? '',
                    ];
                }
            }
        }
        // If only templates (copy_previous mode), use their target dates
        elseif (!empty($templates)) {
            foreach ($templates as $template) {
                $this->previewItems[] = [
                    'name'        => $template['name'],
                    'date'        => $template['target_date'] ?? $template['date'],
                    'type'        => $template['type'],
                    'pay_rate'    => $template['pay_rate'],
                    'is_paid'     => $template['is_paid'],
                    'is_recurring'=> $template['is_recurring'],
                    'description' => $template['description'] ?? '',
                ];
            }
        }
        // If only dates (no templates selected), show empty dates
        elseif (!empty($dates)) {
            foreach ($dates as $date) {
                $this->previewItems[] = [
                    'name'        => 'Holiday',
                    'date'        => $date,
                    'type'        => 'company',
                    'pay_rate'    => 1.0,
                    'is_paid'     => true,
                    'is_recurring'=> false,
                    'description' => '',
                ];
            }
        }
    }

    // -----------------------------------------------------------------
    // Inline editing
    // -----------------------------------------------------------------

    public function startEdit(int $index): void
    {
        $this->editingIndex = $index;
        $this->editItem = $this->previewItems[$index];
    }

    public function cancelEdit(): void
    {
        $this->editingIndex = null;
        $this->editItem = [];
    }

    public function saveEdit(): void
    {
        if ($this->editingIndex !== null) {
            $this->previewItems[$this->editingIndex] = $this->editItem;
        }
        $this->editingIndex = null;
        $this->editItem = [];
    }

    public function removeItem(int $index): void
    {
        unset($this->previewItems[$index]);
        $this->previewItems = array_values($this->previewItems);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function getCalendarName(): string
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        $calendarId = $stepOne['calendarId'] ?? $this->presetData['calendar_id'] ?? null;
        if ($calendarId) {
            $calendar = HolidayCalendar::find($calendarId);
            return $calendar?->name ?? 'Unknown Calendar';
        }
        return 'No calendar selected';
    }

    public function getTargetYear(): int
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        return (int) ($stepOne['targetYear'] ?? $this->presetData['target_year'] ?? now()->year);
    }

    public function getSourceLabel(): string
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        $sourceOption = $stepOne['sourceOption'] ?? $this->presetData['source_option'] ?? 'copy_previous';
        return match ($sourceOption) {
            'empty'         => 'Start Fresh',
            'copy_previous' => 'Copy from Previous Year',
            'template'      => 'Use Country Template',
            'import'        => 'Import CSV File',
            default         => 'Unknown',
        };
    }

    public function getTypeBadgeClass(string $type): string
    {
        return match ($type) {
            'public'     => 'bg-success',
            'company'    => 'bg-primary',
            'regional'   => 'bg-info',
            'observance' => 'bg-secondary',
            'optional'   => 'bg-warning text-dark',
            default      => 'bg-light text-dark',
        };
    }

    /**
     * Submit the batch — bulk-create all preview items as Holiday records.
     * Advancement is handled by the AdvancesWizardStep trait.
     */
    public function submitBatch(): void
    {
        if ($this->submitted) {
            return;
        }

        $this->submitting = true;
        $this->submitError = null;

        try {
            $service = app(HolidayBatchSubmissionService::class);
            $result = $service->submit($this->wizardId, $this->presetData, $this->previewItems);

            $this->submittedCount = $result['copied'];
            $this->submitted = true;
            $this->submitting = false;

            if (!empty($result['errors'])) {
                $this->submitError = implode('; ', $result['errors']);
            }
        } catch (\Throwable $e) {
            $this->submitError = $e->getMessage();
            $this->submitting = false;
        }
    }

    /**
     * Hook used by the AdvancesWizardStep trait — performs the batch
     * submission when the wizard advances from the review step.
     */
    protected function saveStepData(): void
    {
        $this->submitBatch();
    }

    /**
     * The final step must NOT re-save a draft — submitBatch() already
     * deletes it. Re-saving here would leave a stale draft that the
     * route auto-restores on the next visit.
     */
    protected function shouldSaveDraft(): bool
    {
        return false;
    }

    public function render()
    {
        return view('holiday::livewire.holiday-batch-preview');
    }
}
