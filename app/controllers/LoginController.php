<?php
/**
 * ============================================================
 * LOGIN CONTROLLER
 * ============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();

    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_email'] = $email;
        redirect('public/login.php');
    }

    // Authenticate
    $user = $userModel->authenticate($email, $password);

    if ($user) {
        // Login successful
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['is_admin'] = $user['is_admin'];

        // Update last login (optional)
        // $userModel->updateLastLogin($user['user_id']);

        // Redirect based on role
        if ($user['is_admin']) {
            redirect('admin/dashboard.php');
        } else {
            redirect('public/dashboard/index.php');
        }
    } else {
        $_SESSION['errors'] = ['Invalid email or password'];
        $_SESSION['old_email'] = $email;
        redirect('public/login.php');
    }
}

// If not POST, redirect to login
redirect('public/login.php');
?>
