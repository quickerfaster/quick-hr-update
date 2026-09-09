<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Onboarding Step 6: Set notification preferences.
 *
 * Uses the HasSettings trait (via HasUILibraryUser) to persist
 * the user's notification channel preferences.
 */
class NotificationPreferencesForm extends Component
{
    public bool $email_notifications = true;

    public bool $push_notifications = true;

    public bool $sms_notifications = false;

    public string $digest_frequency = 'daily';

    public function mount(): void
    {
        $user = Auth::user();

        $emailVal = $user->getSetting('notifications.email');
        $pushVal = $user->getSetting('notifications.push');
        $smsVal = $user->getSetting('notifications.sms');
        $digestVal = $user->getSetting('notifications.digest_frequency');

        $this->email_notifications = $emailVal !== null ? (bool) $emailVal : true;
        $this->push_notifications = $pushVal !== null ? (bool) $pushVal : true;
        $this->sms_notifications = $smsVal !== null ? (bool) $smsVal : false;
        $this->digest_frequency = $digestVal ?: 'daily';
    }

    public function rules(): array
    {
        return [
            'digest_frequency' => 'required|in:instant,daily,weekly',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        $user->setSetting('notifications.email', $this->email_notifications, 'notifications');
        $user->setSetting('notifications.push', $this->push_notifications, 'notifications');
        $user->setSetting('notifications.sms', $this->sms_notifications, 'notifications');
        $user->setSetting('notifications.digest_frequency', $this->digest_frequency, 'notifications');

        $this->redirectToNextStep();
    }

    public function skip(): void
    {
        $this->redirectToNextStep();
    }

    protected function redirectToNextStep(): void
    {
        // All onboarding steps complete — redirect to dashboard
        $this->redirect(route(config('ui-library.home_route', 'admin.dashboard')));
    }

    public function render()
    {
        return view('hr::onboarding.notification-preferences');
    }
}
