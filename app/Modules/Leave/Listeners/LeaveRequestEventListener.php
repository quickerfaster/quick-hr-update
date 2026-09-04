<?php

namespace App\Modules\Leave\Listeners;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Listeners\DataTableRecordListener;

use App\Modules\Leave\Services\LeaveAttendanceSync;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Attendance\Services\AttendanceAggregator;
use QuickerFaster\UILibrary\Models\Document;

class LeaveRequestEventListener extends DataTableRecordListener
{
    protected function handleCreated(DataTableRecordSaved $event): void
    {
        if (!str_contains($event->model, 'LeaveRequest')) {
            return;
        }

        if (!isset($event->newRecord['id']) || empty($event->newRecord['attachments'] ?? null)) {
            return;
        }

        $leaveRequest = LeaveRequest::find($event->newRecord['id']);
        if (!$leaveRequest) {
            return;
        }

        $filePath = $event->newRecord['attachments'];

        Document::create([
            'documentable_type' => LeaveRequest::class,
            'documentable_id' => $leaveRequest->id,
            'name' => basename($filePath),
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'mime_type' => \Illuminate\Support\Facades\Storage::disk('public')->mimeType($filePath) ?? 'application/octet-stream',
            'size' => \Illuminate\Support\Facades\Storage::disk('public')->size($filePath) ?? 0,
            'document_type' => 'leave_request',
            'disk' => 'public',
        ]);
    }

    protected function handleUpdated(DataTableRecordSaved $event): void
    {
        if (!str_contains($event->model, 'LeaveRequest')) {
            return;
        }

        if (isset($event->newRecord) && isset($event->newRecord["id"])) {
            $leaveRequestId = $event->newRecord["id"];
            $leaveRequest = LeaveRequest::find($leaveRequestId);
            if ($leaveRequest) {
                $this->approveLeave($leaveRequest, $event);
            } else {
                \Log::warning('LeaveRequestEventListener: LeaveRequest not found', [
                    'leaveRequestId' => $leaveRequestId
                ]);
            }

        } else {
            \Log::warning('LeaveRequestEventListener: Updated but no ID found in newRecord', [
                'newRecord' => $event->newRecord ?? null
            ]);
        }
    }

    protected function handleDeleted(DataTableRecordSaved $event): void
    {
        if (!str_contains($event->model, 'LeaveRequest')) {
            return;
        }

        $syncService = new LeaveAttendanceSync(new AttendanceAggregator());

        if (isset($event->oldRecord) && isset($event->oldRecord["status"]) && $event->oldRecord["status"] === 'Approved') {
            $leaveRequestId = $event->oldRecord['id'] ?? null;

            if ($leaveRequestId) {
                $leaveRequest = LeaveRequest::withTrashed()->find($leaveRequestId);

                if ($leaveRequest) {
                    $syncService->removeLeaveAttendance($leaveRequest);
                } else {
                    \Log::warning('LeaveRequestEventListener: Deleted LeaveRequest not found even with trashed', [
                        'leaveRequestId' => $leaveRequestId
                    ]);
                }
            } else {
                \Log::warning('LeaveRequestEventListener: Deleted record but no ID found in oldRecord', [
                    'oldRecord' => $event->oldRecord ?? null
                ]);
            }
        }
    }

    public function approveLeave(LeaveRequest $leaveRequest, $event)
    {
        $originalStatus = $event->oldRecord["status"] ?? null;
        $newStatus = $leaveRequest->status;

        // NEW: Sync to attendance
        $syncService = new LeaveAttendanceSync(new AttendanceAggregator());
        $syncService->syncLeaveToAttendance($leaveRequest);

        // Handle approval: Sync to attendance
        if ($originalStatus !== 'Approved' && $newStatus === 'Approved') {
            $syncService->syncLeaveToAttendance($leaveRequest);
        }

        // Handle cancellation/denial: Remove from attendance
        if ($originalStatus === 'Approved' && in_array($newStatus, ['Denied', 'Cancelled'])) {
            $syncService->removeLeaveAttendance($leaveRequest);
        }

        // Handle re-approval after changes
        if ($originalStatus === 'Approved' && $newStatus === 'Approved') {
            // If dates changed, re-sync
            if ($leaveRequest->isDirty(['start_date', 'end_date'])) {
                // First remove old sync
                $syncService->removeLeaveAttendance($leaveRequest);
                // Then re-sync with new dates
                $syncService->syncLeaveToAttendance($leaveRequest);
            }
        }

        // Send notifications, etc.
    }
}