<!-- ============================================================
     BOOKING CONFIRMATION PAGE (public/booking/confirmation.php)
     ============================================================ -->
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

// Check if booking was successful
if (!isset($_SESSION['booking_success'])) {
    redirect('public/flights/search.php');
}

$bookingData = $_SESSION['booking_success'];
unset($_SESSION['booking_success']);

// Get full booking details
$bookingModel = new Booking();
$booking = $bookingModel->findById($bookingData['booking_id']);

if (!$booking) {
    $_SESSION['errors'] = ['Booking not found'];
    redirect('public/flights/search.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - AirTix</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/confirmation.css">

</head>
<body>
   
<?php include __DIR__ . '/../dashboard/_sidebar.php'; ?>

<div class="content">
<main class="confirmation-page">

    <div class="confirmation-container">

        <div class="success-icon">✓</div>

        <h1>Booking Confirmed!</h1>
        <p class="subtitle">
            Your flight has been successfully booked. Your tickets are ready.
        </p>

        <div class="booking-ref">
            <?= htmlspecialchars($booking['booking_reference']) ?>
        </div>

        <!-- Flight Details Box -->
        <div class="confirmation-details">

            <h3 class="section-title">Flight Details</h3>

            <div class="info-grid">
                <div class="info-block">
                    <div class="info-label">Flight</div>
                    <div class="info-value big">
                        <?= htmlspecialchars($booking['flight_number']) ?>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-label">Route</div>
                    <div class="info-value big">
                        <?= htmlspecialchars($booking['origin_city']) ?> →
                        <?= htmlspecialchars($booking['destination_city']) ?>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-label">Date</div>
                    <div class="info-value big">
                        <?= date('d M Y', strtotime($booking['departure_time'])) ?>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-label">Passengers</div>
                    <div class="info-value big">
                        <?= count($booking['passengers']) ?>
                    </div>
                </div>
            </div>

            <div class="email-note">
                Confirmation email sent to:
                <strong><?= htmlspecialchars($booking['user_email']) ?></strong>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="../tickets/view.php?booking_id=<?= $booking['booking_id'] ?>" 
                   class="btn btn-primary btn-large">
                    View Tickets
            </a>

            <a href="../dashboard/my-bookings.php" class="btn btn-secondary">
                View All Bookings
            </a>

            <a href="../flights/search.php" class="btn btn-secondary">
                Book Another Flight
            </a>
        </div>

    </div>

</main>
</div>
</body>
</html>