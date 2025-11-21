<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AirTix Dashboard</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
</head>
<body>
  <div class="container">
   <?php include_once __DIR__ . '/../includes/_sidebar.php'; ?>
    <!-- Main content -->
    <main class="content">
      <section class="header">
        <h1>Hi 👋 <span id="username">Joseph</span>,</h1>
        <div class="weather">
          <div class="weather-info">
            <p>Partly Cloudy</p>
            <p><strong>Nairobi, Kenya</strong></p>
          </div>
          <div class="weather-temp">
            <span>22°</span><small>71.6°F</small>
          </div>
        </div>
      </section>

      <section class="upcoming-flight">
        <div class="flight-banner">
          <div>
            <h3>UPCOMING FLIGHT</h3>
            <p class="flight-title">Nairobi - Doha</p>
            <p class="flight-meta">Jun 04, Sat | 4 Travelers</p>
          </div>
          <button class="btn-change">Change</button>
        </div>
      </section>

      <div class="dashboard-sections">
        <section class="recent-flights">
          <h3>Recent flights you've had</h3>
          <ul>
            <li>✈️ Nairobi - Dar-el-Salam</li>
            <li>✈️ Dubai - Nairobi</li>
            <li>✈️ Kampala - Mombasa</li>
            <li>✈️ Mombasa - Zanzibar</li>
          </ul>
          <button class="btn-view">View More</button>
        </section>

        <section class="destination-facts">
          <h3>Interesting facts about your destination</h3>
          <img src="../assets/images/doha2.jpg" alt="Doha, Qatar" />
          <ul>
            <li>Known for modern skyscrapers and futuristic architecture.</li>
            <li>Hosts FIFA World Cup 2022 final at Lusail Stadium.</li>
            <li>Has one of the fastest-growing economies globally.</li>
            <li>Famous for Souq Waqif market and The Pearl island.</li>
            <li>Summer temperatures often above 40°C (104°F).</li>
            <li>Hamad International Airport is one of the best worldwide.</li>
          </ul>
        </section>
      </div>
    </main>
  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
