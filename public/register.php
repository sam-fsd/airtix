<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? null;
unset($_SESSION['errors'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Register</title></head>
<body>
  <?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <?php foreach ($errors as $error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endforeach; ?>

  <form action="../app/controllers/AuthController.php" method="POST">
    <input type="text" name="first_name" placeholder="First Name"><br>
    <input type="text" name="last_name" placeholder="Last Name"><br>
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <button type="submit">Register</button>
  </form>
</body>
</html>
