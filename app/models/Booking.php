<?php
/**
 * ============================================================
 * BOOKING MODEL (app/models/Booking.php)
 * ============================================================
 *
 * Handles all database operations related to bookings.
 * Works in conjunction with Flight model to manage seat availability.
 *
 * Responsibilities:
 * - CRUD operations for bookings
 * - Booking validation
 * - Transaction management (booking + seat updates)
 * - Booking statistics and history
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Flight.php';

class Booking
{
    private $db;
    private $conn;

    // Properties matching database columns
    public $booking_id;
    public $booking_reference;
    public $user_id;
    public $flight_id;
    public $booking_date;
    public $total_amount;
    public $payment_status;
    public $booking_status;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    // ==================== CREATE ====================

    /**
     * Create a new booking with passengers (TRANSACTION)
     * This is the main booking function that handles everything atomically
     *
     * @param array $bookingData Booking information
     * @param array $passengers Array of passenger data
     * @return array|false ['booking_id' => int, 'booking_reference' => string] or false
     */
    public function createBooking($bookingData, $passengers)
    {
        try {
            // Start transaction - all or nothing
            $this->conn->beginTransaction();

            // 1. Verify flight exists and has enough seats
            $flightModel = new Flight();
            $seatsNeeded = count($passengers);

            if (!$flightModel->hasAvailableSeats($bookingData['flight_id'], $seatsNeeded)) {
                throw new Exception('Not enough seats available');
            }

            // 2. Generate unique booking reference
            $bookingReference = $this->generateBookingReference();

            // 3. Create booking record
            $sql = "INSERT INTO bookings 
                    (booking_reference, user_id, flight_id, total_amount, 
                     payment_status, booking_status) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->query($sql, [
                $bookingReference,
                $bookingData['user_id'],
                $bookingData['flight_id'],
                $bookingData['total_amount'],
                $bookingData['payment_status'] ?? 'pending',
                'confirmed',
            ]);

            $bookingId = $this->conn->lastInsertId();

            // 4. Add passengers to booking
            foreach ($passengers as $passenger) {
                $sql = "INSERT INTO passengers 
                        (booking_id, first_name, last_name, date_of_birth, 
                         passport_number, nationality, seat_number) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $this->db->query($sql, [
                    $bookingId,
                    $passenger['first_name'],
                    $passenger['last_name'],
                    $passenger['date_of_birth'] ?? null,
                    $passenger['passport_number'] ?? null,
                    $passenger['nationality'] ?? null,
                    $passenger['seat_number'] ?? null,
                ]);
            }

            // 5. Decrease available seats on flight
            if (!$flightModel->decreaseSeats($bookingData['flight_id'], $seatsNeeded)) {
                throw new Exception('Failed to update seat availability');
            }

            // Commit transaction - everything succeeded
            $this->conn->commit();

            return [
                'booking_id' => $bookingId,
                'booking_reference' => $bookingReference,
            ];
        } catch (Exception $e) {
            // Rollback transaction - something failed
            $this->conn->rollBack();
            error_log('Booking creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique booking reference (format: BK + 8 chars)
     *
     * @return string Booking reference
     */
    private function generateBookingReference()
    {
        do {
            $reference = 'BK' . strtoupper(substr(uniqid(), -8));

            // Check if reference already exists
            $stmt = $this->db->query(
                'SELECT booking_id FROM bookings WHERE booking_reference = ?',
                [$reference],
            );
        } while ($stmt->fetch()); // Keep generating until unique

        return $reference;
    }

    /**
     * Create notification for new booking
     */
    private function createBookingNotification($userId, $bookingId, $bookingReference)
    {
        $sql = "INSERT INTO notifications (user_id, title, message, type) 
                VALUES (?, ?, ?, ?)";

        $this->db->query($sql, [
            $userId,
            'Booking Confirmed',
            "Your booking {$bookingReference} has been confirmed. Your tickets are ready.",
            'success',
        ]);
    }

    // ==================== READ ====================

    /**
     * Find booking by ID with complete information
     *
     * @param int $id Booking ID
     * @return array|false Booking data with flight and passenger info
     */
    public function findById($id)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time, f.arrival_time, f.price,
                       o.city as origin_city, o.airport_code as origin_code, 
                       o.airport_name as origin_airport,
                       d.city as destination_city, d.airport_code as destination_code,
                       d.airport_name as destination_airport,
                       u.first_name as user_first_name, u.last_name as user_last_name,
                       u.email as user_email, u.phone as user_phone
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN users u ON b.user_id = u.user_id
                WHERE b.booking_id = ?";

        $stmt = $this->db->query($sql, [$id]);
        $booking = $stmt->fetch();

        if ($booking) {
            // Get passengers for this booking
            $booking['passengers'] = $this->getPassengersByBookingId($id);
        }

        return $booking;
    }

    /**
     * Find booking by reference number
     *
     * @param string $reference Booking reference
     * @return array|false Booking data
     */
    public function findByReference($reference)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time, f.arrival_time,
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE b.booking_reference = ?";

        $stmt = $this->db->query($sql, [$reference]);
        $booking = $stmt->fetch();

        if ($booking) {
            $booking['passengers'] = $this->getPassengersByBookingId($booking['booking_id']);
        }

        return $booking;
    }

    /**
     * Get all bookings for a user
     *
     * @param int $userId User ID
     * @param string $status Filter by status (optional)
     * @return array Array of bookings
     */
    public function getUserBookings($userId, $status = null)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time, f.arrival_time,
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE b.user_id = ?";

        $params = [$userId];

        if ($status) {
            $sql .= ' AND b.booking_status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY b.booking_date DESC';

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming bookings for a user (future flights only)
     *
     * @param int $userId User ID
     * @return array Array of upcoming bookings
     */
    public function getUpcomingBookings($userId)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time, f.arrival_time,
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE b.user_id = ? 
                  AND b.booking_status = 'confirmed'
                  AND f.departure_time > NOW()
                ORDER BY f.departure_time ASC";

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get past bookings for a user (historical flights)
     *
     * @param int $userId User ID
     * @return array Array of past bookings
     */
    public function getPastBookings($userId)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time, f.arrival_time,
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE b.user_id = ? 
                  AND f.departure_time < NOW()
                ORDER BY f.departure_time DESC";

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get passengers for a booking
     *
     * @param int $bookingId Booking ID
     * @return array Array of passengers
     */
    public function getPassengersByBookingId($bookingId)
    {
        $sql = 'SELECT * FROM passengers WHERE booking_id = ? ORDER BY passenger_id ASC';
        $stmt = $this->db->query($sql, [$bookingId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all bookings (for admin)
     *
     * @param int $limit Number of bookings
     * @param int $offset Starting point
     * @return array Array of bookings
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT b.*, 
                       f.flight_number, f.departure_time,
                       o.city as origin_city, d.city as destination_city,
                       u.first_name, u.last_name, u.email
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN users u ON b.user_id = u.user_id
                ORDER BY b.booking_date DESC";

        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            $stmt = $this->db->query($sql, [$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get bookings for a specific flight (flight manifest)
     *
     * @param int $flightId Flight ID
     * @return array Array of bookings with passenger details
     */
    public function getFlightManifest($flightId)
    {
        $sql = "SELECT b.booking_reference, b.booking_date,
                       u.first_name as user_first_name, u.last_name as user_last_name,
                       u.email, u.phone,
                       p.first_name as passenger_first_name, 
                       p.last_name as passenger_last_name,
                       p.seat_number, p.passport_number
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                JOIN passengers p ON b.booking_id = p.booking_id
                WHERE b.flight_id = ? AND b.booking_status = 'confirmed'
                ORDER BY p.seat_number ASC";

        $stmt = $this->db->query($sql, [$flightId]);
        return $stmt->fetchAll();
    }

    // ==================== UPDATE ====================

    /**
     * Update payment status
     *
     * @param int $bookingId Booking ID
     * @param string $status New payment status
     * @return bool Success status
     */
    public function updatePaymentStatus($bookingId, $status)
    {
        try {
            $sql = "UPDATE bookings SET payment_status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE booking_id = ?";
            $stmt = $this->db->query($sql, [$status, $bookingId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Payment status update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel booking (with seat restoration)
     *
     * @param int $bookingId Booking ID
     * @return bool Success status
     */
    public function cancelBooking($bookingId)
    {
        try {
            $this->conn->beginTransaction();

            // Get booking details
            $booking = $this->findById($bookingId);
            if (!$booking) {
                throw new Exception('Booking not found');
            }

            // Check if flight hasn't departed yet
            if (strtotime($booking['departure_time']) < time()) {
                throw new Exception('Cannot cancel booking for past flight');
            }

            // Update booking status
            $sql = "UPDATE bookings SET booking_status = 'cancelled', 
                    updated_at = CURRENT_TIMESTAMP WHERE booking_id = ?";
            $this->db->query($sql, [$bookingId]);

            // Restore seats to flight
            $flightModel = new Flight();
            $passengerCount = count($booking['passengers']);
            $flightModel->increaseSeats($booking['flight_id'], $passengerCount);

            // Create notification
            $sql = "INSERT INTO notifications (user_id, title, message, type) 
                    VALUES (?, ?, ?, ?)";
            $this->db->query($sql, [
                $booking['user_id'],
                'Booking Cancelled',
                "Your booking {$booking['booking_reference']} has been cancelled.",
                'info',
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Booking cancellation failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== VALIDATION ====================

    /**
     * Validate booking data
     *
     * @param array $data Booking data
     * @param array $passengers Passenger data
     * @return array Validation errors (empty if valid)
     */
    public function validate($data, $passengers)
    {
        $errors = [];

        // Check required fields
        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required';
        }

        if (empty($data['flight_id'])) {
            $errors[] = 'Flight ID is required';
        }

        if (empty($data['total_amount']) || $data['total_amount'] <= 0) {
            $errors[] = 'Valid total amount is required';
        }

        // Validate passengers
        if (empty($passengers) || !is_array($passengers)) {
            $errors[] = 'At least one passenger is required';
        } else {
            foreach ($passengers as $index => $passenger) {
                $passengerNum = $index + 1;

                if (empty($passenger['first_name'])) {
                    $errors[] = "First name is required for passenger {$passengerNum}";
                }

                if (empty($passenger['last_name'])) {
                    $errors[] = "Last name is required for passenger {$passengerNum}";
                }

                // Validate date of birth if provided
                if (!empty($passenger['date_of_birth'])) {
                    $dob = strtotime($passenger['date_of_birth']);
                    if (!$dob || $dob > time()) {
                        $errors[] = "Invalid date of birth for passenger {$passengerNum}";
                    }
                }
            }
        }

        // Check flight availability
        if (empty($errors)) {
            $flightModel = new Flight();
            $seatsNeeded = count($passengers);

            if (!$flightModel->hasAvailableSeats($data['flight_id'], $seatsNeeded)) {
                $errors[] = 'Not enough seats available on this flight';
            }
        }

        return $errors;
    }

    // ==================== STATISTICS ====================

    /**
     * Get booking statistics for user
     *
     * @param int $userId User ID
     * @return array Statistics data
     */
    public function getUserStatistics($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    COALESCE(SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END), 0) as total_spent
                FROM bookings
                WHERE user_id = ?";

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetch();
    }

    /**
     * Count total bookings
     *
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function count($filters = [])
    {
        $sql = 'SELECT COUNT(*) as total FROM bookings WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND booking_status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= ' AND payment_status = ?';
            $params[] = $filters['payment_status'];
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
