<?php
/**
 * ============================================================
 * DOWNLOAD TICKET PAGE
 * ============================================================
 *
 * Generates a downloadable PDF ticket
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Ticket.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

session_start();

requireLogin();

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    $_SESSION['errors'] = ['Ticket not found'];
    redirect('public/dashboard/my-bookings.php');
}

$ticketModel = new Ticket();
$ticket = $ticketModel->findById($ticketId);

if (!$ticket) {
    $_SESSION['errors'] = ['Ticket not found'];
    redirect('public/dashboard/my-bookings.php');
}

// Verify ownership
$bookingModel = new Booking();
$booking = $bookingModel->findById($ticket['booking_id']);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    $_SESSION['errors'] = ['Unauthorized access'];
    redirect('public/dashboard/my-bookings.php');
}

// Generate HTML content for PDF
$html = $ticketModel->generatePDF($ticketId);

// Set headers for download
header('Content-Type: text/html');
header('Content-Disposition: inline; filename="ticket-' . $ticket['ticket_number'] . '.html"');

echo $html;
exit();
?>
