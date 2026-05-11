<?php
$pageTitle = 'Book a Pitch';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header('Location: /admin/index.php'); exit; }

$db     = getDB();
$userId = $_SESSION['user_id'];
$error  = '';
$success = '';

// Load item types grouped by category
function getTypesByCategory(string $categoryName): array {
    $db = getDB();
    $stmt = $db->prepare('
        SELECT it.id, it.type_name, it.price
        FROM item_types it
        JOIN item_categories ic ON it.category_id = ic.id
        WHERE ic.name = ?
        ORDER BY it.price ASC
    ');
    $stmt->bind_param('s', $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

$pitches = getTypesByCategory('Pitch');
$balls   = getTypesByCategory('Ball');
$rackets = getTypesByCategory('Racket');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $pitchTypeId  = (int) ($_POST['pitch_type_id'] ?? 0);
        $ballTypeId   = (int) ($_POST['ball_type_id'] ?? 0);
        $racketTypeId = (int) ($_POST['racket_type_id'] ?? 0);
        $numBalls     = max(0, (int) ($_POST['number_of_balls'] ?? 0));
        $numRackets   = max(0, (int) ($_POST['number_of_rackets'] ?? 0));
        $startTime    = sanitize($_POST['start_time'] ?? '');
        $endTime      = sanitize($_POST['end_time'] ?? '');

        $start = DateTime::createFromFormat('Y-m-d\TH:i', $startTime);
        $end   = DateTime::createFromFormat('Y-m-d\TH:i', $endTime);

        if (!$pitchTypeId) {
            $error = 'Please select a pitch type.';
        } elseif (!$start || !$end) {
            $error = 'Please select valid start and end times.';
        } elseif ($end <= $start) {
            $error = 'End time must be after start time.';
        } elseif ($start < new DateTime()) {
            $error = 'Start time must be in the future.';
        } else {
            $startStr = $start->format('Y-m-d H:i:s');
            $endStr   = $end->format('Y-m-d H:i:s');

            if (hasOverlap($pitchTypeId, $startStr, $endStr)) {
                $error = 'This pitch is already booked for the selected time slot. Please choose another time.';
            } else {
                $pricing = calculatePrice($pitchTypeId, $ballTypeId, $racketTypeId, $numBalls, $numRackets, $startStr, $endStr);
                $total   = $pricing['grand_total'];

                $ballId   = $ballTypeId   > 0 && $numBalls   > 0 ? $ballTypeId   : null;
                $racketId = $racketTypeId > 0 && $numRackets > 0 ? $racketTypeId : null;

                $stmt = $db->prepare('
                    INSERT INTO reservations
                    (user_id, pitch_type_id, ball_type_id, racket_type_id, number_of_balls, number_of_rackets, start_time, end_time, total_price, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")
                ');
                $stmt->bind_param('iiiiiisd', $userId, $pitchTypeId, $ballId, $racketId, $numBalls, $numRackets, $startStr, $endStr, $total);
                // Note: mysqli doesn't directly support null via bind_param easily; using alternative
                $stmt->close();

                // Use query with proper null handling
                $ballIdSql   = $ballId   !== null ? $ballId   : 'NULL';
                $racketIdSql = $racketId !== null ? $racketId : 'NULL';
                $safeStart   = $db->real_escape_string($startStr);
                $safeEnd     = $db->real_escape_string($endStr);

                $sql = "INSERT INTO reservations
                    (user_id, pitch_type_id, ball_type_id, racket_type_id, number_of_balls, number_of_rackets, start_time, end_time, total_price, status)
                    VALUES ($userId, $pitchTypeId, $ballIdSql, $racketIdSql, $numBalls, $numRackets, '$safeStart', '$safeEnd', $total, 'pending')";

                if ($db->query($sql)) {
                    flashMessage('success', 'Reservation submitted! Awaiting admin approval.');
                    header('Location: /pages/dashboard.php');
                    exit;
                } else {
                    $error = 'Failed to create reservation. Please try again.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-section">Menu</div>
        <a href="/pages/dashboard.php">&#128203; My Reservations</a>
        <a href="/pages/reservation.php" class="active">&#10133; New Booking</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Book a Pitch</h1>
        <p class="page-sub">Fill in the details below to reserve your padel court.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div style="max-width: 680px;">
            <div class="card">
                <form id="reservationForm" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <!-- Pitch -->
                    <div class="form-group">
                        <label for="pitch_type_id">Pitch Type <span style="color:var(--danger)">*</span></label>
                        <select id="pitch_type_id" name="pitch_type_id" class="form-control" required>
                            <option value="">— Select a pitch —</option>
                            <?php foreach ($pitches as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    data-price="<?= $p['price'] ?>"
                                    <?= ($_POST['pitch_type_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['type_name']) ?> — $<?= number_format($p['price'], 2) ?>/hr
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date & Time -->
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="start_time">Start Time <span style="color:var(--danger)">*</span></label>
                            <input type="datetime-local" id="start_time" name="start_time" class="form-control"
                                value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_time">End Time <span style="color:var(--danger)">*</span></label>
                            <input type="datetime-local" id="end_time" name="end_time" class="form-control"
                                value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Balls -->
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="ball_type_id">Ball Type</label>
                            <select id="ball_type_id" name="ball_type_id" class="form-control">
                                <option value="0" data-price="0">— None —</option>
                                <?php foreach ($balls as $b): ?>
                                    <option value="<?= $b['id'] ?>"
                                        data-price="<?= $b['price'] ?>"
                                        <?= ($_POST['ball_type_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['type_name']) ?> — $<?= number_format($b['price'], 2) ?>/ball
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="number_of_balls">Number of Balls</label>
                            <input type="number" id="number_of_balls" name="number_of_balls" class="form-control"
                                min="0" max="50" value="<?= (int)($_POST['number_of_balls'] ?? 0) ?>">
                        </div>
                    </div>

                    <!-- Rackets -->
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="racket_type_id">Racket Type</label>
                            <select id="racket_type_id" name="racket_type_id" class="form-control">
                                <option value="0" data-price="0">— None —</option>
                                <?php foreach ($rackets as $rk): ?>
                                    <option value="<?= $rk['id'] ?>"
                                        data-price="<?= $rk['price'] ?>"
                                        <?= ($_POST['racket_type_id'] ?? '') == $rk['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rk['type_name']) ?> — $<?= number_format($rk['price'], 2) ?>/racket
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="number_of_rackets">Number of Rackets</label>
                            <input type="number" id="number_of_rackets" name="number_of_rackets" class="form-control"
                                min="0" max="20" value="<?= (int)($_POST['number_of_rackets'] ?? 0) ?>">
                        </div>
                    </div>

                    <!-- Price summary (JS) -->
                    <div id="priceSummary" class="price-box" style="display:none;">
                        <h4 style="margin-bottom:.75rem;font-size:.95rem;">Price Summary</h4>
                        <div class="price-row"><span>Duration</span><span id="priceHours">—</span></div>
                        <div class="price-row"><span>Pitch cost</span><span id="pricePitch">—</span></div>
                        <div class="price-row"><span>Balls cost</span><span id="priceBalls">—</span></div>
                        <div class="price-row"><span>Rackets cost</span><span id="priceRackets">—</span></div>
                        <div class="price-row total"><span>Grand Totalkkkkkk</span><span id="priceTotal">—</span></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3 btn-lg">Confirm Reservation</button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
