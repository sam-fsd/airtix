<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Flight.php';
require_once __DIR__ . '/../../app/models/Seat.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

$flightId = $_GET['flight_id'] ?? null;
$passengers = $_GET['passengers'] ?? 1;

if (!$flightId) {
    $_SESSION['errors'] = ['Flight not found'];
    redirect('public/flights/search.php');
}

// Get flight details
$flightModel = new Flight();
$flight = $flightModel->findById($flightId);

if (!$flight) {
    $_SESSION['errors'] = ['Flight not found'];
    header('Location: ../flights/index.php');
    exit();
}

// Check availability
if ($flight['available_seats'] < $passengers) {
    $_SESSION['errors'] = ['Not enough seats available'];
    redirect('public/flights/flight_detail.php?id=' . $flightId);
}

// Get seat map
$seatModel = new Seat();
$seatMapData = $seatModel->generateSeatMap($flightId);

if (!$seatMapData) {
    $_SESSION['errors'] = ['Unable to load seat map'];
    redirect('public/flights/flight_detail.php?id=' . $flightId);
}

$seatMap = $seatMapData['seats'];
$bookedSeats = $seatMapData['booked_seats'];

// Calculate price
$pricePerPerson = $flight['price'];
$totalPrice = $pricePerPerson * $passengers;

// Get errors
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - AirTix</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/seats.css">
</head>
<body>
    <?php include __DIR__ . '/../dashboard/_sidebar.php'; ?>
    

<div class="content">
<main class="booking-page">

    <div class="page-container">

        <!-- Steps -->
        <div class="booking-steps">
            <div class="step completed">
                <div class="step-number">1</div><span>Select Flight</span>
            </div>
            <div class="step active">
                <div class="step-number">2</div><span>Select Seats</span>
            </div>
            <div class="step"><div class="step-number">3</div><span>Passenger Info</span></div>
            <div class="step"><div class="step-number">4</div><span>Confirmation</span></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><ul>
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars(
    $e,
) ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <div class="seat-selection-layout">

            <!-- LEFT SIDE: Seat Map -->
            <div class="seat-selection-main">

                <div class="seat-selection-header">
                    <div>
                        <h2>Select Your Seats</h2>
                        <p class="muted">
                            Choose <?= $passengers ?> seat<?= $passengers > 1
     ? 's'
     : '' ?> for your flight
                        </p>
                    </div>

                    <div class="selected-seats-display">
                        <span class="selected-count">0 / <?= $passengers ?></span>
                        <span class="muted">seats selected</span>
                    </div>
                </div>

                <!-- Legend -->
                <div class="seat-legend">
                    <div class="legend-item"><span class="seat-preview available"></span>Available</div>
                    <div class="legend-item"><span class="seat-preview selected"></span>Selected</div>
                    <div class="legend-item"><span class="seat-preview booked"></span>Booked</div>
                </div>

                <div class="aircraft-container">

                    <div class="aircraft-cockpit"><div class="cockpit-shape"></div></div>

                    <div class="seat-map">
                        <?php foreach ($seatMap as $row): ?>
                            <div class="seat-row <?= $row['class'] ?>-row">

                                <div class="row-number"><?= $row['row_number'] ?></div>

                                <div class="seats-container">
                                    <?php foreach ($row['seats'] as $seat): ?>
                                        <?php if ($seat['type'] === 'aisle-gap'): ?>
                                            <div class="aisle-gap"></div>
                                        <?php else: ?>
                                            <button class="seat <?= $seat['status'] ?> <?= $seat[
     'type'
 ] ?>"
                                                    data-seat="<?= $seat['number'] ?>"
                                                    <?= $seat['status'] === 'booked'
                                                        ? 'disabled'
                                                        : '' ?>>
                                                <?= $seat['number'] ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <div class="row-number"><?= $row['row_number'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="aircraft-tail"></div>
                </div>

            </div>

            <!-- RIGHT SIDE: Summary -->
            <aside class="seat-selection-sidebar">
                <div class="booking-summary">
                    <h3>Flight Details</h3>

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
                    <div class="summary-item"><span>Passengers</span><strong><?= $passengers ?></strong></div>

                    <div class="summary-item total"><span>Total</span>
                        <strong>KES <?= number_format($totalPrice, 2) ?></strong></div>
                </div>

                <div class="selected-seats-list">
                    <h4>Selected Seats</h4>
                    <div id="selectedSeatsList" class="selected-list-empty">
                        No seats selected yet
                    </div>
                </div>

                <form action="passenger_info.php" method="GET" id="seatSelectionForm">
                    <input type="hidden" name="flight_id" value="<?= $flightId ?>">
                    <input type="hidden" name="passengers" value="<?= $passengers ?>">
                    <input type="hidden" name="selected_seats" id="selectedSeatsInput">

                    <button type="submit" class="btn btn-primary btn-large btn-disabled" id="continueBtn" disabled>
                        Continue to Passenger Info
                    </button>

                    <a href="../flights/flight_detail.php?id=<?= $flightId ?>" class="btn btn-secondary cancel-btn">
                        Cancel
                    </a>
                </form>
            </aside>

        </div>
    </div>

</main>
</div>

    
    
   <script>
        const maxSeats = <?= $passengers ?>;
        const selectedSeats = [];
        
        // Get all seat buttons
        const seatButtons = document.querySelectorAll('.seat.available');
        const selectedCountDisplay = document.querySelector('.selected-count');
        const selectedSeatsList = document.getElementById('selectedSeatsList');
        const selectedSeatsInput = document.getElementById('selectedSeatsInput');
        const continueBtn = document.getElementById('continueBtn');
        
        // Add click handlers to all available seats
        seatButtons.forEach(button => {
            button.addEventListener('click', function() {
                const seatNumber = this.getAttribute('data-seat');
                
                if (this.classList.contains('selected')) {
                    // Deselect seat
                    this.classList.remove('selected');
                    const index = selectedSeats.indexOf(seatNumber);
                    if (index > -1) {
                        selectedSeats.splice(index, 1);
                    }
                } else {
                    // Select seat (if not at max)
                    if (selectedSeats.length < maxSeats) {
                        this.classList.add('selected');
                        selectedSeats.push(seatNumber);
                    } else {
                        // Show alert
                        alert(`You can only select ${maxSeats} seat${maxSeats > 1 ? 's' : ''}`);
                        return;
                    }
                }
                
                updateDisplay();
            });
        });
        
        function updateDisplay() {
            // Update counter
            selectedCountDisplay.textContent = `${selectedSeats.length} / ${maxSeats}`;
            
            // Update selected seats list
            if (selectedSeats.length === 0) {
                selectedSeatsList.innerHTML = '<p style="color: var(--text-muted); font-size: 0.875rem;">No seats selected yet</p>';
            } else {
                selectedSeatsList.innerHTML = selectedSeats.map((seat, index) => 
                    `<div class="selected-seat-item">
                        <span>Passenger ${index + 1}</span>
                        <strong>Seat ${seat}</strong>
                    </div>`
                ).join('');
            }
            
            // Update hidden input
            selectedSeatsInput.value = selectedSeats.join(',');
            
            // Enable/disable continue button
            if (selectedSeats.length === maxSeats) {
                continueBtn.disabled = false;
                continueBtn.classList.remove('btn-disabled');
            } else {
                continueBtn.disabled = true;
                continueBtn.classList.add('btn-disabled');
            }
        }
        
        // Form submission validation
        document.getElementById('seatSelectionForm').addEventListener('submit', function(e) {
            if (selectedSeats.length !== maxSeats) {
                e.preventDefault();
                alert(`Please select exactly ${maxSeats} seat${maxSeats > 1 ? 's' : ''}`);
            }
        });
</script>
</body>
</html>