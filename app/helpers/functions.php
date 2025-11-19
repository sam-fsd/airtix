<?php

require_once __DIR__ . '/../../config/config.php';

/**
 * Sanitize user input
 */
function clean($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Redirect to another page
 */
function redirect($page)
{
    header('Location: ' . BASE_URL . '/' . ltrim($page, '/'));
    exit();
}

/**
 * Display flash message
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y')
{
    return date($format, strtotime($date));
}

/**
 * Format currency
 */
function formatPrice($amount)
{
    return 'KES ' . number_format($amount, 2);
}

/**
 * Generate unique booking reference
 */
function generateBookingRef()
{
    return 'BK' . strtoupper(substr(uniqid(), -8));
}

/**
 * Calculate flight duration
 */
function calculateDuration($departure, $arrival)
{
    $diff = strtotime($arrival) - strtotime($departure);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return sprintf('%dh %dm', $hours, $minutes);
}

/**
 * Check if email is valid
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Require login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to continue');
        redirect('public/login.php');
    }
}

/**
 * Require admin
 */
function requireAdmin()
{
    if (!isAdmin()) {
        setFlash('error', 'Access denied');
        redirect('index.php');
    }
}
?>
