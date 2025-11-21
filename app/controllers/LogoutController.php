<?php
/**
 * ============================================================
 * LOGOUT CONTROLLER
 * ============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/functions.php';

session_start();

// Destroy all session data
session_unset();
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['success'] = 'You have been logged out successfully.';

// Redirect to login
redirect('public/login.php');
?>
