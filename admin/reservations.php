<?php
$pageTitle = 'Manage Reservations';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Actions: approve / cancel / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $resId  = (int) ($_POST['res_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($resId > 0) {
        if ($action === 'approve') {
            $db->query("UPDATE reservations SET status = 'approved' WHERE id = $resId");
            flashMessage('success', 'Reservation approved.');
        } elseif ($action === 'cancel') {
            $db->query("UPDATE reservations SET status = 'cancelled' WHERE id = $resId");
            flashMessage('success', 'Reservation cancelled.');
        } elseif ($action === 'delete') {
            $db->query("DELETE FROM reservations WHERE id = $resId");
            flashMessage('success', 'Reservation deleted.');
        }
    }
    header('Location: /admin/reservations.php');
    exit;
}

// Filters
$whereClause = '1=1';
$params = [];

$filterStatus = sanitize($_GET['status'] ?? '');
$filterUser   = sanitize($_GET['user'] ?? '');
$filterDate   = sanitize($_GET['date'] ?? '');

if ($filterStatus) $whereClause .= " AND r.status = '" . $db->real_escape_string($filterStatus) . "'";
if ($filterUser)   $whereClause .= " AND u.full_name LIKE '%" . $db->real_escape_string($filterUser) . "%'";
if ($filterDate)   $whereClause .= " AND DATE(r.start_time) = '" . $db->real_escape_string($filterDate) . "'";

$reservations = $db->query("
    SELECT r.id, u.full_name, u.email, pt.type_name AS pitch,
           bt.type_name AS ball_type, rkt.type_name AS racket_type,
           r.number_of_balls, r.number_of_rackets,
           r.start_time, r.end_time, r.total_price, r.status, r.created_at
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN item_types pt ON r.pitch_type_id = pt.id
    LEFT JOIN item_types bt  ON r.ball_type_id   = bt.id
    LEFT JOIN item_types rkt ON r.racket_type_id = rkt.id
    WHERE $whereClause
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-section">Admin Panel</div>
        <a href="/admin/index.php">&#128202; Dashboard</a>
        <a href="/admin/reservations.php" class="active">&#128203; Reservations</a>
        <a href="/admin/items.php">&#127907; Item Management</a>
        <a href="/admin/users.php">&#128101; Users</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Reservations</h1>
        <p class="page-sub">View, approve, cancel, or delete reservations.</p>

        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="GET" class="card mb-3" style="padding:1.25rem;">
            <div class="flex gap-2" style="flex-wrap:wrap;align-items:flex-end;">
                <div class="form-group" style="margin:0;flex:1;min-width:160px;">
                    <label>Filter by User</label>
                    <input type="text" name="user" class="form-control" placeholder="Name..." value="<?= htmlspecialchars($filterUser) ?>">
                </div>
                <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                    <label>Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
                </div>
                <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="pending"   <?= $filterStatus === 'pending'   ? 'selected' : '' ?>>Pending</option>
                        <option value="approved"  <?= $filterStatus === 'approved'  ? 'selected' : '' ?>>Approved</option>
                        <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/reservations.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>

        <div class="card" style="padding:0;overflow:hidden;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Pitch</th>
                            <th>Date &amp; Time</th>
                            <th>Gear</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td>
                                <div><?= htmlspecialchars($r['full_name']) ?></div>
                                <small style="color:var(--text-muted);"><?= htmlspecialchars($r['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($r['pitch']) ?></td>
                            <td>
                                <?= date('M d, Y', strtotime($r['start_time'])) ?>
                                <small style="color:var(--text-muted);display:block;">
                                    <?= date('H:i', strtotime($r['start_time'])) ?> – <?= date('H:i', strtotime($r['end_time'])) ?>
                                </small>
                            </td>
                            <td style="font-size:.82rem;color:var(--text-muted);">
                                <?php if ($r['ball_type']): ?>Balls: <?= htmlspecialchars($r['ball_type']) ?> ×<?= $r['number_of_balls'] ?><br><?php endif; ?>
                                <?php if ($r['racket_type']): ?>Rackets: <?= htmlspecialchars($r['racket_type']) ?> ×<?= $r['number_of_rackets'] ?><?php endif; ?>
                                <?php if (!$r['ball_type'] && !$r['racket_type']): ?>&mdash;<?php endif; ?>
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
                                <div class="flex gap-1" style="flex-wrap:wrap;">
                                    <?php if ($r['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="res_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-info btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="res_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-warning btn-sm" data-confirm="Cancel this reservation?">Cancel</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="res_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Permanently delete this reservation?">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservations)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2.5rem;">No reservations found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
