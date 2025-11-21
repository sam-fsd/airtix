<?php
/**
 * ============================================================
 * FIXED TICKET VIEW PAGE (public/tickets/view.php)
 * ============================================================
 *
 * Displays all tickets for a booking
 * NO CONTROLLER NEEDED - Direct approach
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Ticket.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

// Get booking_id from URL
$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    $_SESSION['errors'] = ['Booking not found'];
    redirect('public/dashboard/my-bookings.php');
}

// Get booking and verify ownership
$bookingModel = new Booking();
$booking = $bookingModel->findById($bookingId);

if (!$booking) {
    $_SESSION['errors'] = ['Booking not found'];
    redirect('public/dashboard/my-bookings.php');
}

if ($booking['user_id'] != ($_SESSION['user_id'] ?? 2)) {
    $_SESSION['errors'] = ['Unauthorized access'];
    redirect('public/dashboard/my-bookings.php');
}

// Get all tickets for this booking
$ticketModel = new Ticket();
$tickets = $ticketModel->getTicketsByBookingId($bookingId);

if (empty($tickets)) {
    $_SESSION['errors'] = ['No tickets found for this booking'];
    redirect('public/dashboard/my-bookings.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - <?= htmlspecialchars($booking['booking_reference']) ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/ticket.css">
    <style>
        /* Print Styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .ticket-container { 
                page-break-after: always; 
                margin-bottom: 0;
            }
            .ticket-container:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/_sidebar.php'; ?>
    
    <div class="content">
        <main class="ticket-page">
            <div class="page-container">
                <!-- Action Buttons -->
                <div class="ticket-actions no-print">
                    <a href="../dashboard/my-bookings.php" class="btn btn-secondary">← Back to Bookings</a>
                    <button onclick="window.print()" class="btn btn-primary">Print All Tickets</button>
                </div>
                
                <h2 class="no-print" style="margin-bottom: 2rem; text-align: center;">
                    Tickets for Booking <?= htmlspecialchars($booking['booking_reference']) ?>
                </h2>
                
                <?php foreach ($tickets as $index => $ticket): ?>
                    <?php
                    $departureDate = date('l, F j, Y', strtotime($booking['departure_time']));
                    $departureTime = date('H:i', strtotime($booking['departure_time']));
                    $arrivalTime = date('H:i', strtotime($booking['arrival_time']));
                    ?>
                    
                    <div class="ticket-container" style="<?= $index > 0
                        ? 'margin-top: 3rem;'
                        : '' ?>">
                        <div class="ticket">
                            <!-- Ticket Header -->
                            <div class="ticket-header">
                                <h1>AirTix E-Ticket</h1>
                                <div class="ticket-number">Ticket: <?= htmlspecialchars(
                                    $ticket['ticket_number'],
                                ) ?></div>
                            </div>
                            
                            <!-- Ticket Body -->
                            <div class="ticket-body">
                                <!-- Passenger Name -->
                                <div class="passenger-info">
                                    <div class="info-label">Passenger Name</div>
                                    <div class="info-value">
                                        <?= htmlspecialchars($ticket['passenger_first_name']) ?> 
                                        <?= htmlspecialchars($ticket['passenger_last_name']) ?>
                                    </div>
                                </div>
                                
                                <!-- Flight Info -->
                                <div class="flight-info">
                                    <div class="info-item">
                                        <div class="info-label">Flight</div>
                                        <div class="info-value"><?= htmlspecialchars(
                                            $booking['flight_number'],
                                        ) ?></div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-label">Date</div>
                                        <div class="info-value"><?= $departureDate ?></div>
                                    </div>
                                </div>
                                
                                <!-- Route Display -->
                                <div class="route-display">
                                    <div class="route-point">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['origin_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['origin_code'],
                                        ) ?></div>
                                        <div class="time"><?= $departureTime ?></div>
                                        <div class="airport"><?= htmlspecialchars(
                                            $booking['origin_airport'],
                                        ) ?></div>
                                    </div>
                                    
                                    <div class="route-line">
                                        <div class="plane-icon">✈</div>
                                    </div>
                                    
                                    <div class="route-point">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['destination_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['destination_code'],
                                        ) ?></div>
                                        <div class="time"><?= $arrivalTime ?></div>
                                        <div class="airport"><?= htmlspecialchars(
                                            $booking['destination_airport'],
                                        ) ?></div>
                                    </div>
                                </div>
                                
                                <!-- Additional Info -->
                                <div class="additional-info">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Booking Reference</div>
                                            <div class="info-value"><?= htmlspecialchars(
                                                $booking['booking_reference'],
                                            ) ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Seat Number</div>
                                            <div class="info-value"><?= htmlspecialchars(
                                                $ticket['seat_number'] ?? 'TBA',
                                            ) ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Barcode -->
                                <div class="barcode-section">
                                    <div class="barcode">
                                        <svg width="250" height="80" viewBox="0 0 250 80">
                                            <rect x="10" y="10" width="4" height="50" fill="black"/>
                                            <rect x="17" y="10" width="2" height="50" fill="black"/>
                                            <rect x="23" y="10" width="6" height="50" fill="black"/>
                                            <rect x="32" y="10" width="3" height="50" fill="black"/>
                                            <rect x="38" y="10" width="5" height="50" fill="black"/>
                                            <rect x="46" y="10" width="4" height="50" fill="black"/>
                                            <rect x="53" y="10" width="2" height="50" fill="black"/>
                                            <rect x="59" y="10" width="6" height="50" fill="black"/>
                                            <rect x="68" y="10" width="3" height="50" fill="black"/>
                                            <rect x="74" y="10" width="2" height="50" fill="black"/>
                                            <rect x="80" y="10" width="5" height="50" fill="black"/>
                                            <rect x="88" y="10" width="4" height="50" fill="black"/>
                                            <rect x="95" y="10" width="6" height="50" fill="black"/>
                                            <rect x="104" y="10" width="2" height="50" fill="black"/>
                                            <rect x="110" y="10" width="4" height="50" fill="black"/>
                                            <rect x="117" y="10" width="5" height="50" fill="black"/>
                                            <rect x="125" y="10" width="3" height="50" fill="black"/>
                                            <rect x="131" y="10" width="6" height="50" fill="black"/>
                                            <rect x="140" y="10" width="2" height="50" fill="black"/>
                                            <rect x="146" y="10" width="4" height="50" fill="black"/>
                                            <rect x="153" y="10" width="5" height="50" fill="black"/>
                                            <rect x="161" y="10" width="3" height="50" fill="black"/>
                                            <rect x="167" y="10" width="6" height="50" fill="black"/>
                                            <rect x="176" y="10" width="2" height="50" fill="black"/>
                                            <rect x="182" y="10" width="4" height="50" fill="black"/>
                                            <rect x="189" y="10" width="5" height="50" fill="black"/>
                                            <rect x="197" y="10" width="3" height="50" fill="black"/>
                                            <rect x="203" y="10" width="6" height="50" fill="black"/>
                                            <rect x="212" y="10" width="2" height="50" fill="black"/>
                                            <rect x="218" y="10" width="4" height="50" fill="black"/>
                                            <rect x="225" y="10" width="5" height="50" fill="black"/>
                                            <rect x="233" y="10" width="3" height="50" fill="black"/>
                                        </svg>
                                    </div>
                                    <div class="barcode-text"><?= htmlspecialchars(
                                        $ticket['barcode'],
                                    ) ?></div>
                                </div>
                            </div>
                            
                            <!-- Ticket Footer -->
                            <div class="ticket-footer">
                                <p><strong>Important:</strong> Arrive at least 2 hours before departure.</p>
                                <p>Present this ticket at check-in. Valid ID required.</p>
                                <p>Support: support@airtix.com | +254 700 000 000</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>