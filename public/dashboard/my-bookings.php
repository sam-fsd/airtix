<?php
/**
 * ============================================================
 * MY BOOKINGS PAGE (public/user/my-bookings.php)
 * ============================================================
 *
 * Displays all user bookings (upcoming and past)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/models/Ticket.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

$bookingModel = new Booking();
$ticketModel = new Ticket();
$userId = $_SESSION['user_id'];

// Get bookings
$upcomingBookings = $bookingModel->getUpcomingBookings($userId);
$pastBookings = $bookingModel->getPastBookings($userId);

// Get stats
$stats = $bookingModel->getUserStatistics($userId);

// Get messages
$success = $_SESSION['success'] ?? null;
$errors = $_SESSION['errors'] ?? [];

unset($_SESSION['success']);
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - AirTix</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="dashboard-page">
        <div class="container">
            <div class="dashboard-header">
                <h1>My Bookings</h1>
                <a href="../flights/index.php" class="btn btn-primary">Book New Flight</a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= count($upcomingBookings) ?></div>
                    <div class="stat-label">Upcoming Trips</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">KES <?= number_format($stats['total_spent'], 2) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            
            <!-- Upcoming Bookings -->
            <section class="bookings-section">
                <h2>Upcoming Trips</h2>
                
                <?php if (empty($upcomingBookings)): ?>
                    <div class="empty-state">
                        <p>No upcoming bookings</p>
                        <a href="../flights/search.php" class="btn btn-primary">Search Flights</a>
                    </div>
                <?php else: ?>
                    <div class="bookings-grid">
                        <?php foreach ($upcomingBookings as $booking): ?>
                            <?php
                            $tickets = $ticketModel->getTicketsByBookingId($booking['booking_id']);
                            $passengerCount = count($tickets);
                            $daysUntil = ceil(
                                (strtotime($booking['departure_time']) - time()) / 86400,
                            );
                            ?>
                            
                            <div class="booking-card">
                                <div class="booking-header">
                                    <span class="booking-ref"><?= htmlspecialchars(
                                        $booking['booking_reference'],
                                    ) ?></span>
                                    <span class="booking-status status-<?= $booking[
                                        'booking_status'
                                    ] ?>">
                                        <?= ucfirst($booking['booking_status']) ?>
                                    </span>
                                </div>
                                
                                <div class="booking-route">
                                    <div class="route-info">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['origin_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['origin_code'],
                                        ) ?></div>
                                    </div>
                                    
                                    <div class="route-arrow">→</div>
                                    
                                    <div class="route-info">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['destination_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['destination_code'],
                                        ) ?></div>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="label">Flight</span>
                                        <span class="value"><?= htmlspecialchars(
                                            $booking['flight_number'],
                                        ) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="label">Date</span>
                                        <span class="value"><?= date(
                                            'd M Y',
                                            strtotime($booking['departure_time']),
                                        ) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="label">Time</span>
                                        <span class="value"><?= date(
                                            'H:i',
                                            strtotime($booking['departure_time']),
                                        ) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="label">Passengers</span>
                                        <span class="value"><?= $passengerCount ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($daysUntil <= 7): ?>
                                    <div class="booking-alert">
                                        ⏰ Departing in <?= $daysUntil ?> day<?= $daysUntil != 1
     ? 's'
     : '' ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="booking-actions">
                                    <a href="../tickets/view.php?booking_id=<?= $booking[
                                        'booking_id'
                                    ] ?>" 
                                       class="btn btn-primary btn-sm">
                                        View Tickets
                                    </a>
                                    
                                    <?php if ($daysUntil > 2): ?>
                                        <button onclick="cancelBooking(<?= $booking[
                                            'booking_id'
                                        ] ?>, '<?= htmlspecialchars(
    $booking['booking_reference'],
) ?>')" 
                                                class="btn btn-secondary btn-sm">
                                            Cancel Booking
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Past Bookings -->
            <?php if (!empty($pastBookings)): ?>
                <section class="bookings-section">
                    <h2>Past Trips</h2>
                    
                    <div class="bookings-grid">
                        <?php foreach ($pastBookings as $booking): ?>
                            <?php $tickets = $ticketModel->getTicketsByBookingId(
                                $booking['booking_id'],
                            ); ?>
                            
                            <div class="booking-card past">
                                <div class="booking-header">
                                    <span class="booking-ref"><?= htmlspecialchars(
                                        $booking['booking_reference'],
                                    ) ?></span>
                                    <span class="past-label">Completed</span>
                                </div>
                                
                                <div class="booking-route">
                                    <div class="route-info">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['origin_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['origin_code'],
                                        ) ?></div>
                                    </div>
                                    
                                    <div class="route-arrow">→</div>
                                    
                                    <div class="route-info">
                                        <div class="city"><?= htmlspecialchars(
                                            $booking['destination_city'],
                                        ) ?></div>
                                        <div class="code"><?= htmlspecialchars(
                                            $booking['destination_code'],
                                        ) ?></div>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="label">Flight</span>
                                        <span class="value"><?= htmlspecialchars(
                                            $booking['flight_number'],
                                        ) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="label">Date</span>
                                        <span class="value"><?= date(
                                            'd M Y',
                                            strtotime($booking['departure_time']),
                                        ) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="label">Passengers</span>
                                        <span class="value"><?= count($tickets) ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-actions">
                                    <a href="../tickets/view.php?booking_id=<?= $booking[
                                        'booking_id'
                                    ] ?>" 
                                       class="btn btn-secondary btn-sm">
                                        View Tickets
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Cancel Booking Modal -->
    <div id="cancelModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Cancel Booking?</h3>
            <p>Are you sure you want to cancel booking <strong id="cancelBookingRef"></strong>?</p>
            <p style="color: var(--text-secondary);">This action cannot be undone.</p>
            
            <form id="cancelForm" action="../../app/controllers/BookingCancelController.php" method="POST">
                <input type="hidden" name="booking_id" id="cancelBookingId">
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1;">
                        Keep Booking
                    </button>
                    <button type="submit" class="btn btn-primary" style="flex: 1; background-color: var(--error-color);">
                        Yes, Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/bookings.js"></script>
</body>
</html>