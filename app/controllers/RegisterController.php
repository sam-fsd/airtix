<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();

    // Get form data
    $data = [
        'email' => clean($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'first_name' => clean($_POST['first_name'] ?? ''),
        'last_name' => clean($_POST['last_name'] ?? ''),
        'phone' => clean($_POST['phone'] ?? ''),
    ];

    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // Additional validation
    $errors = [];

    // Check password confirmation
    if ($data['password'] !== $passwordConfirm) {
        $errors[] = 'Passwords do not match';
    }

    // Check terms acceptance
    if (!isset($_POST['terms'])) {
        $errors[] = 'You must agree to the Terms of Service';
    }

    // Validate using model
    $modelErrors = $userModel->validate($data);
    $errors = array_merge($errors, $modelErrors);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $data;
        redirect('public/register.php');
    }

    // Create user
    $userId = $userModel->create($data);

    if ($userId) {
        $_SESSION['success'] = 'Registration successful! Please login.';
        redirect('public/login.php');
    } else {
        $_SESSION['errors'] = ['Registration failed. Please try again.'];
        $_SESSION['old_input'] = $data;
        redirect('public/register.php');
    }
}

// If not POST, redirect to register
redirect('public/register.php');
?>
