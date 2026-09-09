{{-- Onboarding Step 5: Notification Preferences (OPTIONAL) --}}
<div>
    <h4 class="mb-1">Notification Preferences</h4>
    <p class="text-muted small mb-4">
        Choose how you want to receive notifications from the platform.
    </p>

    <form wire:submit.prevent="save">
        <div class="mb-4">
            <h6 class="text-secondary mb-3">Notification Channels</h6>

            <div class="form-check form-switch mb-3 ps-0">
                <label class="d-flex justify-content-between align-items-center mb-0" style="cursor: pointer;">
                    <div>
                        <strong><i class="fas fa-envelope me-2 text-muted"></i>Email Notifications</strong>
                        <p class="text-muted small mb-0">Receive notificationsvia email</p>
                    </div>
                    <input class="form-check-input ms-3" type="checkbox" wire:model="email_notifications"
                           role="switch" style="width: 3em; height: 1.5em;">
                </label>
            </div>

            <div class="form-check form-switch mb-3 ps-0">
                <label class="d-flex justify-content-between align-items-center mb-0" style="cursor: pointer;">
                    <div>
                        <strong><i class="fas fa-bell me-2 text-muted"></i>Push Notifications</strong>
                        <p class="text-muted small mb-0">Receive push notifications in your browser</p>
                    </div>
                    <input class="form-check-input ms-3" type="checkbox" wire:model="push_notifications"
                           role="switch" style="width: 3em; height: 1.5em;">
                </label>
            </div>

            <div class="form-check form-switch mb-3 ps-0">
                <label class="d-flex justify-content-between align-items-center mb-0" style="cursor: pointer;">
                    <div>
                        <strong><i class="fas fa-sms me-2 text-muted"></i>SMS Notifications</strong>
                        <p class="text-muted small mb-0">Receive notifications via text message</p>
                    </div>
                    <input class="form-check-input ms-3" type="checkbox" wire:model="sms_notifications"
                           role="switch" style="width: 3em; height: 1.5em;">
                </label>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="text-secondary mb-3">Digest Frequency</h6>
            <p class="text-muted small mb-2">How often should we send you a summary of notifications?</p>

            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" id="digest_instant" wire:model="digest_frequency"
                       value="instant" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="digest_instant">Instant</label>

                <input type="radio" class="btn-check" id="digest_daily" wire:model="digest_frequency"
                       value="daily" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="digest_daily">Daily</label>

                <input type="radio" class="btn-check" id="digest_weekly" wire:model="digest_frequency"
                       value="weekly" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="digest_weekly">Weekly</label>
            </div>
            @error('digest_frequency') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary" wire:click="skip">
                Skip for Now
            </button>
            <button type="submit" class="btn btn-primary">
                Save & Finish
                <i class="fas fa-check ms-2"></i>
            </button>
        </div>
    </form>
</div>
