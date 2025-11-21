<?php
/**
 * ============================================================
 * TICKET MODEL (app/models/Ticket.php)
 * ============================================================
 *
 * Handles ticket generation and management.
 * Tickets are created automatically after successful booking.
 *
 * Responsibilities:
 * - Generate tickets for bookings
 * - Generate PDF tickets
 * - Ticket retrieval and validation
 * - QR/Barcode generation
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class Ticket
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    // ==================== CREATE ====================

    /**
     * Generate tickets for all passengers in a booking
     * Called automatically after booking creation
     *
     * @param int $bookingId Booking ID
     * @return bool Success status
     */
    public function generateTicketsForBooking($bookingId)
    {
        try {
            // Get passengers for this booking
            $sql = 'SELECT * FROM passengers WHERE booking_id = ?';
            $stmt = $this->db->query($sql, [$bookingId]);
            $passengers = $stmt->fetchAll();

            foreach ($passengers as $passenger) {
                $ticketNumber = $this->generateTicketNumber();
                $barcode = $this->generateBarcode($ticketNumber);

                $sql = "INSERT INTO tickets (booking_id, passenger_id, ticket_number, barcode) 
                        VALUES (?, ?, ?, ?)";

                $this->db->query($sql, [
                    $bookingId,
                    $passenger['passenger_id'],
                    $ticketNumber,
                    $barcode,
                ]);
            }

            return true;
        } catch (PDOException $e) {
            error_log('Ticket generation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique ticket number
     *
     * @return string Ticket number
     */
    private function generateTicketNumber()
    {
        do {
            $ticketNumber = 'TK' . strtoupper(substr(uniqid(), -10));

            $stmt = $this->db->query('SELECT ticket_id FROM tickets WHERE ticket_number = ?', [
                $ticketNumber,
            ]);
        } while ($stmt->fetch());

        return $ticketNumber;
    }

    /**
     * Generate barcode for ticket
     * In production, this would generate actual barcode image
     *
     * @param string $ticketNumber Ticket number
     * @return string Barcode string
     */
    private function generateBarcode($ticketNumber)
    {
        return 'BAR' . strtoupper(substr(md5($ticketNumber . time()), 0, 12));
    }

    // ==================== READ ====================

    /**
     * Get ticket by ID with complete booking and flight info
     *
     * @param int $ticketId Ticket ID
     * @return array|false Ticket data
     */
    public function findById($ticketId)
    {
        $sql = "SELECT t.*, 
                       p.first_name as passenger_first_name, 
                       p.last_name as passenger_last_name,
                       p.date_of_birth, p.passport_number, p.seat_number,
                       b.booking_reference, b.total_amount, b.booking_date,
                       f.flight_number, f.departure_time, f.arrival_time,
                       o.city as origin_city, o.airport_code as origin_code,
                       o.airport_name as origin_airport,
                       d.city as destination_city, d.airport_code as destination_code,
                       d.airport_name as destination_airport,
                       u.first_name as user_first_name, u.last_name as user_last_name,
                       u.email as user_email
                FROM tickets t
                JOIN passengers p ON t.passenger_id = p.passenger_id
                JOIN bookings b ON t.booking_id = b.booking_id
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                JOIN users u ON b.user_id = u.user_id
                WHERE t.ticket_id = ?";

        $stmt = $this->db->query($sql, [$ticketId]);
        return $stmt->fetch();
    }

    /**
     * Get ticket by ticket number
     *
     * @param string $ticketNumber Ticket number
     * @return array|false Ticket data
     */
    public function findByTicketNumber($ticketNumber)
    {
        $sql = "SELECT t.*, 
                       p.first_name as passenger_first_name, 
                       p.last_name as passenger_last_name,
                       b.booking_reference
                FROM tickets t
                JOIN passengers p ON t.passenger_id = p.passenger_id
                JOIN bookings b ON t.booking_id = b.booking_id
                WHERE t.ticket_number = ?";

        $stmt = $this->db->query($sql, [$ticketNumber]);
        return $stmt->fetch();
    }

    /**
     * Get all tickets for a booking
     *
     * @param int $bookingId Booking ID
     * @return array Array of tickets
     */
    public function getTicketsByBookingId($bookingId)
    {
        $sql = "SELECT t.*, 
                       p.first_name as passenger_first_name, 
                       p.last_name as passenger_last_name,
                       p.seat_number
                FROM tickets t
                JOIN passengers p ON t.passenger_id = p.passenger_id
                WHERE t.booking_id = ?
                ORDER BY p.passenger_id ASC";

        $stmt = $this->db->query($sql, [$bookingId]);
        return $stmt->fetchAll();
    }

    /**
     * Get tickets for a user
     *
     * @param int $userId User ID
     * @return array Array of tickets
     */
    public function getUserTickets($userId)
    {
        $sql = "SELECT t.*, 
                       p.first_name as passenger_first_name, 
                       p.last_name as passenger_last_name,
                       b.booking_reference,
                       f.flight_number, f.departure_time,
                       o.city as origin_city, d.city as destination_city
                FROM tickets t
                JOIN passengers p ON t.passenger_id = p.passenger_id
                JOIN bookings b ON t.booking_id = b.booking_id
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN destinations o ON f.origin_id = o.destination_id
                JOIN destinations d ON f.destination_id = d.destination_id
                WHERE b.user_id = ?
                ORDER BY f.departure_time DESC";

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    // ==================== PDF GENERATION ====================

    /**
     * Generate PDF ticket
     * Uses simple HTML to PDF conversion (no external libraries needed)
     *
     * @param int $ticketId Ticket ID
     * @return string PDF content or false
     */
    public function generatePDF($ticketId)
    {
        $ticket = $this->findById($ticketId);

        if (!$ticket) {
            return false;
        }

        // Generate HTML for PDF
        $html = $this->generateTicketHTML($ticket);

        // In production, use a library like TCPDF or mPDF
        // For now, we'll return HTML that can be printed
        return $html;
    }

    /**
     * Generate HTML for ticket (print-friendly)
     *
     * @param array $ticket Ticket data
     * @return string HTML content
     */
    private function generateTicketHTML($ticket)
    {
        $departureDate = date('l, F j, Y', strtotime($ticket['departure_time']));
        $departureTime = date('H:i', strtotime($ticket['departure_time']));
        $arrivalTime = date('H:i', strtotime($ticket['arrival_time']));

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ticket - {$ticket['ticket_number']}</title>
            <style>
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
                body {
                    font-family: Arial, sans-serif;
                    max-width: 800px;
                    margin: 20px auto;
                    padding: 20px;
                }
                .ticket {
                    border: 2px solid #2563eb;
                    border-radius: 10px;
                    overflow: hidden;
                }
                .ticket-header {
                    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .ticket-header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .ticket-header .ticket-number {
                    font-size: 16px;
                    margin-top: 10px;
                    opacity: 0.9;
                }
                .ticket-body {
                    padding: 30px;
                    background: white;
                }
                .section {
                    margin-bottom: 25px;
                }
                .section-title {
                    font-size: 12px;
                    color: #64748b;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                .section-content {
                    font-size: 18px;
                    color: #0f172a;
                    font-weight: 500;
                }
                .route {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin: 30px 0;
                    padding: 20px;
                    background: #f8fafc;
                    border-radius: 8px;
                }
                .route-point {
                    text-align: center;
                }
                .route-point .city {
                    font-size: 24px;
                    font-weight: 700;
                    color: #0f172a;
                }
                .route-point .code {
                    font-size: 32px;
                    font-weight: 700;
                    color: #2563eb;
                    margin: 5px 0;
                }
                .route-point .time {
                    font-size: 20px;
                    color: #475569;
                }
                .route-arrow {
                    font-size: 36px;
                    color: #94a3b8;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin: 20px 0;
                }
                .barcode {
                    text-align: center;
                    margin: 30px 0;
                    padding: 20px;
                    background: #f8fafc;
                    border-radius: 8px;
                }
                .barcode-number {
                    font-family: monospace;
                    font-size: 24px;
                    letter-spacing: 2px;
                    margin-top: 10px;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    background: #f8fafc;
                    color: #64748b;
                    font-size: 12px;
                }
                .print-button {
                    background: #2563eb;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    margin: 20px auto;
                    display: block;
                }
                .print-button:hover {
                    background: #1e40af;
                }
            </style>
        </head>
        <body>
            <button class="print-button no-print" onclick="window.print()">Print Ticket</button>

            <div class="ticket">
                <div class="ticket-header">
                    <h1>AirTix E-Ticket</h1>
                    <div class="ticket-number">Ticket Number: {$ticket['ticket_number']}</div>
                </div>

                <div class="ticket-body">
                    <div class="section">
                        <div class="section-title">Passenger Name</div>
                        <div class="section-content">{$ticket['passenger_first_name']} {$ticket['passenger_last_name']}</div>
                    </div>

                    <div class="section">
                        <div class="section-title">Flight</div>
                        <div class="section-content">{$ticket['flight_number']}</div>
                    </div>

                    <div class="section">
                        <div class="section-title">Date</div>
                        <div class="section-content">{$departureDate}</div>
                    </div>

                    <div class="route">
                        <div class="route-point">
                            <div class="city">{$ticket['origin_city']}</div>
                            <div class="code">{$ticket['origin_code']}</div>
                            <div class="time">{$departureTime}</div>
                        </div>

                        <div class="route-arrow">→</div>

                        <div class="route-point">
                            <div class="city">{$ticket['destination_city']}</div>
                            <div class="code">{$ticket['destination_code']}</div>
                            <div class="time">{$arrivalTime}</div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="section">
                            <div class="section-title">Booking Reference</div>
                            <div class="section-content">{$ticket['booking_reference']}</div>
                        </div>

                        <div class="section">
                            <div class="section-title">Seat Number</div>
                            <div class="section-content">{$ticket['seat_number']}</div>
                        </div>

                        <div class="section">
                            <div class="section-title">Departure Airport</div>
                            <div class="section-content">{$ticket['origin_airport']}</div>
                        </div>

                        <div class="section">
                            <div class="section-title">Arrival Airport</div>
                            <div class="section-content">{$ticket['destination_airport']}</div>
                        </div>
                    </div>

                    <div class="barcode">
                        <svg width="200" height="60" style="margin: 0 auto;">
                            <rect x="0" y="0" width="200" height="60" fill="white"/>
                            <rect x="10" y="10" width="3" height="40" fill="black"/>
                            <rect x="15" y="10" width="2" height="40" fill="black"/>
                            <rect x="20" y="10" width="5" height="40" fill="black"/>
                            <rect x="28" y="10" width="2" height="40" fill="black"/>
                            <rect x="33" y="10" width="4" height="40" fill="black"/>
                            <rect x="40" y="10" width="3" height="40" fill="black"/>
                            <rect x="46" y="10" width="2" height="40" fill="black"/>
                            <rect x="51" y="10" width="5" height="40" fill="black"/>
                            <rect x="59" y="10" width="3" height="40" fill="black"/>
                            <rect x="65" y="10" width="2" height="40" fill="black"/>
                            <rect x="70" y="10" width="4" height="40" fill="black"/>
                            <rect x="77" y="10" width="3" height="40" fill="black"/>
                            <rect x="83" y="10" width="5" height="40" fill="black"/>
                            <rect x="91" y="10" width="2" height="40" fill="black"/>
                            <rect x="96" y="10" width="3" height="40" fill="black"/>
                            <rect x="102" y="10" width="4" height="40" fill="black"/>
                            <rect x="109" y="10" width="2" height="40" fill="black"/>
                            <rect x="114" y="10" width="5" height="40" fill="black"/>
                            <rect x="122" y="10" width="3" height="40" fill="black"/>
                            <rect x="128" y="10" width="2" height="40" fill="black"/>
                            <rect x="133" y="10" width="4" height="40" fill="black"/>
                            <rect x="140" y="10" width="3" height="40" fill="black"/>
                            <rect x="146" y="10" width="5" height="40" fill="black"/>
                            <rect x="154" y="10" width="2" height="40" fill="black"/>
                            <rect x="159" y="10" width="3" height="40" fill="black"/>
                            <rect x="165" y="10" width="4" height="40" fill="black"/>
                            <rect x="172" y="10" width="2" height="40" fill="black"/>
                            <rect x="177" y="10" width="5" height="40" fill="black"/>
                            <rect x="185" y="10" width="3" height="40" fill="black"/>
                        </svg>
                        <div class="barcode-number">{$ticket['barcode']}</div>
                    </div>
                </div>

                <div class="footer">
                    <p>Please arrive at the airport at least 2 hours before departure.</p>
                    <p>This is an electronic ticket. Please present this ticket at check-in.</p>
                    <p>For assistance, contact support@airtix.com</p>
                </div>
            </div>

            <button class="print-button no-print" onclick="window.print()">Print Ticket</button>
        </body>
        </html>
        HTML;

        return $html;
    }
}
?>
