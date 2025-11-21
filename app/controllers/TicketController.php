<?php
/**
 * ============================================================
 * TICKET VIEW CONTROLLER (app/controllers/TicketViewController.php)
 * ============================================================
 *
 * Handles ticket viewing and PDF generation
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../helpers/functions.php';

session_start();

requireLogin();

$ticketModel = new Ticket();
$bookingModel = new Booking();

$ticketId = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'view'; // 'view' or 'download'

if (!$ticketId) {
    $_SESSION['errors'] = ['Ticket not found'];
    redirect('public/dashboard/my-bookings.php');
}

// Get ticket details
$ticket = $ticketModel->findById($ticketId);

if (!$ticket) {
    $_SESSION['errors'] = ['Ticket not found'];
    redirect('public/dashboard/my-bookings.php');
    exit();
}

// Verify ticket belongs to user (through booking)
$booking = $bookingModel->findById($ticket['booking_id']);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    $_SESSION['errors'] = ['Unauthorized access'];
    redirect('public/dashboard/my-bookings.php');
}

// Store ticket in session for view
$_SESSION['ticket_data'] = $ticket;

if ($action === 'download') {
    // Generate PDF
    $pdfContent = $ticketModel->generatePDF($ticketId);

    if ($pdfContent) {
        redirect('public/tickets/download.php?id=' . $ticketId);
    } else {
        $_SESSION['errors'] = ['Failed to generate ticket'];
        redirect('public/tickets/view.php?id=' . $ticketId);
    }
}

// Redirect to ticket view page
redirect('public/tickets/view.php?id=' . $ticketId);
?>
