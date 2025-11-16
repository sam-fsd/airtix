<?php
/**
 * ============================================================
 * FLIGHT MODEL (lib/Flight.php)
 * ============================================================
 *
 * Handles all database operations related to flights.
 * Responsibilities:
 * - CRUD operations for flights
 * - Flight search and filtering
 * - Availability management
 * - Flight validation
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class Flight
{
    private $db;
    private $conn;

    // Properties matching database columns
    public $flight_id;
    public $flight_number;
    public $aircraft_id;
    public $origin_id;
    public $destination_id;
    public $departure_time;
    public $arrival_time;
    public $price;
    public $available_seats;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    // ==================== CREATE ====================

    /**
     * Create a new flight
     *
     * @param array $data Flight data
     * @return int|false Flight ID if successful, false on failure
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO flights 
                    (flight_number, aircraft_id, origin_id, destination_id, 
                     departure_time, arrival_time, price, available_seats, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->query($sql, [
                $data['flight_number'],
                $data['aircraft_id'],
                $data['origin_id'],
                $data['destination_id'],
                $data['departure_time'],
                $data['arrival_time'],
                $data['price'],
                $data['available_seats'],
                $data['status'] ?? 'scheduled',
            ]);

            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('Flight creation failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== READ ====================

    /**
     * Find flight by ID with complete information
     *
     * @param int $id Flight ID
     * @return array|false Flight data or false if not found
     */
    public function findById($id)
    {
        $sql = "SELECT f.*, 
                       o.city as origin_city, o.airport_code as origin_code, 
                       o.airport_name as origin_airport,
                       d.city as destination_city, d.airport_code as destination_code,
                       d.airport_name as destination_airport,
                       a.model as aircraft_model, a.manufacturer as aircraft_manufacturer,
                       a.total_seats
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN aircraft a ON f.aircraft_id = a.aircraft_id
                WHERE f.flight_id = ?";

        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * Find flight by flight number
     *
     * @param string $flightNumber Flight number
     * @return array|false Flight data or false if not found
     */
    public function findByFlightNumber($flightNumber)
    {
        $sql = "SELECT f.*, 
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE f.flight_number = ?";

        $stmt = $this->db->query($sql, [$flightNumber]);
        return $stmt->fetch();
    }

    /**
     * Search flights with filters
     *
     * @param array $filters Search criteria (origin_id, destination_id, date, etc.)
     * @return array Array of flights
     */
    public function search($filters = [])
    {
        $sql = "SELECT f.*, 
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code,
                       a.model as aircraft_model
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN aircraft a ON f.aircraft_id = a.aircraft_id
                WHERE 1=1";

        $params = [];

        // Origin filter
        if (!empty($filters['origin_id'])) {
            $sql .= ' AND f.origin_id = ?';
            $params[] = $filters['origin_id'];
        }

        // Destination filter
        if (!empty($filters['destination_id'])) {
            $sql .= ' AND f.destination_id = ?';
            $params[] = $filters['destination_id'];
        }

        // Date filter
        if (!empty($filters['date'])) {
            $sql .= ' AND DATE(f.departure_time) = ?';
            $params[] = $filters['date'];
        }

        // Date range filter (from date to future)
        if (!empty($filters['from_date'])) {
            $sql .= ' AND DATE(f.departure_time) >= ?';
            $params[] = $filters['from_date'];
        }

        // Status filter (default to scheduled flights)
        if (isset($filters['status'])) {
            $sql .= ' AND f.status = ?';
            $params[] = $filters['status'];
        } else {
            $sql .= " AND f.status IN ('scheduled', 'boarding')";
        }

        // Available seats filter
        if (isset($filters['min_seats'])) {
            $sql .= ' AND f.available_seats >= ?';
            $params[] = $filters['min_seats'];
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $sql .= ' AND f.price >= ?';
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= ' AND f.price <= ?';
            $params[] = $filters['max_price'];
        }

        // Time of day filter
        if (!empty($filters['time_of_day'])) {
            switch ($filters['time_of_day']) {
                case 'morning':
                    $sql .= ' AND HOUR(f.departure_time) >= 6 AND HOUR(f.departure_time) < 12';
                    break;
                case 'afternoon':
                    $sql .= ' AND HOUR(f.departure_time) >= 12 AND HOUR(f.departure_time) < 18';
                    break;
                case 'evening':
                    $sql .= ' AND HOUR(f.departure_time) >= 18 AND HOUR(f.departure_time) < 24';
                    break;
            }
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'departure_time';
        $sortOrder = $filters['sort_order'] ?? 'ASC';

        $allowedSorts = ['departure_time', 'price', 'arrival_time'];
        if (in_array($sortBy, $allowedSorts)) {
            $sql .= " ORDER BY f.{$sortBy} {$sortOrder}";
        } else {
            $sql .= ' ORDER BY f.departure_time ASC';
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get all flights with pagination
     *
     * @param int $limit Number of flights to return
     * @param int $offset Starting point
     * @return array Array of flights
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT f.*, 
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code,
                       a.model as aircraft_model
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN aircraft a ON f.aircraft_id = a.aircraft_id
                ORDER BY f.departure_time DESC";

        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            $stmt = $this->db->query($sql, [$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get upcoming flights (future flights only)
     *
     * @param int $limit Number of flights to return
     * @return array Array of upcoming flights
     */
    public function getUpcoming($limit = 10)
    {
        $sql = "SELECT f.*, 
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE f.departure_time > NOW()
                  AND f.status = 'scheduled'
                ORDER BY f.departure_time ASC
                LIMIT ?";

        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get popular routes (most booked)
     *
     * @param int $limit Number of routes to return
     * @return array Array of popular routes
     */
    public function getPopularRoutes($limit = 5)
    {
        $sql = "SELECT f.origin_id, f.destination_id,
                       o.city as origin_city, o.airport_code as origin_code,
                       d.city as destination_city, d.airport_code as destination_code,
                       COUNT(b.booking_id) as booking_count,
                       MIN(f.price) as min_price
                FROM flights f
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                LEFT JOIN bookings b ON f.flight_id = b.flight_id
                WHERE f.departure_time > NOW()
                GROUP BY f.origin_id, f.destination_id
                ORDER BY booking_count DESC
                LIMIT ?";

        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get total count of flights
     *
     * @param array $filters Optional filters
     * @return int Total number of flights
     */
    public function count($filters = [])
    {
        $sql = 'SELECT COUNT(*) as total FROM flights WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= ' AND DATE(departure_time) >= ?';
            $params[] = $filters['from_date'];
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    // ==================== UPDATE ====================

    /**
     * Update flight information
     *
     * @param int $id Flight ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE flights SET 
                    flight_number = ?,
                    aircraft_id = ?,
                    origin_id = ?,
                    destination_id = ?,
                    departure_time = ?,
                    arrival_time = ?,
                    price = ?,
                    available_seats = ?,
                    status = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE flight_id = ?";

            $stmt = $this->db->query($sql, [
                $data['flight_number'],
                $data['aircraft_id'],
                $data['origin_id'],
                $data['destination_id'],
                $data['departure_time'],
                $data['arrival_time'],
                $data['price'],
                $data['available_seats'],
                $data['status'],
                $id,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Flight update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update flight status
     *
     * @param int $id Flight ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status)
    {
        try {
            $sql = "UPDATE flights SET status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE flight_id = ?";
            $stmt = $this->db->query($sql, [$status, $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Status update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrease available seats (when booking is made)
     *
     * @param int $id Flight ID
     * @param int $seats Number of seats to decrease
     * @return bool Success status
     */
    public function decreaseSeats($id, $seats = 1)
    {
        try {
            $sql = "UPDATE flights 
                    SET available_seats = available_seats - ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE flight_id = ? 
                      AND available_seats >= ?";

            $stmt = $this->db->query($sql, [$seats, $id, $seats]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Seat decrease failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Increase available seats (when booking is cancelled)
     *
     * @param int $id Flight ID
     * @param int $seats Number of seats to increase
     * @return bool Success status
     */
    public function increaseSeats($id, $seats = 1)
    {
        try {
            $sql = "UPDATE flights 
                    SET available_seats = available_seats + ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE flight_id = ?";

            $stmt = $this->db->query($sql, [$seats, $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Seat increase failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== DELETE ====================

    /**
     * Delete flight
     *
     * @param int $id Flight ID
     * @return bool Success status
     */
    public function delete($id)
    {
        try {
            // Check if flight has bookings
            $sql = 'SELECT COUNT(*) as count FROM bookings WHERE flight_id = ?';
            $stmt = $this->db->query($sql, [$id]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                // Don't delete if there are bookings
                return false;
            }

            $sql = 'DELETE FROM flights WHERE flight_id = ?';
            $stmt = $this->db->query($sql, [$id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Flight deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== VALIDATION ====================

    /**
     * Validate flight data
     *
     * @param array $data Flight data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validate($data)
    {
        $errors = [];

        // Flight number
        if (empty($data['flight_number'])) {
            $errors[] = 'Flight number is required';
        }

        // Aircraft
        if (empty($data['aircraft_id'])) {
            $errors[] = 'Aircraft is required';
        }

        // Origin and destination
        if (empty($data['origin_id'])) {
            $errors[] = 'Origin is required';
        }

        if (empty($data['destination_id'])) {
            $errors[] = 'Destination is required';
        }

        if (!empty($data['origin_id']) && !empty($data['destination_id'])) {
            if ($data['origin_id'] == $data['destination_id']) {
                $errors[] = 'Origin and destination cannot be the same';
            }
        }

        // Times
        if (empty($data['departure_time'])) {
            $errors[] = 'Departure time is required';
        }

        if (empty($data['arrival_time'])) {
            $errors[] = 'Arrival time is required';
        }

        if (!empty($data['departure_time']) && !empty($data['arrival_time'])) {
            if (strtotime($data['arrival_time']) <= strtotime($data['departure_time'])) {
                $errors[] = 'Arrival time must be after departure time';
            }
        }

        // Price
        if (empty($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Valid price is required';
        }

        // Available seats
        if (!isset($data['available_seats']) || $data['available_seats'] < 0) {
            $errors[] = 'Valid number of available seats is required';
        }

        return $errors;
    }

    /**
     * Check if seats are available
     *
     * @param int $flightId Flight ID
     * @param int $requiredSeats Number of seats needed
     * @return bool True if available, false otherwise
     */
    public function hasAvailableSeats($flightId, $requiredSeats = 1)
    {
        $sql = 'SELECT available_seats FROM flights WHERE flight_id = ?';
        $stmt = $this->db->query($sql, [$flightId]);
        $flight = $stmt->fetch();

        return $flight && $flight['available_seats'] >= $requiredSeats;
    }

    /**
     * Check if flight number exists
     *
     * @param string $flightNumber Flight number to check
     * @param int $excludeId Flight ID to exclude (for updates)
     * @return bool True if exists, false otherwise
     */
    public function flightNumberExists($flightNumber, $excludeId = null)
    {
        $sql = 'SELECT flight_id FROM flights WHERE flight_number = ?';
        $params = [$flightNumber];

        if ($excludeId) {
            $sql .= ' AND flight_id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch() !== false;
    }

    // ==================== STATISTICS ====================

    /**
     * Get flight statistics
     *
     * @param int $flightId Flight ID
     * @return array Statistics data
     */
    public function getStatistics($flightId)
    {
        $sql = "SELECT 
                    f.available_seats,
                    f.price,
                    a.total_seats,
                    (a.total_seats - f.available_seats) as booked_seats,
                    ROUND(((a.total_seats - f.available_seats) / a.total_seats) * 100, 2) as occupancy_rate,
                    COUNT(DISTINCT b.booking_id) as total_bookings,
                    COUNT(p.passenger_id) as total_passengers,
                    COALESCE(SUM(b.total_amount), 0) as total_revenue
                FROM flights f
                JOIN aircraft a ON f.aircraft_id = a.aircraft_id
                LEFT JOIN bookings b ON f.flight_id = b.flight_id 
                    AND b.booking_status = 'confirmed'
                LEFT JOIN passengers p ON b.booking_id = p.booking_id
                WHERE f.flight_id = ?
                GROUP BY f.flight_id";

        $stmt = $this->db->query($sql, [$flightId]);
        return $stmt->fetch();
    }
}

?>
