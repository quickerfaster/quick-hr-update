<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\ClockEvent;
use Carbon\Carbon;
use QuickerFaster\UILibrary\Contracts\Attendance\ClockEventRecorder;

/**
 * ClockEventRecorderService — consuming-app implementation of the
 * library's ClockEventRecorder contract.
 *
 * Binds the library's domain-independent clock-in/out component to
 * the consuming app's ClockEvent model.
 */
class ClockEventRecorderService implements ClockEventRecorder
{
    /**
     * {@inheritdoc}
     */
    public function getLatestToday(int|string $employeeId): ?array
    {
        $today = Carbon::today();

        $event = ClockEvent::query()
            ->where('employee_id', $employeeId)
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$event) {
            return null;
        }

        return [
            'event_type' => $event->event_type,
            'timestamp'  => $event->timestamp instanceof Carbon
                ? $event->timestamp->toIso8601String()
                : (string) $event->timestamp,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function record(int|string $employeeId, string $eventType, array $meta = []): array
    {
        $now = Carbon::now();

        $event = ClockEvent::create([
            'employee_id'     => $employeeId,
            'event_type'      => $eventType,
            'timestamp'       => $now,
            'method'          => $meta['method'] ?? 'web',
            'ip_address'      => $meta['ip_address'] ?? request()->ip(),
            'device_name'     => $meta['device_name'] ?? request()->userAgent(),
            'timezone'        => $meta['timezone'] ?? config('app.timezone', 'UTC'),
            'sync_status'     => 'synced',
        ]);

        return [
            'event_type' => $event->event_type,
            'timestamp'  => $event->timestamp instanceof Carbon
                ? $event->timestamp->toIso8601String()
                : (string) $event->timestamp,
        ];
    }
}
