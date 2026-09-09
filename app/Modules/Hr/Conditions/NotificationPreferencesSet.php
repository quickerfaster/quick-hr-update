<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the user has set notification preferences.
 *
 * Uses the HasSettings trait (bundled via HasUILibraryUser) to check
 * whether the user has explicitly saved notification preference settings.
 */
class NotificationPreferencesSet implements OnboardingCondition
{
    public function __invoke($user): bool
    {
        // Check if the user has saved any notification-related settings.
        // The HasSettings trait provides getSetting() — we check for a
        // known notification preference key.
        $emailNotifications = $user->getSetting('notifications.email', null);
        $pushNotifications = $user->getSetting('notifications.push', null);

        // If either preference has been explicitly set, consider this step complete.
        return $emailNotifications !== null || $pushNotifications !== null;
    }
}
