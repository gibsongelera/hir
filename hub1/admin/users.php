<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$page_title = 'Manage Students - Admin';
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle_status') {
        $current = $pdo->prepare("SELECT status FROM users WHERE id=? AND role='student'");
        $current->execute([$id]);
        $row = $current->fetch();
        $new = ($row && $row['status'] === 'active') ? 'inactive' : 'active';
        $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new, $id]);
        set_flash('success', 'Student status updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$id]);
        set_flash('info', 'Student deleted.');
    }
    redirect(APP_URL . '/admin/users.php');
}

$search = $_GET['q'] ?? '';
$where = "role='student'";
$params = [];
if ($search) { $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ? OR email LIKE ?)"; $s = "%$search%"; $params = [$s, $s, $s, $s]; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-user-graduate me-2 text-primary"></i>Registered Students</h4>
            <span class="badge bg-primary"><?= count($students) ?> students</span>
        </div>
        <div class="admin-content">
            <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search by name, ID, or email..." value="<?= sanitize($search) ?>"></div>
                    <div class="col-md-3"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search</button></div>
                    <div class="col-md-3"><a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline-secondary w-100">Clear</a></div>
                </form>
            </div></div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Gender</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No students registered</td></tr>
                            <?php else: foreach ($students as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><strong><?= sanitize($s['student_id'] ?? '—') ?></strong></td>
                                <td><?= sanitize($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                <td><small><?= sanitize($s['email']) ?></small></td>
                                <td><?= sanitize($s['course'] ?? '—') ?></td>
                                <td><?= sanitize($s['year_level'] ?? '—') ?></td>
                                <td><?= sanitize($s['gender'] ?? '—') ?></td>
                                <td><?= status_badge($s['status']) ?></td>
                                <td><small><?= date('M d, Y', strtotime($s['created_at'])) ?></small></td>
                                <td>
                                    <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="toggle_status">
                                        <button class="btn btn-sm btn-<?= $s['status'] === 'active' ? 'warning' : 'success' ?>" title="Toggle"><i class="fas fa-<?= $s['status'] === 'active' ? 'ban' : 'check' ?>"></i></button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this student and all their data?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="delete">
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
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
