<?php

namespace App\Modules\Hr\Listeners;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Listeners\DataTableRecordListener;
use QuickerFaster\UILibrary\Models\Invitation;
use App\Modules\Hr\Services\HrInvitationService;

class LinkInvitationToEmployee extends DataTableRecordListener
{
    public function __construct(
        protected HrInvitationService $hrInvitationService
    ) {}

    /**
     * When an Invitation is updated to 'accepted', attempt auto-linking.
     */
    protected function handleUpdated(DataTableRecordSaved $event): void
    {
        if ($event->model !== Invitation::class) {
            return;
        }

        $newStatus = $event->newRecord['status'] ?? null;
        if ($newStatus !== 'accepted') {
            return;
        }

        $invitation = Invitation::find($event->newRecord['id'] ?? null);
        if (! $invitation) {
            return;
        }

        // Find the user that was just created/activated
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $invitation->email)->first();

        if (! $user) {
            return;
        }

        $this->hrInvitationService->linkOnAccept($invitation, $user);
    }
}
