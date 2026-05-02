<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$page_title = 'Manage Volunteers - Admin';
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle_status') {
        $current = $pdo->prepare("SELECT status FROM volunteers WHERE id=?");
        $current->execute([$id]);
        $row = $current->fetch();
        $new = ($row && $row['status'] === 'active') ? 'inactive' : 'active';
        $pdo->prepare("UPDATE volunteers SET status=? WHERE id=?")->execute([$new, $id]);
        set_flash('success', 'Volunteer status updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM volunteers WHERE id=?")->execute([$id]);
        set_flash('info', 'Volunteer removed.');
    } elseif ($action === 'add') {
        $pdo->prepare("INSERT INTO volunteers (name, course, year_level, phone, availability) VALUES (?, ?, ?, ?, ?)")
            ->execute([sanitize($_POST['name']), sanitize($_POST['course']), sanitize($_POST['year_level'] ?? ''), sanitize($_POST['phone']), sanitize($_POST['availability'])]);
        set_flash('success', 'Volunteer added.');
    }
    redirect(APP_URL . '/admin/volunteers.php');
}

$search = $_GET['q'] ?? '';
$where = "1=1";
$params = [];
if ($search) { $where .= " AND (name LIKE ? OR course LIKE ?)"; $s = "%$search%"; $params = [$s, $s]; }

$stmt = $pdo->prepare("SELECT * FROM volunteers WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$volunteers = $stmt->fetchAll();
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-users me-2 text-primary"></i>Volunteers</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVolModal"><i class="fas fa-plus me-1"></i> Add Volunteer</button>
        </div>
        <div class="admin-content">
            <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search by name or course..." value="<?= sanitize($search) ?>"></div>
                    <div class="col-md-3"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search</button></div>
                    <div class="col-md-3"><a href="<?= APP_URL ?>/admin/volunteers.php" class="btn btn-outline-secondary w-100">Clear</a></div>
                </form>
            </div></div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>Name</th><th>Course</th><th>Phone</th><th>Availability</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (empty($volunteers)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No volunteers yet</td></tr>
                            <?php else: foreach ($volunteers as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>
                                <td><strong><?= sanitize($v['name']) ?></strong></td>
                                <td><?= sanitize($v['course'] ?? '') ?> <?= sanitize($v['year_level'] ?? '') ?></td>
                                <td><?= sanitize($v['phone'] ?? '—') ?></td>
                                <td><?= sanitize($v['availability'] ?? '—') ?></td>
                                <td><?= status_badge($v['status']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="action" value="toggle_status">
                                        <button class="btn btn-sm btn-<?= $v['status'] === 'active' ? 'warning' : 'success' ?>" title="Toggle"><i class="fas fa-<?= $v['status'] === 'active' ? 'pause' : 'play' ?>"></i></button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="action" value="delete">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
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

<div class="modal fade" id="addVolModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Add Volunteer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="add">
        <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Course</label><input type="text" name="course" class="form-control" placeholder="e.g. BSIT"></div>
        <div class="mb-3"><label class="form-label">Year Level</label><select name="year_level" class="form-select"><option value="">Select</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Availability</label><select name="availability" class="form-select"><option value="Morning (8AM-12PM)">Morning (8AM-12PM)</option><option value="Afternoon (1PM-5PM)">Afternoon (1PM-5PM)</option><option value="Anytime">Anytime</option></select></div>
        <button type="submit" class="btn btn-primary w-100">Add Volunteer</button>
    </form></div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
