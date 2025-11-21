<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Flight.php';
require_once __DIR__ . '/../../app/models/Seat.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

$flightId = $_GET['flight_id'] ?? null;
$passengers = $_GET['passengers'] ?? 1;
$selectedSeatsParam = $_GET['selected_seats'] ?? '';

if (!$flightId) {
    $_SESSION['errors'] = ['Flight not found'];
    redirect('public/flights/search.php');
}

// Parse selected seats
$selectedSeats = array_filter(explode(',', $selectedSeatsParam));

if (count($selectedSeats) !== (int) $passengers) {
    $_SESSION['errors'] = ['Invalid seat selection'];
    redirect(
        'public/booking/seat_selection.php?flight_id=' . $flightId . '&passengers=' . $passengers,
    );
    exit();
}

// Verify seats are still available
$seatModel = new Seat();
if (!$seatModel->areSeatsAvailable($flightId, $selectedSeats)) {
    $_SESSION['errors'] = ['One or more selected seats are no longer available'];
    redirect(
        'public/booking/seat_selection.php?flight_id=' . $flightId . '&passengers=' . $passengers,
    );
}

// Get flight details
$flightModel = new Flight();
$flight = $flightModel->findById($flightId);

if (!$flight) {
    $_SESSION['errors'] = ['Flight not found'];
    redirect('public/flights/search.php');
}

// Calculate price
$pricePerPerson = $flight['price'];
$totalPrice = $pricePerPerson * $passengers;

// Get errors and old input
$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

unset($_SESSION['errors']);
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Information - AirTix</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/seats.css">
    <link rel="stylesheet" href="../assets/css/passenger.css">
</head>
<body>
  <?php include __DIR__ . '/../dashboard/_sidebar.php'; ?>

<div class="content">
<main class="passenger-info-page">

    <div class="page-container">

        <!-- Steps -->
        <div class="booking-steps">
            <div class="step completed"><div class="step-number">1</div><span>Select Flight</span></div>
            <div class="step completed"><div class="step-number">2</div><span>Select Seats</span></div>
            <div class="step active"><div class="step-number">3</div><span>Passenger Info</span></div>
            <div class="step"><div class="step-number">4</div><span>Confirmation</span></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="passenger-layout">

            <!-- LEFT SIDE — Passenger Form -->
            <div class="passenger-form-section">
                <h2>Passenger Information</h2>
                <p class="muted">
                    Provide details for <?= $passengers ?> passenger<?= $passengers > 1
     ? 's'
     : '' ?>.
                </p>

                <form action="../../app/controllers/BookingController.php" method="POST">

                    <input type="hidden" name="flight_id" value="<?= $flightId ?>">
                    <input type="hidden" name="passenger_count" value="<?= $passengers ?>">
                    <input type="hidden" name="total_amount" value="<?= $totalPrice ?>">

                    <?php for ($i = 0; $i < $passengers; $i++): ?>
                        <div class="passenger-card">

                            <div class="card-title-bar">
                                <h3>Passenger <?= $i + 1 ?></h3>
                                <div class="seat-badge">Seat <?= htmlspecialchars(
                                    $selectedSeats[$i],
                                ) ?></div>
                            </div>

                            <input type="hidden" name="passenger_seat_<?= $i ?>" value="<?= htmlspecialchars(
    $selectedSeats[$i],
) ?>">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="passenger_first_name_<?= $i ?>" required
                                           value="<?= htmlspecialchars(
                                               $oldInput["passenger_first_name_{$i}"] ?? '',
                                           ) ?>">
                                </div>

                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="passenger_last_name_<?= $i ?>" required
                                           value="<?= htmlspecialchars(
                                               $oldInput["passenger_last_name_{$i}"] ?? '',
                                           ) ?>">
                                </div>

                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="passenger_dob_<?= $i ?>" max="<?= date(
    'Y-m-d',
) ?>"
                                           value="<?= htmlspecialchars(
                                               $oldInput["passenger_dob_{$i}"] ?? '',
                                           ) ?>">
                                </div>

                                <div class="form-group">
                                    <label>Passport Number</label>
                                    <input type="text" name="passenger_passport_<?= $i ?>"
                                           value="<?= htmlspecialchars(
                                               $oldInput["passenger_passport_{$i}"] ?? '',
                                           ) ?>">
                                </div>

                                <div class="form-group">
                                    <label>Nationality</label>
                                    <input type="text" name="passenger_nationality_<?= $i ?>"
                                           value="<?= htmlspecialchars(
                                               $oldInput["passenger_nationality_{$i}"] ?? 'Kenyan',
                                           ) ?>">
                                </div>
                            </div>

                        </div>
                    <?php endfor; ?>

                    <div class="action-buttons">
                        <a href="seat_selection.php?flight_id=<?= $flightId ?>&passengers=<?= $passengers ?>" 
                           class="btn btn-secondary">← Back</a>

                        <button type="submit" class="btn btn-primary btn-large">
                            Continue to Payment
                        </button>
                    </div>

                </form>
            </div>

            <!-- RIGHT SIDE — Summary -->
            <aside class="booking-summary passenger-summary">
                
                <h3>Booking Summary</h3>

                <div class="summary-item"><span>Flight</span><strong><?= $flight[
                    'flight_number'
                ] ?></strong></div>
                <div class="summary-item"><span>Route</span>
                    <strong><?= $flight['origin_code'] ?> → <?= $flight[
     'destination_code'
 ] ?></strong></div>
                <div class="summary-item"><span>Date</span><strong><?= date(
                    'd M Y',
                    strtotime($flight['departure_time']),
                ) ?></strong></div>
                <div class="summary-item"><span>Departure</span>
                    <strong><?= date('H:i', strtotime($flight['departure_time'])) ?></strong></div>
                <div class="summary-item"><span>Passengers</span><strong><?= $passengers ?></strong></div>

                <div class="seat-list-box">
                    <h4>Selected Seats</h4>
                    <?php foreach ($selectedSeats as $idx => $s): ?>
                        <div class="summary-item">
                            <span>Passenger <?= $idx + 1 ?></span>
                            <strong class="seat-highlight"><?= $s ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-item"><span>Price per person</span>
                    <strong>KES <?= number_format($pricePerPerson, 2) ?></strong></div>

                <div class="summary-item total"><span>Total Amount</span>
                    <strong>KES <?= number_format($totalPrice, 2) ?></strong></div>

            </aside>
        </div>

    </div>
</main>
</div>

    
</body>
</html>