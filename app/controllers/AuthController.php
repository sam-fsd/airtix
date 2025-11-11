<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();

    // Get form data
    $data = [
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'phone' => $_POST['phone'] ?? null,
    ];

    // Validate data using model
    $errors = $userModel->validate($data);

    if (empty($errors)) {
        // Create user using model
        $userId = $userModel->create($data);

        if ($userId) {
            $_SESSION['success'] = 'Registration successful!';
            header('Location: ../../public/login.php');
            exit();
        } else {
            $_SESSION['errors'] = ['Registration failed. Please try again.'];
        }
    } else {
        $_SESSION['errors'] = $errors;
    }
}

header('Location: ../../public/register.php');

?>
