<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Stats
$totalUsers = $db->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetch_row()[0];
$totalRes   = $db->query('SELECT COUNT(*) FROM reservations')->fetch_row()[0];
$totalRev   = $db->query('SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE status = "approved"')->fetch_row()[0];
$pendingRes = $db->query('SELECT COUNT(*) FROM reservations WHERE status = "pending"')->fetch_row()[0];

// Recent reservations
$recent = $db->query('
    SELECT r.id, u.full_name, pt.type_name AS pitch, r.start_time, r.end_time, r.total_price, r.status
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN item_types pt ON r.pitch_type_id = pt.id
    ORDER BY r.created_at DESC
    LIMIT 8
')->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">Admin Panel</div>
        <a href="/admin/index.php" class="active">&#128202; Dashboard</a>
        <a href="/admin/reservations.php">&#128203; Reservations</a>
        <a href="/admin/items.php">&#127907; Item Management</a>
        <a href="/admin/users.php">&#128101; Users</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-sub">Overview of the padel reservation system.</p>

        <!-- Stats -->
        <div class="grid-4 mb-4">
            <div class="card stat-card">
                <div class="icon">&#128101;</div>
                <div class="card-value"><?= $totalUsers ?></div>
                <div class="card-label">Registered Users</div>
            </div>
            <div class="card stat-card">
                <div class="icon">&#128203;</div>
                <div class="card-value"><?= $totalRes ?></div>
                <div class="card-label">Total Reservations</div>
            </div>
            <div class="card stat-card">
                <div class="icon">&#128176;</div>
                <div class="card-value">$<?= number_format($totalRev, 2) ?></div>
                <div class="card-label">Total Revenue</div>
            </div>
            <div class="card stat-card">
                <div class="icon">&#9201;</div>
                <div class="card-value"><?= $pendingRes ?></div>
                <div class="card-label">Pending Approvals</div>
            </div>
        </div>

        <!-- Quick links -->
        <div class="flex gap-2 mb-4" style="flex-wrap:wrap;">
            <a href="/admin/reservations.php" class="btn btn-primary">&#128203; Manage Reservations</a>
            <a href="/admin/items.php" class="btn btn-outline">&#127907; Manage Items</a>
            <a href="/admin/users.php" class="btn btn-outline">&#128101; Manage Users</a>
        </div>

        <!-- Recent reservations -->
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--dark-3); display:flex; justify-content:space-between; align-items:center;">
                <h2 class="card-title" style="margin:0;">Recent Reservations</h2>
                <a href="/admin/reservations.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Pitch</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= htmlspecialchars($r['pitch']) ?></td>
                            <td>
                                <?= date('M d, Y', strtotime($r['start_time'])) ?>
                                <small style="color:var(--text-muted);display:block;">
                                    <?= date('H:i', strtotime($r['start_time'])) ?> – <?= date('H:i', strtotime($r['end_time'])) ?>
                                </small>
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
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent)): ?>
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">No reservations yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
