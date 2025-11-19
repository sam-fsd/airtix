<?php
/**
 * SEARCH RESULTS VIEW
 * public/flights/results.php
 */

require_once __DIR__ . '/../../config/config.php';

session_start();

// Ensure search results exist
if (!isset($_SESSION['search_results'])) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/public/flights/search.php');
    exit();
}

$searchData = $_SESSION['search_results'];
$flights = $searchData['flights'];
$origin = $searchData['origin'];
$destination = $searchData['destination'];
$searchDate = $searchData['search_date'];
$passengers = $searchData['passengers'];

$success = $_SESSION['success'] ?? null;
$errors = $_SESSION['errors'] ?? [];

unset($_SESSION['success'], $_SESSION['errors']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Flight Results - AirTix</title>

    <!-- global shared styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/styles.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/results.css">
</head>
<body>

<?php include_once __DIR__ . '/../dashboard/_sidebar.php'; ?>

<div class="content">
    <main class="results-page">
        <div class="page-container">
    
            <!-- Summary -->
            <section class="search-summary">
                <h1>
                    <?= htmlspecialchars($origin['city']) ?>
                    →
                    <?= htmlspecialchars($destination['city']) ?>
                </h1>
    
                <p class="summary-meta">
                    <?= date('D, d M Y', strtotime($searchDate)) ?> •
                    <?= $passengers ?> passenger<?= $passengers > 1 ? 's' : '' ?>
                </p>
    
                <a class="btn btn-secondary"
                   href="<?= BASE_URL ?>/public/flights/search.php">
                   Modify Search
                </a>
            </section>
    
            <!-- Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
    
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
    
    
            <!-- Main Results -->
            <section class="results-container">
    
                <?php if (empty($flights)): ?>
                    <div class="no-results">
                        <h2>No Flights Found</h2>
                        <p>Try adjusting your search filters or select a different date.</p>
    
                        <a class="btn btn-primary"
                           href="<?= BASE_URL ?>flights/index.php">
                           Try Another Search
                        </a>
                    </div>
    
                <?php else: ?>
                    <div class="results-header">
                        <p class="results-count">
                            Found <?= count($flights) ?> flight<?= count($flights) > 1 ? 's' : '' ?>
                        </p>
                    </div>
    
                    <div class="flights-list">
                        <?php foreach ($flights as $flight): ?>
    
                            <?php
                            $departure = strtotime($flight['departure_time']);
                            $arrival = strtotime($flight['arrival_time']);
                            $duration = ($arrival - $departure) / 3600;
                            $totalPrice = $flight['price'] * $passengers;
                            $canBook = $flight['available_seats'] >= $passengers;
                            ?>
    
                            <div class="flight-card <?= !$canBook ? 'sold-out' : '' ?>">
    
                                <div class="flight-main">
    
                                    <!-- Times -->
                                    <div class="flight-times">
                                        <div class="time-block">
                                            <span class="time"><?= date('H:i', $departure) ?></span>
                                            <span class="airport"><?= htmlspecialchars(
                                                $flight['origin_code'],
                                            ) ?></span>
                                        </div>
    
                                        <div class="duration">
                                            <span><?= number_format($duration, 1) ?>h</span>
                                            <div class="flight-line"></div>
                                        </div>
    
                                        <div class="time-block">
                                            <span class="time"><?= date('H:i', $arrival) ?></span>
                                            <span class="airport"><?= htmlspecialchars(
                                                $flight['destination_code'],
                                            ) ?></span>
                                        </div>
                                    </div>
    
                                    <!-- Flight info -->
                                    <div class="flight-info">
                                        <p class="flight-number"><?= htmlspecialchars(
                                            $flight['flight_number'],
                                        ) ?></p>
                                        <p class="aircraft"><?= htmlspecialchars(
                                            $flight['aircraft_model'],
                                        ) ?></p>
                                        <p class="seats">
                                            <?= $flight['available_seats'] ?> available seats
                                        </p>
                                    </div>
    
                                    <!-- Price -->
                                    <div class="flight-price">
                                        <p class="price">KES <?= number_format(
                                            $totalPrice,
                                            2,
                                        ) ?></p>
                                        <p class="per-person">
                                            KES <?= number_format($flight['price'], 2) ?> per person
                                        </p>
                                    </div>
    
                                    <!-- Actions -->
                                    <div class="flight-actions">
                                        <a class="btn btn-secondary"
                                           href="<?= BASE_URL ?>/public/flights/flight_detail.php?id=<?= $flight[
    'flight_id'
] ?>">
                                            Details
                                        </a>
    
                                        <?php if ($canBook): ?>
                                            <a class="btn btn-primary"
                                               href="<?= BASE_URL ?>/public/booking/seat_selection.php?flight_id=<?= $flight[
    'flight_id'
] ?>&passengers=<?= $passengers ?>">
                                                Book Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-disabled" disabled>
                                                Not Enough Seats
                                            </button>
                                        <?php endif; ?>
                                    </div>
    
                                </div>
                            </div>
    
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
    
            </section>
        </div>
    </main>
</div>
</body>
</html>
