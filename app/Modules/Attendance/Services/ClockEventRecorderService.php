<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\ClockEvent;
use App\Modules\Attendance\Jobs\ProcessAttendanceJob;
use App\Modules\Hr\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * ClockEventRecorderService — handles clock-in/clock-out event recording.
 *
 * Features:
 *   - Idempotency check (10-second window)
 *   - Company scoping (HasCompanyScope compatibility)
 *   - Overnight shift detection
 *   - Geofence validation (via GeofenceValidator)
 *   - Browser geolocation capture (lat/lng from $meta)
 *   - Automatic attendance recalculation (ProcessAttendanceJob)
 */
class ClockEventRecorderService
{
    /**
     * {@inheritdoc}
     */
    public function getLatestToday(int|string $employeeId): ?array
    {
        $resolvedId = $this->resolveEmployeeId($employeeId);

        // Resolve the employee's local "today" boundaries and convert to UTC
        // so the query matches the correct 24-hour window regardless of the
        // app's default timezone (UTC). Without this, a Lagos employee who
        // clocks in between 00:00–00:59 local time would be assigned to the
        // wrong calendar day.
        $timezone = UserTimezone::resolve($resolvedId);
        $startOfTodayUtc = Carbon::today($timezone)->setTimezone('UTC');
        $endOfTodayUtc = Carbon::tomorrow($timezone)->setTimezone('UTC');

        $event = ClockEvent::query()
            ->where('employee_id', $resolvedId)
            ->whereBetween('timestamp', [$startOfTodayUtc, $endOfTodayUtc])
            ->orderBy('timestamp', 'desc')
            ->first();

        // If no event today, check for an unclosed session from yesterday
        // (handles overnight shifts where clock-in was before midnight)
        if (!$event) {
            $yesterdayStartUtc = $startOfTodayUtc->copy()->subDay();
            $yesterdayEvent = ClockEvent::query()
                ->where('employee_id', $resolvedId)
                ->whereBetween('timestamp', [$yesterdayStartUtc, $startOfTodayUtc])
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
     *   - 'accuracy'  (float) — browser geolocation accuracy in meters
     *   - 'method'    (string) — 'web', 'device', etc.
     *   - 'ip_address', 'device_name', 'timezone'
     */
    public function record(int|string $employeeId, string $eventType, array $meta = []): array
    {
        $resolvedId = $this->resolveEmployeeId($employeeId);
        $now = Carbon::now();
        $timezone = $meta['timezone'] ?? UserTimezone::resolve($resolvedId);

        // Resolve company_id from the employee so the ClockEvent is visible
        // under the HasCompanyScope global scope after page refresh.
        $companyId = Employee::withoutCompanyScope()->find($resolvedId)?->company_id
            ?? Session::get(config('ui-library.tenancy.session_key', 'current_company_id'));

        // Capture GPS coordinates and resolve location name for audit (all event types)
        $latitude = $meta['latitude'] ?? null;
        $longitude = $meta['longitude'] ?? null;
        $accuracy = $meta['accuracy'] ?? null;
        $locationName = null;

        if ($latitude !== null && $longitude !== null) {
            $validator = app(GeofenceValidator::class);
            $geofenceResult = $validator->validate($resolvedId, (float) $latitude, (float) $longitude);

            // Enforce geofence for clock-in only (clock-out is unrestricted —
            // employees may need to clock out after leaving the office)
            if ($eventType === 'clock_in' && !$geofenceResult['passed']) {
                Log::warning('Clock-in blocked', [
                    'employee_id' => $resolvedId,
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                    'accuracy'    => $accuracy,
                    'geofence'    => $geofenceResult,
                ]);

                throw new \RuntimeException(
                    'Clock-in failed: ' . ($geofenceResult['reason'] ?? 'Outside allowed geofence area.')
                );
            }

            // Resolve location name: use company location when within geofence,
            // otherwise reverse-geocode the actual GPS coordinates for audit.
            if ($geofenceResult['passed']) {
                $locationName = $geofenceResult['location_name'];
            } else {
                $locationName = $this->reverseGeocode((float) $latitude, (float) $longitude);
            }

            Log::info('Geofence validation', [
                'employee_id' => $resolvedId,
                'event_type'  => $eventType,
                'enforced'    => $eventType === 'clock_in',
                'accuracy'    => $accuracy,
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
            'accuracy'        => $accuracy,
            'location_name'   => $locationName,
            'method'          => $meta['method'] ?? 'web',
            'ip_address'      => $meta['ip_address'] ?? request()->ip(),
            'device_name'     => $meta['device_name'] ?? request()->userAgent(),
            'timezone'        => $timezone,
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
     * Reverse-geocode GPS coordinates to a human-readable location name
     * using the free OpenStreetMap Nominatim API.
     *
     * Falls back to "lat, lng" format if the API is unreachable or
     * returns no result.
     */
    private function reverseGeocode(float $latitude, float $longitude): string
    {
        try {
            $url = sprintf(
                'https://nominatim.openstreetmap.org/reverse?format=json&lat=%.6f&lon=%.6f&zoom=18&addressdetails=1',
                $latitude,
                $longitude
            );

            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: QuickerFaster-HR/1.0\r\n",
                    'timeout' => 5,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                Log::warning('Reverse geocode failed — network error', [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                ]);
                return sprintf('%.4f, %.4f', $latitude, $longitude);
            }

            $data = json_decode($response, true);

            if (!empty($data['display_name'])) {
                return $data['display_name'];
            }

            Log::info('Reverse geocode returned no display_name', [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'response'  => $data,
            ]);

            return sprintf('%.4f, %.4f', $latitude, $longitude);
        } catch (\Throwable $e) {
            Log::warning('Reverse geocode exception', [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'error'     => $e->getMessage(),
            ]);
            return sprintf('%.4f, %.4f', $latitude, $longitude);
        }
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
