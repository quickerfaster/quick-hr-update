<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\ClockEvent;
use App\Modules\Attendance\Jobs\ProcessAttendanceJob;
use App\Modules\Hr\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use QuickerFaster\UILibrary\Contracts\Attendance\ClockEventRecorder;

/**
 * ClockEventRecorderService — consuming-app implementation of the
 * library's ClockEventRecorder contract.
 *
 * Binds the library's domain-independent clock-in/out component to
 * the consuming app's ClockEvent model.
 *
 * Features:
 *   - Idempotency check (10-second window)
 *   - Company scoping (HasCompanyScope compatibility)
 *   - Overnight shift detection
 *   - Geofence validation (via GeofenceValidator)
 *   - Browser geolocation capture (lat/lng from $meta)
 *   - Automatic attendance recalculation (ProcessAttendanceJob)
 */
class ClockEventRecorderService implements ClockEventRecorder
{
    /**
     * {@inheritdoc}
     */
    public function getLatestToday(int|string $employeeId): ?array
    {
        $resolvedId = $this->resolveEmployeeId($employeeId);
        $today = Carbon::today();

        $event = ClockEvent::query()
            ->where('employee_id', $resolvedId)
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->first();

        // If no event today, check for an unclosed session from yesterday
        // (handles overnight shifts where clock-in was before midnight)
        if (!$event) {
            $yesterdayEvent = ClockEvent::query()
                ->where('employee_id', $resolvedId)
                ->whereDate('timestamp', $today->copy()->subDay())
                ->orderBy('timestamp', 'desc')
                ->first();

            if ($yesterdayEvent && $yesterdayEvent->event_type === 'clock_in') {
                return [
                    'event_type' => 'clock_in',
                    'timestamp'  => $yesterdayEvent->timestamp instanceof Carbon
                        ? $yesterdayEvent->timestamp->toIso8601String()
                        : (string) $yesterdayEvent->timestamp,
                ];
            }

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
     *
     * Accepts optional $meta keys:
     *   - 'latitude'  (float) — browser geolocation
     *   - 'longitude' (float) — browser geolocation
     *   - 'method'    (string) — 'web', 'device', etc.
     *   - 'ip_address', 'device_name', 'timezone'
     */
    public function record(int|string $employeeId, string $eventType, array $meta = []): array
    {
        $resolvedId = $this->resolveEmployeeId($employeeId);
        $now = Carbon::now();

        // Resolve company_id from the employee so the ClockEvent is visible
        // under the HasCompanyScope global scope after page refresh.
        $companyId = Employee::find($resolvedId)?->company_id
            ?? Session::get(config('ui-library.tenancy.session_key', 'current_company_id'));

        // Capture GPS coordinates and resolve location name for audit (all event types)
        $latitude = $meta['latitude'] ?? null;
        $longitude = $meta['longitude'] ?? null;
        $locationName = null;

        if ($latitude !== null && $longitude !== null) {
            $validator = app(GeofenceValidator::class);
            $geofenceResult = $validator->validate($resolvedId, (float) $latitude, (float) $longitude);

            // Always capture the nearest location name for audit
            $locationName = $geofenceResult['location_name'];

            // Enforce geofence for clock-in only (clock-out is unrestricted —
            // employees may need to clock out after leaving the office)
            if ($eventType === 'clock_in' && !$geofenceResult['passed']) {
                Log::warning('Clock-in blocked by geofence', [
                    'employee_id' => $resolvedId,
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                    'geofence'    => $geofenceResult,
                ]);

                throw new \RuntimeException(
                    $geofenceResult['reason'] ?? 'Outside allowed geofence area.'
                );
            }

            Log::info('Geofence validation', [
                'employee_id' => $resolvedId,
                'event_type'  => $eventType,
                'enforced'    => $eventType === 'clock_in',
                'geofence'    => $geofenceResult,
            ]);
        }

        // Idempotency check: prevent duplicate events within a 10-second window
        // (mirrors the check in ClockEventController::processClockEvent for API flow)
        $existing = ClockEvent::where('employee_id', $resolvedId)
            ->where('event_type', $eventType)
            ->where('timestamp', '>=', $now->copy()->subSeconds(10))
            ->where('timestamp', '<=', $now)
            ->exists();

        if ($existing) {
            return [
                'event_type' => $eventType,
                'timestamp'  => $now->toIso8601String(),
            ];
        }

        $event = ClockEvent::create([
            'company_id'      => $companyId,
            'employee_id'     => $resolvedId,
            'event_type'      => $eventType,
            'timestamp'       => $now,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'location_name'   => $locationName,
            'method'          => $meta['method'] ?? 'web',
            'ip_address'      => $meta['ip_address'] ?? request()->ip(),
            'device_name'     => $meta['device_name'] ?? request()->userAgent(),
            'timezone'        => $meta['timezone'] ?? config('app.timezone', 'UTC'),
            'sync_status'     => 'synced',
        ]);

        // Dispatch attendance recalculation (mirrors ClockEventController::processClockEvent)
        ProcessAttendanceJob::dispatch($resolvedId, $now->toDateString());

        return [
            'event_type' => $event->event_type,
            'timestamp'  => $event->timestamp instanceof Carbon
                ? $event->timestamp->toIso8601String()
                : (string) $event->timestamp,
        ];
    }

    /**
     * Resolve an employee identifier to an integer employee_id.
     *
     * Accepts either:
     *   - An integer (or numeric string) employee_id — returned as int
     *   - A non-numeric string employee_number — resolved via Employee model
     *
     * @param int|string $identifier
     * @return int
     * @throws \InvalidArgumentException if the employee cannot be found
     */
    private function resolveEmployeeId(int|string $identifier): int
    {
        if (is_numeric($identifier)) {
            return (int) $identifier;
        }

        $id = Employee::where('employee_number', $identifier)->value('id');

        if (!$id) {
            throw new \InvalidArgumentException("Employee not found for identifier: {$identifier}");
        }

        return (int) $id;
    }
}
