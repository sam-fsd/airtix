<?php
/**
 * ============================================================
 * FLIGHT SEARCH CONTROLLER
 * ============================================================
 *
 * Handles flight search requests
 * Validates input, queries the Flight model, and stores results in session
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Flight.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $flightModel = new Flight();

    // Get search parameters
    $origin = $_GET['origin'] ?? null;
    $destination = $_GET['destination'] ?? null;
    $date = $_GET['date'] ?? null;
    $passengers = $_GET['passengers'] ?? 1;

    // Validation
    $errors = [];

    if (empty($origin)) {
        $errors[] = 'Please select departure city';
    }

    if (empty($destination)) {
        $errors[] = 'Please select destination city';
    }

    if (empty($date)) {
        $errors[] = 'Please select travel date';
    } else {
        // Check if date is not in the past
        if (strtotime($date) < strtotime('today')) {
            $errors[] = 'Travel date cannot be in the past';
        }
    }

    if ($origin && $destination && $origin == $destination) {
        $errors[] = 'Origin and destination cannot be the same';
    }

    // If validation fails, redirect back to search page
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_GET; // Preserve user input
        header('Location: ../../public/flights/index.php');
        exit();
    }

    // Build search filters
    $filters = [
        'origin_id' => $origin,
        'destination_id' => $destination,
        'date' => $date,
        'min_seats' => $passengers,
    ];

    // Optional filters
    if (!empty($_GET['time_of_day'])) {
        $filters['time_of_day'] = $_GET['time_of_day'];
    }

    if (!empty($_GET['min_price'])) {
        $filters['min_price'] = $_GET['min_price'];
    }

    if (!empty($_GET['max_price'])) {
        $filters['max_price'] = $_GET['max_price'];
    }

    // Sorting
    $filters['sort_by'] = $_GET['sort_by'] ?? 'departure_time';
    $filters['sort_order'] = $_GET['sort_order'] ?? 'ASC';

    // Search flights
    $flights = $flightModel->search($filters);

    // Get destination info for display
    $db = Database::getInstance();

    $originInfo = $db
        ->query('SELECT * FROM destinations WHERE destination_id = ?', [$origin])
        ->fetch();

    $destinationInfo = $db
        ->query('SELECT * FROM destinations WHERE destination_id = ?', [$destination])
        ->fetch();

    // Store results in session
    $_SESSION['search_results'] = [
        'flights' => $flights,
        'origin' => $originInfo,
        'destination' => $destinationInfo,
        'search_date' => $date,
        'passengers' => $passengers,
        'filters' => $filters,
    ];

    // Redirect to results page
    header('Location: ../../public/flights/results.php');
    exit();
}

// If not GET request, redirect to search page
header('Location: ../../public/flights/index.php');
exit();
?>
