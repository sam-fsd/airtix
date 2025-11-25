 <!-- Sidebar -->
    <?php
    require_once __DIR__ . '/../../app/helpers/functions.php';
    $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    ?>

<aside class="sidebar">
    <div class="logo">
        <img src="../assets/images/airtix-logo.png" alt="AirTix Logo" />
        <h2>AirTix</h2>
    </div>

    <nav>
        <ul>
            <a href="../dashboard/index.php">
                <li class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                    <span>🏠</span> Home
                </li>
            </a>

            <a href="../flights/search.php">
                <li class="<?= $currentPage === 'search.php' ? 'active' : '' ?>">
                    <span>✈️</span> Book a flight
                </li>
            </a>

            <a href="../dashboard/my-bookings.php">
                <li class="<?= $currentPage === 'my-bookings.php' ? 'active' : '' ?>">
                    <span>📜</span> My Bookings
                </li>
            </a>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <button class="user-icon">👤 <a href="<?= BASE_URL ?>/app/controllers/LogoutController.php" style="color: red;">Logout</a> </button>
    </div>
</aside>
