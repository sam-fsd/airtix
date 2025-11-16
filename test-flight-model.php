
<?php
require_once './config/config.php';
require_once './app/models/Flight.php';

echo '<h2>Testing Flight Model</h2>';

$flightModel = new Flight();

// Test 1: Get all flights
echo '<h3>Test 1: Get All Flights</h3>';
$flights = $flightModel->getAll(5);
echo '✓ Retrieved ' . count($flights) . ' flights<br>';
foreach ($flights as $flight) {
    echo "- {$flight['flight_number']}: {$flight['origin_city']} → {$flight['destination_city']}<br>";
}

// Test 2: Search flights
echo '<h3>Test 2: Search Flights (Nairobi to Mombasa)</h3>';
$results = $flightModel->search([
    'origin_id' => 1,
    'destination_id' => 2,
]);
echo '✓ Found ' . count($results) . ' flights<br>';

// Test 3: Find by ID
echo '<h3>Test 3: Find Flight by ID</h3>';
$flight = $flightModel->findById(1);
echo $flight ? "✓ Found: {$flight['flight_number']}<br>" : '✗ Not found<br>';

// Test 4: Get upcoming flights
echo '<h3>Test 4: Get Upcoming Flights</h3>';
$upcoming = $flightModel->getUpcoming(5);
echo '✓ Found ' . count($upcoming) . ' upcoming flights<br>';

// Test 5: Check seat availability
echo '<h3>Test 5: Check Seat Availability</h3>';
$hasSeats = $flightModel->hasAvailableSeats(1, 2);
echo $hasSeats ? '✓ Seats available<br>' : '✗ No seats<br>';

// Test 6: Get statistics
echo '<h3>Test 6: Get Flight Statistics</h3>';
$stats = $flightModel->getStatistics(1);
echo "✓ Occupancy: {$stats['occupancy_rate']}%<br>";
echo '✓ Revenue: KES ' . number_format($stats['total_revenue'], 2) . '<br>';

echo '<h2>All tests completed!</h2>';


?>
