<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Flight.php';

session_start();

$flightId = $_GET['id'] ?? null;
if (!$flightId) {
    redirect('public/flights/search.php');
}

$flightModel = new Flight();
$flight = $flightModel->findById($flightId);

if (!$flight) {
    redirect('public/flights/search.php');
}

$stats = $flightModel->getStatistics($flightId);

$departure = strtotime($flight['departure_time']);
$arrival = strtotime($flight['arrival_time']);
$duration = ($arrival - $departure) / 3600;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight <?= htmlspecialchars($flight['flight_number']) ?> - AirTix</title>

    <!-- global -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

    <!-- page-specific -->
    <link rel="stylesheet" href="../assets/css/flight-details.css">
</head>

<body>

<?php include __DIR__ . '/../dashboard/_sidebar.php'; ?>

<div class="content">
<main class="flight-details-page">
    
    <div class="page-container">

        <a href="javascript:history.back()" class="back-link">← Back to Results</a>

        <div class="flight-details-card">

            <!-- Header -->
            <div class="flight-header">
                <h1>Flight <?= htmlspecialchars($flight['flight_number']) ?></h1>
                <span class="status-pill status-<?= strtolower($flight['status']) ?>">
                    <?= ucfirst($flight['status']) ?>
                </span>
            </div>

            <!-- Route Visual -->
            <div class="route-visual">

                <div class="route-point">
                    <h3><?= htmlspecialchars($flight['origin_city']) ?></h3>
                    <p class="airport"><?= htmlspecialchars($flight['origin_airport']) ?></p>
                    <p class="code"><?= htmlspecialchars($flight['origin_code']) ?></p>
                    <p class="time"><?= date('H:i', $departure) ?></p>
                    <p class="date"><?= date('D, d M Y', $departure) ?></p>
                </div>

                <div class="route-mid">
                    <div class="duration-line"></div>
                    <p class="duration-text"><?= number_format($duration, 1) ?> hrs</p>
                </div>

                <div class="route-point">
                    <h3><?= htmlspecialchars($flight['destination_city']) ?></h3>
                    <p class="airport"><?= htmlspecialchars($flight['destination_airport']) ?></p>
                    <p class="code"><?= htmlspecialchars($flight['destination_code']) ?></p>
                    <p class="time"><?= date('H:i', $arrival) ?></p>
                    <p class="date"><?= date('D, d M Y', $arrival) ?></p>
                </div>

            </div>

            <!-- Info Grid -->
            <div class="info-grid">

                <div class="info-card">
                    <h4>Aircraft</h4>
                    <p><?= htmlspecialchars($flight['aircraft_manufacturer']) ?></p>
                    <p><?= htmlspecialchars($flight['aircraft_model']) ?></p>
                </div>

                <div class="info-card">
                    <h4>Capacity</h4>
                    <p><?= $flight['total_seats'] ?> total seats</p>
                    <p><?= $flight['available_seats'] ?> available</p>
                </div>

                <div class="info-card">
                    <h4>Occupancy</h4>
                    <p><?= $stats['occupancy_rate'] ?>% full</p>
                    <p><?= $stats['booked_seats'] ?> seats booked</p>
                </div>

                <div class="info-card">
                    <h4>Price</h4>
                    <p class="price">KES <?= number_format($flight['price'], 2) ?></p>
                    <p>per person</p>
                </div>

            </div>

            <!-- Booking -->
            <div class="booking-section">

                <?php if ($flight['available_seats'] > 0 && $flight['status'] === 'scheduled'): ?>

                    <form action="../booking/seat_selection.php" method="GET">
                        <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">

                        <label for="passengers">Passengers:</label>
                        <input type="number"
                               name="passengers"
                               min="1"
                               max="<?= min($flight['available_seats'], 9) ?>"
                               value="1"
                               class="passenger-input">

                        <button class="btn btn-primary btn-large" type="submit">
                            Proceed to Booking
                        </button>
                    </form>

                <?php elseif ($flight['available_seats'] == 0): ?>
                    <div class="alert alert-warning">This flight is fully booked.</div>

                <?php else: ?>
                    <div class="alert alert-info">
                        Unavailable (Status: <?= htmlspecialchars($flight['status']) ?>)
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

</main>
</div>

</body>
</html>
