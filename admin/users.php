<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId && $userId !== (int)$_SESSION['user_id']) {
        if ($action === 'promote') {
            $db->query("UPDATE users SET role = 'admin' WHERE id = $userId");
            flashMessage('success', 'User promoted to admin.');
        } elseif ($action === 'demote') {
            $db->query("UPDATE users SET role = 'user' WHERE id = $userId");
            flashMessage('success', 'User demoted to regular user.');
        } elseif ($action === 'delete') {
            $db->query("DELETE FROM users WHERE id = $userId");
            flashMessage('success', 'User deleted.');
        }
    } else {
        flashMessage('success', 'Cannot modify your own account here.');
    }
    header('Location: /admin/users.php');
    exit;
}

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE full_name LIKE '%" . $db->real_escape_string($search) . "%' OR email LIKE '%" . $db->real_escape_string($search) . "%'" : '';

$users = $db->query("
    SELECT u.id, u.full_name, u.email, u.role, u.created_at,
           COUNT(r.id) AS total_reservations
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$flash = getFlash('success');

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-section">Admin Panel</div>
        <a href="/admin/index.php">&#128202; Dashboard</a>
        <a href="/admin/reservations.php">&#128203; Reservations</a>
        <a href="/admin/items.php">&#127907; Item Management</a>
        <a href="/admin/users.php" class="active">&#128101; Users</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Users</h1>
        <p class="page-sub">View and manage all registered users.</p>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Search -->
        <form method="GET" class="search-bar mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <a href="/admin/users.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>

        <div class="card" style="padding:0;overflow:hidden;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Reservations</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'badge-info' : 'badge-muted' ?>">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td><?= $u['total_reservations'] ?></td>
                            <td style="color:var(--text-muted);font-size:.85rem;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <div class="flex gap-1" style="flex-wrap:wrap;">
                                    <?php if ($u['role'] === 'user'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="promote">
                                        <button type="submit" class="btn btn-info btn-sm"
                                            data-confirm="Promote this user to admin?">Promote</button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="demote">
                                        <button type="submit" class="btn btn-warning btn-sm"
                                            data-confirm="Demote this admin to regular user?">Demote</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            data-confirm="Delete this user and all their data?">Delete</button>
                                    </form>
                                </div>
                                <?php else: ?>
                                    <span style="color:var(--muted);font-size:.82rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">No users found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
