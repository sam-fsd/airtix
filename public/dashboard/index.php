<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/models/Flight.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();
requireLogin();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['first_name'] ?? 'User';

$bookingModel = new Booking();
$upcomingBookings = $bookingModel->getUpcomingBookings($userId);

$recentFlights = $bookingModel->getPastBookings($userId);
$recentFlights = array_slice($recentFlights, 0, 4);

$nextFlight = !empty($upcomingBookings) ? $upcomingBookings[0] : null;

// Simulated weather
$weather = [
    'condition' => 'Partly Cloudy',
    'temp_c' => 22,
    'temp_f' => 71.6,
    'city' => 'Nairobi',
    'country' => 'Kenya',
    'day' => 'Monday',
];

// Destination facts
$destinationFacts = null;
if ($nextFlight) {
    $destinationCity = $nextFlight['destination_city'];

    $factsDatabase = [
        'Doha' => [
            'image' => 'https://images.unsplash.com/photo-1570544820795-0960b7d3a25f?w=800',
            'facts' => [
                'Known for its modern skyscrapers and futuristic architecture.',
                'Hosts the 2022 FIFA World Cup final at Lusail Stadium.',
                'Has one of the fastest-growing economies in the world.',
                'Famous for Souq Waqif market.',
                'Pearl-Qatar is a man-made island shaped like a pearl.',
                'Extremely hot summers, often above 40°C.',
                'Hamad International Airport is globally top ranked.',
            ],
        ],
        'Dubai' => [
            'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800',
            'facts' => [
                'Home to the Burj Khalifa.',
                'Features the massive Dubai Mall.',
                'Famous for ultramodern architecture.',
                'Palm Jumeirah is shaped like a palm tree.',
                'Indoor ski resort in the desert.',
                'More than 200 nationalities live there.',
                'Built on trade, oil, and tourism.',
            ],
        ],
        'Mombasa' => [
            'image' => 'https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=800',
            'facts' => [
                'Kenya’s oldest city.',
                'White-sand beaches along the Indian Ocean.',
                'Fort Jesus built in 1593.',
                'Rich Swahili culture.',
                'Haller Park transformed from a quarry.',
                'Likoni Ferry connects south coast.',
                'Major tourism hub.',
            ],
        ],
    ];

    if (isset($factsDatabase[$destinationCity])) {
        $destinationFacts = $factsDatabase[$destinationCity];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AirTix</title>

    <!-- Base styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <!-- Component styles for this page -->
    <link rel="stylesheet" href="../assets/css/dashboard-home.css">
</head>
<body>
    <div class="container">

        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            
            <!-- Header -->
            <header class="dashboard-header-hero">
                <div class="hero-left">
                    <h1>Hi 👋 <?= htmlspecialchars($userName) ?>,</h1>
                </div>

                <div class="hero-weather">
                    <div class="weather-info">
                        <div class="weather-condition"><?= $weather['condition'] ?></div>
                        <div class="weather-location">
                            <?= $weather['city'] ?>, <?= $weather['country'] ?>
                            <span class="weather-day"><?= $weather['day'] ?></span>
                        </div>
                    </div>

                    <div class="weather-visual">
                        <div class="weather-icon">
                            <div class="cloud">☁️</div>
                            <div class="sun">☀️</div>
                            <div class="airplane">✈️</div>
                        </div>
                        <div class="weather-temp">
                            <span class="temp-large"><?= $weather['temp_c'] ?>°</span>
                            <span class="temp-small"><?= $weather['temp_f'] ?> F</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Upcoming Flight -->
            <?php if ($nextFlight): ?>
            <section class="upcoming-flight-section">

                <div class="section-badge">UPCOMING FLIGHT</div>

                <div class="upcoming-flight-card">
                    <div class="flight-icon">✈️</div>

                    <div class="flight-route">
                        <h2><?= htmlspecialchars($nextFlight['origin_city']) ?> - 
                            <?= htmlspecialchars($nextFlight['destination_city']) ?></h2>

                        <div class="flight-details">
                            <?= date('M d, D', strtotime($nextFlight['departure_time'])) ?> |
                            <?= count($nextFlight['passengers'] ?? [1]) ?>
                            TRAVELER<?= count($nextFlight['passengers'] ?? [1]) > 1 ? 'S' : '' ?>
                        </div>
                    </div>

                    <a class="btn-change" 
                       href="../tickets/view.php?booking_id=<?= $nextFlight['booking_id'] ?>">
                       View Tickets
                    </a>
                </div>
            </section>
            <?php endif; ?>


            <!-- Main Two Column Layout -->
            <div class="dashboard-grid">

                <!-- Recent Flights -->
                <section class="recent-flights-section">
                    <h3 class="section-title">Recent flights you've had</h3>

                    <?php if (empty($recentFlights)): ?>
                        <div class="empty-state">
                            <p>No recent flights yet.</p>
                            <a href="../flights/search.php" class="btn btn-primary">Search Flights</a>
                        </div>
                    <?php else: ?>
                        <div class="recent-flights-list">
                            <?php foreach ($recentFlights as $flight): ?>
                                <div class="recent-flight-item">
                                    <div class="flight-icon-small">✈️</div>

                                    <div class="flight-route-info">
                                        <strong><?= htmlspecialchars($flight['origin_city']) ?> - 
                                            <?= htmlspecialchars(
                                                $flight['destination_city'],
                                            ) ?></strong>
                                        <span class="flight-date">
                                            <?= date(
                                                'M d, Y',
                                                strtotime($flight['departure_time']),
                                            ) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <a href="../dashboard/my-bookings.php" class="btn-view-more">
                            View More
                        </a>
                    <?php endif; ?>
                </section>

                <!-- Destination Facts -->
                <?php if ($destinationFacts): ?>
                <section class="destination-facts-section">
                    <h3 class="section-title">Interesting facts about your destination</h3>

                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?= $destinationFacts['image'] ?>" 
                                 alt="<?= htmlspecialchars($nextFlight['destination_city']) ?>">
                            <div class="destination-label">
                                <?= htmlspecialchars($nextFlight['destination_city']) ?>
                            </div>
                        </div>

                        <div class="destination-facts">
                            <ul>
                                <?php foreach ($destinationFacts['facts'] as $fact): ?>
                                    <li><?= htmlspecialchars($fact) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>

                <?php else: ?>
                <section class="destination-facts-section">
                    <h3 class="section-title" style="color: aliceblue;">Start Your Journey</h3>

                    <div class="empty-destination">
                        <p>Book a flight to discover more about your destination.</p>
                        <a href="../flights/search.php" class="btn btn-primary">Book Now</a>
                    </div>
                </section>
                <?php endif; ?>

            </div>

        </main>
    </div>
</body>
</html>
