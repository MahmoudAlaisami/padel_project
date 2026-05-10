<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="container">
        <a href="/index.php" class="navbar-brand">Padel<span>Pro</span></a>

        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>

        <ul class="navbar-nav">
            <li><a href="/index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>

            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="/admin/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">Admin Dashboard</a></li>
                <?php else: ?>
                    <li><a href="/pages/dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">My Dashboard</a></li>
                    <li><a href="/pages/reservation.php" class="nav-link <?= $currentPage === 'reservation.php' ? 'active' : '' ?>">Book Now</a></li>
                <?php endif; ?>
                <li>
                    <a href="/pages/logout.php" class="btn btn-outline btn-sm">Sign Out</a>
                </li>
            <?php else: ?>
                <li><a href="/pages/signin.php" class="nav-link <?= $currentPage === 'signin.php' ? 'active' : '' ?>">Sign In</a></li>
                <li><a href="/pages/signup.php" class="btn btn-primary btn-sm">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
