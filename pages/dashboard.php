<?php
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header('Location: /admin/index.php'); exit; }

$db     = getDB();
$userId = $_SESSION['user_id'];

// Cancel reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $cancelId = (int) $_POST['cancel_id'];
        $stmt = $db->prepare('UPDATE reservations SET status = "cancelled" WHERE id = ? AND user_id = ? AND status = "pending"');
        $stmt->bind_param('ii', $cancelId, $userId);
        $stmt->execute();
        $stmt->close();
        flashMessage('success', 'Reservation cancelled.');
    }
    header('Location: /pages/dashboard.php');
    exit;
}

// Stats
$stmt = $db->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($totalRes);
$stmt->fetch();
$stmt->close();

$now = date('Y-m-d H:i:s');
$stmt = $db->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ? AND start_time > ? AND status = "approved"');
$stmt->bind_param('is', $userId, $now);
$stmt->execute();
$stmt->bind_result($upcoming);
$stmt->fetch();
$stmt->close();

$stmt = $db->prepare('SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE user_id = ? AND status != "cancelled"');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($totalSpent);
$stmt->fetch();
$stmt->close();

// Reservations list
$stmt = $db->prepare('
    SELECT r.id, r.start_time, r.end_time, r.total_price, r.status, r.created_at,
           pt.type_name AS pitch_name,
           bt.type_name AS ball_name,
           rkt.type_name AS racket_name,
           r.number_of_balls, r.number_of_rackets
    FROM reservations r
    LEFT JOIN item_types pt  ON r.pitch_type_id  = pt.id
    LEFT JOIN item_types bt  ON r.ball_type_id   = bt.id
    LEFT JOIN item_types rkt ON r.racket_type_id = rkt.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">Menu</div>
        <a href="/pages/dashboard.php" class="active">&#128203; My Reservations</a>
        <a href="/pages/reservation.php">&#10133; New Booking</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
        <p class="page-sub">Here's an overview of your padel bookings.</p>

        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid-3 mb-4">
            <div class="card stat-card">
                <div class="icon">&#128203;</div>
                <div class="card-value"><?= $totalRes ?></div>
                <div class="card-label">Total Reservations</div>
            </div>
            <div class="card stat-card">
                <div class="icon">&#9201;</div>
                <div class="card-value"><?= $upcoming ?></div>
                <div class="card-label">Upcoming Sessions</div>
            </div>
            <div class="card stat-card">
                <div class="icon">&#128176;</div>
                <div class="card-value">$<?= number_format($totalSpent, 2) ?></div>
                <div class="card-label">Total Spent</div>
            </div>
        </div>

        <!-- Action -->
        <div class="mb-3">
            <a href="/pages/reservation.php" class="btn btn-primary">&#10133; Book a Pitch</a>
        </div>

        <!-- Reservations table -->
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--dark-3);">
                <h2 class="card-title" style="margin:0;">Reservation History</h2>
            </div>
            <?php if (empty($reservations)): ?>
                <div style="padding:3rem;text-align:center;color:var(--text-muted);">
                    <div style="font-size:3rem;margin-bottom:1rem;">&#127934;</div>
                    <p>No reservations yet. <a href="/pages/reservation.php">Book your first pitch!</a></p>
                </div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pitch</th>
                            <th>Date &amp; Time</th>
                            <th>Gear</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['pitch_name']) ?></td>
                            <td>
                                <div><?= date('M d, Y', strtotime($r['start_time'])) ?></div>
                                <small style="color:var(--text-muted);">
                                    <?= date('H:i', strtotime($r['start_time'])) ?> – <?= date('H:i', strtotime($r['end_time'])) ?>
                                </small>
                            </td>
                            <td style="font-size:.82rem;color:var(--text-muted);">
                                <?php if ($r['ball_name']): ?>Balls: <?= htmlspecialchars($r['ball_name']) ?> ×<?= $r['number_of_balls'] ?><br><?php endif; ?>
                                <?php if ($r['racket_name']): ?>Rackets: <?= htmlspecialchars($r['racket_name']) ?> ×<?= $r['number_of_rackets'] ?><?php endif; ?>
                                <?php if (!$r['ball_name'] && !$r['racket_name']): ?>&mdash;<?php endif; ?>
                            </td>
                            <td style="font-weight:700;color:var(--green);">$<?= number_format($r['total_price'], 2) ?></td>
                            <td>
                                <?php
                                $badge = match($r['status']) {
                                    'approved'  => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    default     => 'badge-warning',
                                };
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="cancel_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Cancel this reservation?">Cancel</button>
                                </form>
                                <?php else: ?>
                                    <span style="color:var(--muted);font-size:.82rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
