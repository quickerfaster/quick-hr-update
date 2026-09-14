<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Location;

/**
 * GeofenceValidator — validates clock-in GPS coordinates against
 * the employee's assigned work location boundaries.
 *
 * Validation chain (first match wins):
 *   1. Employee's assigned Location (EmployeePosition.location_id)
 *   2. Company's headquarters Location (is_headquarters = true)
 *   3. Any active, non-remote company Location with coordinates
 *   4. Skip validation (graceful degradation — no coordinates available)
 *
 * Remote locations (is_remote = true) are always skipped.
 */
class GeofenceValidator
{
    /**
     * Earth's mean radius in meters (WGS-84).
     */
    private const EARTH_RADIUS_METERS = 6_371_000;

    /**
     * Validate that the given GPS coordinates fall within the geofence
     * of the employee's assigned work location.
     *
     * @param int $employeeId
     * @param float $latitude  Clock-in latitude
     * @param float $longitude Clock-in longitude
     * @return array{passed: bool, location_id: int|null, location_name: string|null, distance_meters: float|null, geofence_radius: float|null, reason: string}
     */
    public function validate(int $employeeId, float $latitude, float $longitude): array
    {
        $employee = Employee::with(['employeePosition.location'])->find($employeeId);

        if (!$employee) {
            return $this->skip('Employee not found');
        }

        $position = $employee->employeePosition;

        if (!$position) {
            return $this->skip('Employee has no position assigned');
        }

        // Priority 1: Employee's assigned location
        if ($position->location_id) {
            $location = $position->location;

            if ($this->isValidatable($location)) {
                return $this->checkDistance($location, $latitude, $longitude);
            }
        }

        // Priority 2: Company headquarters
        $headquarters = Location::where('company_id', $position->company_id)
            ->where('is_headquarters', true)
            ->where('is_active', true)
            ->first();

        if ($headquarters && $this->isValidatable($headquarters)) {
            return $this->checkDistance($headquarters, $latitude, $longitude);
        }

        // Priority 3: Any active, non-remote company location with coordinates
        $anyLocation = Location::where('company_id', $position->company_id)
            ->where('is_active', true)
            ->where('is_remote', false)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        if ($anyLocation) {
            return $this->checkDistance($anyLocation, $latitude, $longitude);
        }

        // Priority 4: Skip — no validatable locations found
        return $this->skip('No company locations with coordinates configured');
    }

    /**
     * Check if a location can be used for geofence validation.
     */
    private function isValidatable(?Location $location): bool
    {
        if (!$location) {
            return false;
        }

        if (!$location->is_active) {
            return false;
        }

        if ($location->is_remote) {
            return false;
        }

        if ($location->latitude === null || $location->longitude === null) {
            return false;
        }

        return true;
    }

    /**
     * Calculate Haversine distance and check against the location's geofence radius.
     */
    private function checkDistance(Location $location, float $latitude, float $longitude): array
    {
        $distance = $this->haversineDistance(
            (float) $location->latitude,
            (float) $location->longitude,
            $latitude,
            $longitude
        );

        $radius = (float) ($location->geofence_radius ?? 100);

        $passed = $distance <= $radius;

        return [
            'passed'           => $passed,
            'location_id'      => $location->id,
            'location_name'    => $location->name,
            'distance_meters'  => round($distance, 2),
            'geofence_radius'  => $radius,
            'reason'           => $passed
                ? "Within geofence ({$location->name}: " . round($distance, 1) . "m / {$radius}m)"
                : "Outside geofence ({$location->name}: " . round($distance, 1) . "m > {$radius}m)",
        ];
    }

    /**
     * Return a skip result — validation not performed.
     */
    private function skip(string $reason): array
    {
        return [
            'passed'           => true,  // Skip = allow (don't block clock-in)
            'location_id'      => null,
            'location_name'    => null,
            'distance_meters'  => null,
            'geofence_radius'  => null,
            'reason'           => "Geofence skipped: {$reason}",
        ];
    }

    /**
     * Calculate the great-circle distance between two GPS coordinates
     * using the Haversine formula.
     *
     * @param float $lat1 Location latitude
     * @param float $lon1 Location longitude
     * @param float $lat2 Clock-in latitude
     * @param float $lon2 Clock-in longitude
     * @return float Distance in meters
     */
    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }
}
