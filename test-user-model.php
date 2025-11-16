<?php
// test-user-model.php

require_once './config/config.php';
require_once __DIR__ . '/app/models/User.php';

$userModel = new User();

echo '<h2>Testing User Model</h2>';

// Test 1: Create user
echo '<h3>Test 1: Create User</h3>';
$newUserId = $userModel->create([
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123',
    'first_name' => 'Test',
    'last_name' => 'User',
    'phone' => '+254712345678',
]);
echo $newUserId ? "✓ User created with ID: $newUserId<br>" : '✗ Failed<br>';

// Test 2: Find by ID
echo '<h3>Test 2: Find User by ID</h3>';
$user = $userModel->findById($newUserId);
echo $user ? "✓ Found: {$user['first_name']} {$user['last_name']}<br>" : '✗ Not found<br>';

// Test 3: Find by email
echo '<h3>Test 3: Find User by Email</h3>';
$user = $userModel->findByEmail($user['email']);
echo $user ? '✓ Found by email<br>' : '✗ Not found<br>';

// Test 4: Update
echo '<h3>Test 4: Update User</h3>';
$updated = $userModel->update($newUserId, [
    'first_name' => 'Updated',
    'last_name' => 'Name',
    'phone' => '+254700000000',
]);
echo $updated ? '✓ User updated<br>' : '✗ Update failed<br>';

// Test 5: Authentication
echo '<h3>Test 5: Authentication</h3>';
$authUser = $userModel->authenticate($user['email'], 'password123');
echo $authUser ? '✓ Authentication successful<br>' : '✗ Auth failed<br>';

// Test 6: Email exists check
echo '<h3>Test 6: Email Exists Check</h3>';
$exists = $userModel->emailExists($user['email']);
echo $exists ? '✓ Email exists check working<br>' : '✗ Failed<br>';

// Test 7: Get all users
echo '<h3>Test 7: Get All Users</h3>';
$allUsers = $userModel->getAll(5);
echo '✓ Retrieved ' . count($allUsers) . ' users<br>';

// Test 8: Delete
echo '<h3>Test 8: Delete User</h3>';
$deleted = $userModel->delete($newUserId);
echo $deleted ? '✓ User deleted<br>' : '✗ Delete failed<br>';

echo '<h2>All tests completed!</h2>';

?>
