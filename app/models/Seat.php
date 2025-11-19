<?php
/**
 * ============================================================
 * SEAT MODEL
 * ============================================================
 *
 * Handles seat management and availability
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class Seat
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get all booked seats for a flight
     *
     * @param int $flightId Flight ID
     * @return array Array of booked seat numbers
     */
    public function getBookedSeats($flightId)
    {
        $sql = "SELECT DISTINCT p.seat_number 
                FROM passengers p
                JOIN bookings b ON p.booking_id = b.booking_id
                WHERE b.flight_id = ? 
                  AND b.booking_status = 'confirmed'
                  AND p.seat_number IS NOT NULL";

        $stmt = $this->db->query($sql, [$flightId]);
        $results = $stmt->fetchAll();

        // Return just the seat numbers as array
        return array_column($results, 'seat_number');
    }

    /**
     * Generate seat map for aircraft
     * Returns array of seats with their status
     *
     * @param int $flightId Flight ID
     * @return array Seat map data
     */
    public function generateSeatMap($flightId)
    {
        // Get flight and aircraft info
        $sql = "SELECT f.*, a.total_seats, a.economy_seats, a.business_seats, a.model
                FROM flights f
                JOIN aircraft a ON f.aircraft_id = a.aircraft_id
                WHERE f.flight_id = ?";

        $stmt = $this->db->query($sql, [$flightId]);
        $flight = $stmt->fetch();

        if (!$flight) {
            return false;
        }

        // Get booked seats
        $bookedSeats = $this->getBookedSeats($flightId);

        // Generate seat layout based on aircraft type
        $seatMap = $this->createSeatLayout($flight, $bookedSeats);

        return [
            'flight' => $flight,
            'seats' => $seatMap,
            'booked_seats' => $bookedSeats,
        ];
    }

    /**
     * Create seat layout based on aircraft configuration
     *
     * @param array $flight Flight and aircraft data
     * @param array $bookedSeats Already booked seats
     * @return array Structured seat layout
     */
    private function createSeatLayout($flight, $bookedSeats)
    {
        $seatMap = [];
        $businessSeats = $flight['business_seats'] ?? 0;
        $economySeats = $flight['economy_seats'] ?? 0;

        $rowNumber = 1;

        // Business Class (if available)
        if ($businessSeats > 0) {
            // Business class: 2-2 configuration
            $seatsPerRow = 4;
            $businessRows = ceil($businessSeats / $seatsPerRow);

            for ($row = 1; $row <= $businessRows; $row++) {
                $seatRow = [
                    'row_number' => $rowNumber,
                    'class' => 'business',
                    'seats' => [],
                ];

                // Left side: A, B
                foreach (['A', 'B'] as $letter) {
                    $seatNumber = $rowNumber . $letter;
                    $seatRow['seats'][] = [
                        'number' => $seatNumber,
                        'type' => $letter === 'A' ? 'window' : 'aisle',
                        'status' => in_array($seatNumber, $bookedSeats) ? 'booked' : 'available',
                    ];
                }

                // Aisle gap
                $seatRow['seats'][] = ['type' => 'aisle-gap'];

                // Right side: C, D
                foreach (['C', 'D'] as $letter) {
                    $seatNumber = $rowNumber . $letter;
                    $seatRow['seats'][] = [
                        'number' => $seatNumber,
                        'type' => $letter === 'C' ? 'aisle' : 'window',
                        'status' => in_array($seatNumber, $bookedSeats) ? 'booked' : 'available',
                    ];
                }

                $seatMap[] = $seatRow;
                $rowNumber++;
            }
        }

        // Economy Class
        if ($economySeats > 0) {
            // Economy class: 3-3 configuration
            $seatsPerRow = 6;
            $economyRows = ceil($economySeats / $seatsPerRow);

            for ($row = 1; $row <= $economyRows; $row++) {
                $seatRow = [
                    'row_number' => $rowNumber,
                    'class' => 'economy',
                    'seats' => [],
                ];

                // Left side: A, B, C
                foreach (['A', 'B', 'C'] as $letter) {
                    $seatNumber = $rowNumber . $letter;
                    $type = 'middle';
                    if ($letter === 'A') {
                        $type = 'window';
                    }
                    if ($letter === 'C') {
                        $type = 'aisle';
                    }

                    $seatRow['seats'][] = [
                        'number' => $seatNumber,
                        'type' => $type,
                        'status' => in_array($seatNumber, $bookedSeats) ? 'booked' : 'available',
                    ];
                }

                // Aisle gap
                $seatRow['seats'][] = ['type' => 'aisle-gap'];

                // Right side: D, E, F
                foreach (['D', 'E', 'F'] as $letter) {
                    $seatNumber = $rowNumber . $letter;
                    $type = 'middle';
                    if ($letter === 'D') {
                        $type = 'aisle';
                    }
                    if ($letter === 'F') {
                        $type = 'window';
                    }

                    $seatRow['seats'][] = [
                        'number' => $seatNumber,
                        'type' => $type,
                        'status' => in_array($seatNumber, $bookedSeats) ? 'booked' : 'available',
                    ];
                }

                $seatMap[] = $seatRow;
                $rowNumber++;
            }
        }

        return $seatMap;
    }

    /**
     * Check if seat is available
     *
     * @param int $flightId Flight ID
     * @param string $seatNumber Seat number
     * @return bool True if available
     */
    public function isSeatAvailable($flightId, $seatNumber)
    {
        $bookedSeats = $this->getBookedSeats($flightId);
        return !in_array($seatNumber, $bookedSeats);
    }

    /**
     * Check if multiple seats are available
     *
     * @param int $flightId Flight ID
     * @param array $seatNumbers Array of seat numbers
     * @return bool True if all available
     */
    public function areSeatsAvailable($flightId, $seatNumbers)
    {
        $bookedSeats = $this->getBookedSeats($flightId);

        foreach ($seatNumbers as $seatNumber) {
            if (in_array($seatNumber, $bookedSeats)) {
                return false;
            }
        }

        return true;
    }
}
?>
