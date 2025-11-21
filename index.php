<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AirTix — Book Your Next Flight</title>

    <link rel="stylesheet" href="public/assets/css/landing.css">
    <script
  src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.5/dist/dotlottie-wc.js"
  type="module"
></script>
</head>
<body>

    <header class="landing-header">
        <div class="logo">
            <img src="public/assets/images/airtix-logo.png" alt="AirTix Logo">
            <h1>AirTix</h1>
        </div>

        <div class="auth-links">
            <a href="public/login.php" class="auth-btn">Login</a>
            <a href="public/register.php" class="auth-btn secondary">Sign Up</a>
        </div>
    </header>

    <main class="hero-section">
        <div class="hero-content">
            <h2>Fly Smarter. Fly AirTix.</h2>
            <p>Search, book, and manage your trips with ease. Your journey begins here.</p>

            <a href="public/login.php" class="cta-btn">Get Started</a>
        </div>

        <div class="hero-art">

    <dotlottie-wc
      src="https://lottie.host/37beea7d-bcc1-494d-a6ae-21851d978570/2kJckXmyM2.lottie"
      style="width: 800px;height: 800px"
      autoplay
      loop
    >
    </dotlottie-wc>        
  </div>
</main>

    <footer class="landing-footer">
        <p>© <?= date('Y') ?> AirTix. All rights reserved.</p>
    </footer>

</body>
</html>
