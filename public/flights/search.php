<?php
/**
 * SEARCH PAGE VIEW
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Flight.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

// Get all destinations for dropdown
$db = Database::getInstance();
$stmt = $db->query('SELECT * FROM destinations ORDER BY city ASC');
$destinations = $stmt->fetchAll();

// Get popular routes
$flightModel = new Flight();
$popularRoutes = $flightModel->getPopularRoutes(5);

// Get errors and old input from session
$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['old_input']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search Flights - AirTix</title>

  <!-- global app styles (shared) -->
  <link rel="stylesheet" href="<?= rtrim(BASE_URL, '/') ?>/public/assets/css/dashboard.css">

  <!-- page-specific styles -->
  <link rel="stylesheet" href="<?= rtrim(BASE_URL, '/') ?>/public/assets/css/flights.css">
</head>
<body>
  <?php include_once __DIR__ . '/../includes/_sidebar.php'; ?>

  <div class="content">
    <main class="search-page">
      <section class="hero">
        <div class="page-container hero-inner">
          <div class="hero-text">
            <h1>Find Your Next Flight</h1>
            <p>Search and compare flights to your favorite destinations</p>
          </div>
        </div>
      </section>
      <section class="search-form-section">
        <div class="page-container">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
              <ul>
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <form
            action="<?= rtrim(BASE_URL, '/') ?>/app/controllers/FlightController.php"
            method="GET"
            class="flight-search-form"
            id="flightSearchForm"
          >
            <div class="row">
              <div class="form-group">
                <label for="origin">From</label>
                <select name="origin" id="origin" required>
                  <option value="">Select departure city</option>
                  <?php foreach ($destinations as $dest): ?>
                    <option value="<?= htmlspecialchars(
                        $dest['destination_id'] ?? ($dest['id'] ?? ''),
                    ) ?>"
                      <?= isset($oldInput['origin']) &&
                      $oldInput['origin'] == ($dest['destination_id'] ?? ($dest['id'] ?? ''))
                          ? 'selected'
                          : '' ?>>
                      <?= htmlspecialchars(
                          ($dest['city'] ?? '') . ' (' . ($dest['airport_code'] ?? '') . ')',
                      ) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="destination">To</label>
                <select name="destination" id="destination" required>
                  <option value="">Select destination city</option>
                  <?php foreach ($destinations as $dest): ?>
                    <option value="<?= htmlspecialchars(
                        $dest['destination_id'] ?? ($dest['id'] ?? ''),
                    ) ?>"
                      <?= isset($oldInput['destination']) &&
                      $oldInput['destination'] == ($dest['destination_id'] ?? ($dest['id'] ?? ''))
                          ? 'selected'
                          : '' ?>>
                      <?= htmlspecialchars(
                          ($dest['city'] ?? '') . ' (' . ($dest['airport_code'] ?? '') . ')',
                      ) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="date">Date</label>
                <input
                  type="date"
                  name="date"
                  id="date"
                  required
                  min="<?= date('Y-m-d') ?>"
                  value="<?= htmlspecialchars($oldInput['date'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="passengers">Passengers</label>
                <input
                  type="number"
                  name="passengers"
                  id="passengers"
                  min="1"
                  max="9"
                  value="<?= htmlspecialchars($oldInput['passengers'] ?? 1) ?>">
              </div>
              <div class="form-group form-submit">
                <button type="submit" class="btn btn-primary">Search Flights</button>
              </div>
            </div>
            <details class="advanced-filters">
              <summary>Advanced Filters</summary>
              <div class="row">
                <div class="form-group">
                  <label for="time_of_day">Time of Day</label>
                  <select name="time_of_day" id="time_of_day">
                    <option value="">Any time</option>
                    <option value="morning">Morning (6AM - 12PM)</option>
                    <option value="afternoon">Afternoon (12PM - 6PM)</option>
                    <option value="evening">Evening (6PM - 12AM)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="min_price">Min Price (KES)</label>
                  <input type="number" name="min_price" id="min_price" step="100" />
                </div>
                <div class="form-group">
                  <label for="max_price">Max Price (KES)</label>
                  <input type="number" name="max_price" id="max_price" step="100" />
                </div>
                <div class="form-group">
                  <label for="sort_by">Sort By</label>
                  <select name="sort_by" id="sort_by">
                    <option value="departure_time">Departure Time</option>
                    <option value="price">Price</option>
                    <option value="arrival_time">Arrival Time</option>
                  </select>
                </div>
              </div>
            </details>
          </form>
        </div>
      </section>
      <?php if (!empty($popularRoutes)): ?>
      <section class="popular-routes">
        <div class="page-container">
          <h2>Popular Routes</h2>
          <div class="routes-grid">
            <?php foreach ($popularRoutes as $route): ?>
              <div class="route-card">
                <h3><?= htmlspecialchars($route['origin_city'] ?? $route['origin']) ?>
                  → <?= htmlspecialchars(
                      $route['destination_city'] ?? $route['destination'],
                  ) ?></h3>
                <p class="price">From KES <?= number_format($route['min_price'] ?? 0, 2) ?></p>
                <p class="bookings"><?= htmlspecialchars(
                    $route['booking_count'] ?? 0,
                ) ?> recent bookings</p>
                <a
                  class="btn btn-secondary"
                  href="<?= rtrim(
                      BASE_URL,
                      '/',
                  ) ?>/app/controllers/FlightController.php?origin=<?= urlencode(
    $route['origin_id'] ?? $route['origin'],
) ?>&destination=<?= urlencode($route['destination_id'] ?? $route['destination']) ?>&date=<?= date(
    'Y-m-d',
    strtotime('+1 day'),
) ?>&passengers=1"
                >Search Flights</a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>
    </main>
  </div>
  <script src="<?= rtrim(BASE_URL, '/') ?>/public/assets/js/validation.js"></script>
</body>
</html>
