 <!-- Sidebar -->
    <?php // Get current page path
// Get current page path
// Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    // Get current page path
    $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>

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
        <button class="user-icon">👤 <a href="<?= BASE_URL ?>/app/controller/LogoutController"></a> Logout</button>
    </div>
</aside>
