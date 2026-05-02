<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$page_title = 'Manage Donations - Admin';
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_status') {
        $new_status = sanitize($_POST['new_status'] ?? '');
        $pdo->prepare("UPDATE donations SET status=? WHERE id=?")->execute([$new_status, $id]);
        set_flash('success', 'Donation status updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM donations WHERE id=?")->execute([$id]);
        set_flash('info', 'Donation deleted.');
    } elseif ($action === 'add') {
        $pdo->prepare("INSERT INTO donations (donor_name, donation_type, items, amount, payment_method, phone)
                        VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([
                sanitize($_POST['donor_name']),
                sanitize($_POST['donation_type']),
                sanitize($_POST['items'] ?? ''),
                (float)($_POST['amount'] ?? 0),
                sanitize($_POST['payment_method'] ?? ''),
                sanitize($_POST['phone'] ?? '')
            ]);
        set_flash('success', 'Donation added.');
    }
    redirect(APP_URL . '/admin/donations.php');
}

$type_filter = $_GET['type'] ?? '';
$search = $_GET['q'] ?? '';
$where = "1=1";
$params = [];
if ($type_filter) { $where .= " AND donation_type=?"; $params[] = $type_filter; }
if ($search) { $where .= " AND donor_name LIKE ?"; $params[] = "%$search%"; }

$stmt = $pdo->prepare("SELECT * FROM donations WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$donations = $stmt->fetchAll();

$total_funds = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='monetary'")->fetchColumn();
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-box-open me-2 text-primary"></i>Donations</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDonationModal"><i class="fas fa-plus me-1"></i> Add Donation</button>
        </div>
        <div class="admin-content">
            <!-- Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card stat-blue"><div class="stat-number"><?= count(array_filter($donations, fn($d) => $d['donation_type'] === 'item')) ?></div><div class="stat-label">Item Donations</div></div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-teal"><div class="stat-number">&#8369;<?= number_format($total_funds) ?></div><div class="stat-label">Funds Raised</div></div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-emerald"><div class="stat-number"><?= count($donations) ?></div><div class="stat-label">Total Records</div></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search donor..." value="<?= sanitize($search) ?>"></div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="item" <?= $type_filter === 'item' ? 'selected' : '' ?>>Item</option>
                                <option value="monetary" <?= $type_filter === 'monetary' ? 'selected' : '' ?>>Monetary</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button></div>
                        <div class="col-md-2"><a href="<?= APP_URL ?>/admin/donations.php" class="btn btn-outline-secondary w-100">Clear</a></div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>Donor</th><th>Type</th><th>Items/Amount</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (empty($donations)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No donations yet</td></tr>
                            <?php else: foreach ($donations as $d): ?>
                            <tr>
                                <td><?= $d['id'] ?></td>
                                <td><?= sanitize($d['donor_name']) ?></td>
                                <td><span class="badge bg-<?= $d['donation_type'] === 'monetary' ? 'warning' : 'info' ?>"><?= ucfirst($d['donation_type']) ?></span></td>
                                <td><?= $d['donation_type'] === 'monetary' ? '&#8369;' . number_format($d['amount'], 2) : sanitize($d['items']) ?></td>
                                <td><?= sanitize($d['payment_method'] ?: '—') ?></td>
                                <td><?= status_badge($d['status']) ?></td>
                                <td><small><?= date('M d, Y', strtotime($d['created_at'])) ?></small></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="new_status" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="this.form.submit()">
                                            <?php foreach (['pending','received','distributed'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $d['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
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

<!-- Add Donation Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-primary text-white"><h5 class="modal-title">Add Donation</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="add">
                <div class="mb-3"><label class="form-label">Donor Name</label><input type="text" name="donor_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Type</label>
                    <select name="donation_type" class="form-select" id="donTypeSelect" onchange="document.getElementById('itemFields').style.display=this.value==='item'?'block':'none';document.getElementById('moneyFields').style.display=this.value==='monetary'?'block':'none';">
                        <option value="item">Item</option><option value="monetary">Monetary</option>
                    </select>
                </div>
                <div id="itemFields"><div class="mb-3"><label class="form-label">Items</label><input type="text" name="items" class="form-control" placeholder="e.g. 5kg Rice, Canned Goods"></div><div class="mb-3"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div></div>
                <div id="moneyFields" style="display:none;"><div class="mb-3"><label class="form-label">Amount (PHP)</label><input type="number" name="amount" class="form-control" step="0.01"></div><div class="mb-3"><label class="form-label">Payment Method</label><input type="text" name="payment_method" class="form-control" placeholder="GCash, Bank, etc."></div></div>
                <button type="submit" class="btn btn-primary w-100">Add Donation</button>
            </form>
        </div>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
