<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$page_title = 'Manage Requests - Admin';
require_once __DIR__ . '/../includes/header.php';
require_admin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'approve') {
        $pdo->prepare("UPDATE requests SET status='approved', admin_notes=?, updated_at=NOW() WHERE id=?")->execute([$_POST['notes'] ?? '', $id]);
        set_flash('success', 'Request approved.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE requests SET status='rejected', admin_notes=?, updated_at=NOW() WHERE id=?")->execute([$_POST['notes'] ?? '', $id]);
        set_flash('warning', 'Request rejected.');
    } elseif ($action === 'fulfill') {
        $pdo->prepare("UPDATE requests SET status='fulfilled', updated_at=NOW() WHERE id=?")->execute([$id]);
        set_flash('success', 'Request marked as fulfilled.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM requests WHERE id=?")->execute([$id]);
        set_flash('info', 'Request deleted.');
    }
    redirect(APP_URL . '/admin/requests.php');
}

$filter = $_GET['status'] ?? '';
$search = $_GET['q'] ?? '';
$where = "1=1";
$params = [];
if ($filter) { $where .= " AND r.status=?"; $params[] = $filter; }
if ($search) { $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.student_id LIKE ?)"; $s = "%$search%"; $params = array_merge($params, [$s, $s, $s]); }

$stmt = $pdo->prepare("SELECT r.*, CONCAT(u.first_name,' ',u.last_name) as student_name, u.student_id
                        FROM requests r JOIN users u ON r.user_id=u.id
                        WHERE $where ORDER BY r.created_at DESC");
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-utensils me-2 text-primary"></i>Student Requests</h4>
            <span class="badge bg-primary"><?= count($requests) ?> total</span>
        </div>
        <div class="admin-content">
            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <input type="text" name="q" class="form-control" placeholder="Search by name or ID..." value="<?= sanitize($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="fulfilled" <?= $filter === 'fulfilled' ? 'selected' : '' ?>>Fulfilled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= APP_URL ?>/admin/requests.php" class="btn btn-outline-secondary w-100">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Item Requested</th>
                                <th>Qty</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No requests found</td></tr>
                            <?php else: foreach ($requests as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td>
                                    <strong><?= sanitize($r['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= sanitize($r['student_id']) ?></small>
                                </td>
                                <td><?= sanitize($r['item_type']) ?></td>
                                <td><?= $r['quantity'] ?></td>
                                <td><?= urgency_badge($r['urgency']) ?></td>
                                <td><?= status_badge($r['status']) ?></td>
                                <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($r['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm" title="Reject"><i class="fas fa-times"></i></button>
                                        </form>
                                        <?php elseif ($r['status'] === 'approved'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="fulfill">
                                            <button class="btn btn-info btn-sm text-white" title="Mark Fulfilled"><i class="fas fa-gift"></i></button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this request?')">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                    <?php if ($r['details']): ?>
                                    <br><small class="text-muted mt-1 d-inline-block"><i class="fas fa-comment"></i> <?= sanitize(substr($r['details'], 0, 50)) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
