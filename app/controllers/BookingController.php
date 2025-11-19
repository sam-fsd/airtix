
<?php
/**
 * ============================================================
 * BOOKING CREATE CONTROLLER (app/controllers/BookingCreateController.php)
 * ============================================================
 *
 * Handles the booking creation process
 * This is called after user fills passenger info and confirms payment
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Flight.php';
require_once __DIR__ . '/../helpers/functions.php';

session_start();

// Ensure user is logged in
// requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingModel = new Booking();
    $ticketModel = new Ticket();

    // Get booking data from form
    $flightId = $_POST['flight_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? 2;
    $totalAmount = $_POST['total_amount'] ?? 0;

    // Get passengers data (array)
    $passengers = [];
    $passengerCount = $_POST['passenger_count'] ?? 0;

    for ($i = 0; $i < $passengerCount; $i++) {
        $passengers[] = [
            'first_name' => clean($_POST["passenger_first_name_{$i}"] ?? ''),
            'last_name' => clean($_POST["passenger_last_name_{$i}"] ?? ''),
            'date_of_birth' => $_POST["passenger_dob_{$i}"] ?? null,
            'passport_number' => clean($_POST["passenger_passport_{$i}"] ?? ''),
            'nationality' => clean($_POST["passenger_nationality_{$i}"] ?? 'Kenyan'),
            'seat_number' => clean($_POST["passenger_seat_{$i}"] ?? null),
        ];
    }

    // Prepare booking data
    $bookingData = [
        'user_id' => $userId,
        'flight_id' => $flightId,
        'total_amount' => $totalAmount,
        'payment_status' => 'completed', // Simulated payment
    ];

    // Validate
    $errors = $bookingModel->validate($bookingData, $passengers);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        redirect('public/booking/passenger_info.php?flight_id=' . $flightId);
    }
    // Create booking (this handles transaction)
    $result = $bookingModel->createBooking($bookingData, $passengers);

    if ($result) {
        $bookingId = $result['booking_id'];
        $bookingReference = $result['booking_reference'];

        // Generate tickets for all passengers
        $ticketModel->generateTicketsForBooking($bookingId);

        // Store booking info in session for confirmation page
        $_SESSION['booking_success'] = [
            'booking_id' => $bookingId,
            'booking_reference' => $bookingReference,
        ];

        // Redirect to confirmation page
        redirect('public/booking/confirmation.php');
    } else {
        $_SESSION['errors'] = ['Booking failed. Please try again.'];
        redirect('public/booking/passenger_info.php?flight_id=' . $flightId);
    }
}

// If not POST, redirect to flights
redirect('public/flights/search.php');
?>
<script>
console.log(<?= $errors ?>)
</script>

