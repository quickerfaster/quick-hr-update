<?php

namespace App\Modules\Holiday\Traits;

use App\Modules\Holiday\Models\HolidayBatchDraft;

/**
 * Trait for Holiday batch wizard custom components.
 *
 * The library's Wizard component calls next() on "Save & Continue",
 * which dispatches a `saveStepForm` event (because customComponent
 * steps are treated as form steps). Components must listen for that
 * event and emit `stepFormSaved` back so the wizard advances.
 *
 * Also handles cross-session draft persistence: on each step
 * advancement, the wizard state is saved to the holiday_batch_drafts
 * table so the user can resume after their session expires.
 *
 * Components using this trait must define a public int $stepIndex
 * property, a public string $wizardId property, and a protected
 * saveStepData(): void method.
 */
trait AdvancesWizardStep
{
    public function initializeAdvancesWizardStep(): void
    {
        $this->listeners['saveStepForm'] = 'handleSaveStepForm';
        $this->listeners['wizardCancelled'] = 'handleWizardCancelled';
    }

    /**
     * Handle the wizard's saveStepForm event, persist the component's
     * state, save a cross-session draft, then emit stepFormSaved to
     * advance the wizard.
     */
    public function handleSaveStepForm(int $stepIndex): void
    {
        // Only respond if the event targets this step.
        if ($stepIndex !== ($this->stepIndex ?? null)) {
            return;
        }

        if (method_exists($this, 'saveStepData')) {
            $this->saveStepData();
        }

        // Save a cross-session draft so the user can resume later.
        // Skipped on the final submission step, where the submission
        // service deletes the draft instead.
        if ($this->shouldSaveDraft()) {
            $this->saveWizardDraft();
        }

        // recordId 0 signals no single primary record was created.
        $this->dispatch('stepFormSaved', recordId: 0, stepIndex: $this->stepIndex);
    }

    /**
     * Whether this step should persist a cross-session draft.
     * The final submission step overrides this to return false.
     */
    protected function shouldSaveDraft(): bool
    {
        return true;
    }

    /**
     * Persist the current wizard state to the holiday_batch_drafts
     * table for cross-session resume.
     */
    protected function saveWizardDraft(): void
    {
        try {
            $userId = auth()->id();
            HolidayBatchDraft::saveDraft($this->wizardId, $userId);
        } catch (\Throwable $e) {
            \Log::warning('Holiday batch: failed to save draft', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the wizard's wizardCancelled event (user chose "Delete
     * Progress"). Deletes the persisted DB draft so the wizard
     * starts fresh next time.
     */
    public function handleWizardCancelled(string $wizardId): void
    {
        if ($wizardId !== ($this->wizardId ?? null)) {
            return;
        }

        try {
            HolidayBatchDraft::where('wizard_id', $wizardId)->delete();
        } catch (\Throwable $e) {
            \Log::warning('Holiday batch: failed to delete draft on cancel', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
