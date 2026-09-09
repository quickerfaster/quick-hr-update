<?php

namespace App\Modules\Hr\Listeners;

use QuickerFaster\UILibrary\Events\Invitations\InvitationAccepted;
use App\Modules\Hr\Services\HrInvitationService;

class LinkInvitationToEmployee
{
    public function __construct(
        protected HrInvitationService $hrInvitationService
    ) {}

    /**
     * When an Invitation is accepted, attempt auto-linking to the employee.
     *
     * Listens for InvitationAccepted (fired by InvitationService::accept())
     * rather than DataTableRecordSaved, because the accept flow uses direct
     * Eloquent updates and never dispatches DataTableRecordSaved.
     */
    public function handle(InvitationAccepted $event): void
    {
        $invitation = $event->invitation;

        // Find the user that was just created/activated
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $invitation->email)->first();

        if (! $user) {
            return;
        }

        $this->hrInvitationService->linkOnAccept($invitation, $user);
    }
}
