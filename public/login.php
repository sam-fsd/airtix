<?php
/**
 * ============================================================
 * LOGIN PAGE
 * ============================================================
 */

require_once __DIR__ . '/../config/config.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/index.php');
    exit();
}

// Get errors and old input
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? null;
$oldEmail = $_SESSION['old_email'] ?? '';

unset($_SESSION['errors']);
unset($_SESSION['success']);
unset($_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AirTix</title>
    <link rel="stylesheet" href="./assets/css/styles.css">
    <link rel="stylesheet" href="./assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <!-- Left Side - Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <h1 class="brand-logo">
                    <span class="logo-icon">✈</span>
                    AirTix
                </h1>
                <h2>Welcome Back!</h2>
                <p>Login to manage your bookings and explore new destinations.</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Easy flight booking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Manage your trips</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Instant e-tickets</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h2 class="form-title">Sign In</h2>
                <p class="form-subtitle">Enter your credentials to access your account</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <form action="../app/controllers/LoginController.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input"
                               placeholder="your@email.com"
                               value="<?= htmlspecialchars($oldEmail) ?>"
                               required
                               autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input"
                               placeholder="Enter your password"
                               required>
                    </div>
                                  
                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        Sign In
                    </button>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? 
                        <a href="register.php" class="link-primary">Sign up</a>
                    </p>
                </div>
            
            </div>
        </div>
    </div>
</body>
</html>