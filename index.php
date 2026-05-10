<?php
$pageTitle = 'Home';
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/navbar.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-tag">&#127934; Premium Padel Experience</div>
            <h1>Reserve Your<br><span>Padel Pitch</span><br>Instantly</h1>
            <p>Book top-quality padel courts online in seconds. Choose your pitch, gear up with balls and rackets, and play.</p>
            <div class="hero-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="/pages/reservation.php" class="btn btn-primary btn-lg">Book a Pitch</a>
                    <a href="/pages/dashboard.php" class="btn btn-outline btn-lg">My Reservations</a>
                <?php else: ?>
                    <a href="/pages/signup.php" class="btn btn-primary btn-lg">Get Started Free</a>
                    <a href="/pages/signin.php" class="btn btn-outline btn-lg">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- About -->
<section style="background: var(--dark-2); border-top: 1px solid var(--dark-3); border-bottom: 1px solid var(--dark-3);">
    <div class="container text-center">
        <div class="section-tag">About Us</div>
        <h2 class="section-title">The Smartest Way to Play Padel</h2>
        <p class="section-sub">PadelPro simplifies court reservations so you spend less time organising and more time on the court. We offer premium open and covered pitches, quality gear, and a seamless booking experience.</p>
        <div class="grid-3 mt-4">
            <div class="card text-center" style="border-top: 3px solid var(--green);">
                <div style="font-size:2.5rem;margin-bottom:.75rem;">&#127947;</div>
                <div class="card-title">Pro-Level Courts</div>
                <p style="color:var(--text-muted);font-size:.9rem;">Open and covered pitches maintained to the highest standards.</p>
            </div>
            <div class="card text-center" style="border-top: 3px solid var(--green);">
                <div style="font-size:2.5rem;margin-bottom:.75rem;">&#9201;</div>
                <div class="card-title">Instant Booking</div>
                <p style="color:var(--text-muted);font-size:.9rem;">Reserve a slot in under 60 seconds, any time of day.</p>
            </div>
            <div class="card text-center" style="border-top: 3px solid var(--green);">
                <div style="font-size:2.5rem;margin-bottom:.75rem;">&#127953;</div>
                <div class="card-title">Quality Gear</div>
                <p style="color:var(--text-muted);font-size:.9rem;">Add balls and rackets to your booking at competitive rates.</p>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section>
    <div class="container text-center">
        <div class="section-tag">Features</div>
        <h2 class="section-title">Everything You Need</h2>
        <p class="section-sub">Powerful features built for players and club managers alike.</p>
        <div class="grid-4">
            <div class="card feature-card">
                <div class="feature-icon">&#128203;</div>
                <h3>Online Reservations</h3>
                <p>Book courts 24/7 without phone calls or queues.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#128176;</div>
                <h3>Auto Pricing</h3>
                <p>Transparent pricing calculated instantly based on duration and gear.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#128101;</div>
                <h3>User Accounts</h3>
                <p>Track your booking history and upcoming sessions in one place.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#9881;</div>
                <h3>Admin Control</h3>
                <p>Full admin panel to manage pitches, gear, users, and reservations.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#128241;</div>
                <h3>Mobile Friendly</h3>
                <p>Fully responsive design — works great on any device.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#128274;</div>
                <h3>Secure & Safe</h3>
                <p>Encrypted passwords, session protection, and SQL injection prevention.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#128337;</div>
                <h3>Overlap Prevention</h3>
                <p>Smart scheduling prevents double-bookings automatically.</p>
            </div>
            <div class="card feature-card">
                <div class="feature-icon">&#127942;</div>
                <h3>Open &amp; Covered Pitches</h3>
                <p>Choose between outdoor and indoor courts based on your preference.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="section-tag">Ready to Play?</div>
        <h2 class="section-title">Book Your Pitch Today</h2>
        <p class="section-sub">Join hundreds of players who reserve their courts through PadelPro every week.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/pages/signup.php" class="btn btn-primary btn-lg">Create Free Account</a>
        <?php else: ?>
            <a href="/pages/reservation.php" class="btn btn-primary btn-lg">&#127934; Book a Pitch Now</a>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
