<?php
/**
 * ============================================================
 * REGISTRATION PAGE (public/auth/register.php)
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
$oldInput = $_SESSION['old_input'] ?? [];

unset($_SESSION['errors']);
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AirTix</title>
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
                <h2>Join AirTix Today!</h2>
                <p>Create an account and start your journey with us.</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Quick & easy booking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Secure payments</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Track your bookings</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Digital tickets</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Registration Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h2 class="form-title">Create Account</h2>
                <p class="form-subtitle">Fill in your details to get started</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="../app/controllers/RegisterController.php" method="POST" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-input"
                                   placeholder="John"
                                   value="<?= htmlspecialchars($oldInput['first_name'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-input"
                                   placeholder="Doe"
                                   value="<?= htmlspecialchars($oldInput['last_name'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input"
                               placeholder="your@email.com"
                               value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input"
                               placeholder="+254712345678"
                               value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input"
                               placeholder="At least 8 characters"
                               required>
                        <small class="form-hint">Must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" 
                               id="password_confirm" 
                               name="password_confirm" 
                               class="form-input"
                               placeholder="Re-enter your password"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#" class="link-text">Terms of Service</a> and <a href="#" class="link-text">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        Create Account
                    </button>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? 
                        <a href="login.php" class="link-primary">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
