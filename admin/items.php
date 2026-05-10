<?php
$pageTitle = 'Item Management';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$error   = '';
$success = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    // Add category
    if ($action === 'add_category') {
        $name = sanitize($_POST['category_name'] ?? '');
        if ($name) {
            $stmt = $db->prepare('INSERT INTO item_categories (name) VALUES (?)');
            $stmt->bind_param('s', $name);
            $stmt->execute() ? flashMessage('success', 'Category added.') : ($error = 'Category may already exist.');
            $stmt->close();
        }
    }

    // Add item type
    if ($action === 'add_type') {
        $catId     = (int) ($_POST['category_id'] ?? 0);
        $typeName  = sanitize($_POST['type_name'] ?? '');
        $price     = (float) ($_POST['price'] ?? 0);
        if ($catId && $typeName && $price >= 0) {
            $stmt = $db->prepare('INSERT INTO item_types (category_id, type_name, price) VALUES (?, ?, ?)');
            $stmt->bind_param('isd', $catId, $typeName, $price);
            $stmt->execute() ? flashMessage('success', 'Item type added.') : ($error = 'Failed to add item type.');
            $stmt->close();
        }
    }

    // Edit item type
    if ($action === 'edit_type') {
        $typeId   = (int) ($_POST['type_id'] ?? 0);
        $typeName = sanitize($_POST['type_name'] ?? '');
        $price    = (float) ($_POST['price'] ?? 0);
        if ($typeId && $typeName && $price >= 0) {
            $stmt = $db->prepare('UPDATE item_types SET type_name = ?, price = ? WHERE id = ?');
            $stmt->bind_param('sdi', $typeName, $price, $typeId);
            $stmt->execute() ? flashMessage('success', 'Item updated.') : ($error = 'Failed to update item.');
            $stmt->close();
        }
    }

    // Delete item type
    if ($action === 'delete_type') {
        $typeId = (int) ($_POST['type_id'] ?? 0);
        if ($typeId) {
            $db->query("DELETE FROM item_types WHERE id = $typeId");
            flashMessage('success', 'Item type deleted.');
        }
    }

    // Delete category
    if ($action === 'delete_category') {
        $catId = (int) ($_POST['cat_id'] ?? 0);
        if ($catId) {
            $db->query("DELETE FROM item_categories WHERE id = $catId");
            flashMessage('success', 'Category and all its types deleted.');
        }
    }

    header('Location: /admin/items.php');
    exit;
}

// Load data
$categories = $db->query('SELECT * FROM item_categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$itemTypes   = $db->query('
    SELECT it.*, ic.name AS category_name
    FROM item_types it
    JOIN item_categories ic ON it.category_id = ic.id
    ORDER BY ic.name, it.type_name
')->fetch_all(MYSQLI_ASSOC);

$flash = getFlash('success');

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-section">Admin Panel</div>
        <a href="/admin/index.php">&#128202; Dashboard</a>
        <a href="/admin/reservations.php">&#128203; Reservations</a>
        <a href="/admin/items.php" class="active">&#127907; Item Management</a>
        <a href="/admin/users.php">&#128101; Users</a>
        <div class="sidebar-section">Account</div>
        <a href="/pages/logout.php">&#128682; Sign Out</a>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Item Management</h1>
        <p class="page-sub">Manage categories (Ball, Pitch, Racket) and their pricing types.</p>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid-2 mb-4">
            <!-- Add Category -->
            <div class="card">
                <h2 class="card-title">Add Category</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Ball" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>

            <!-- Add Item Type -->
            <div class="card">
                <h2 class="card-title">Add Item Type</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="add_type">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Type Name</label>
                        <input type="text" name="type_name" class="form-control" placeholder="e.g. Premium" required>
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Type</button>
                </form>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--dark-3);display:flex;justify-content:space-between;align-items:center;">
                <h2 class="card-title" style="margin:0;">All Item Types</h2>
                <input type="text" id="itemSearch" class="form-control" placeholder="Search..." style="max-width:220px;">
            </div>
            <div class="table-wrap">
                <table id="itemsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Type Name</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($itemTypes as $it): ?>
                        <tr>
                            <td><?= $it['id'] ?></td>
                            <td><?= htmlspecialchars($it['category_name']) ?></td>
                            <td><?= htmlspecialchars($it['type_name']) ?></td>
                            <td style="color:var(--green);font-weight:700;">$<?= number_format($it['price'], 2) ?></td>
                            <td>
                                <div class="flex gap-1">
                                    <button class="btn btn-info btn-sm"
                                        data-modal-open="editModal"
                                        onclick="fillEditModal(<?= $it['id'] ?>, '<?= htmlspecialchars(addslashes($it['type_name'])) ?>', <?= $it['price'] ?>)">
                                        Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="delete_type">
                                        <input type="hidden" name="type_id" value="<?= $it['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            data-confirm="Delete this item type? This may affect existing reservations.">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($itemTypes)): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">No item types found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories list -->
        <div class="card mt-4" style="padding:0;overflow:hidden;">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--dark-3);">
                <h2 class="card-title" style="margin:0;">Categories</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#</th><th>Name</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Delete this category and ALL its item types? This cannot be undone.">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Item Type</span>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="action" value="edit_type">
            <input type="hidden" name="type_id" id="editTypeId">
            <div class="form-group">
                <label>Type Name</label>
                <input type="text" name="type_name" id="editTypeName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" name="price" id="editTypePrice" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function fillEditModal(id, name, price) {
    document.getElementById('editTypeId').value    = id;
    document.getElementById('editTypeName').value  = name;
    document.getElementById('editTypePrice').value = price;
}
</script>
<script>
document.addEventListener('DOMContentLoaded', () => tableSearch('itemSearch', 'itemsTable'));
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
