<?php
$page_title = 'My Requests - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_student();

$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([current_user_id()]);
$requests = $stmt->fetchAll();

$total = count($requests);
$pending = count(array_filter($requests, fn($r) => $r['status'] === 'pending'));
$approved = count(array_filter($requests, fn($r) => $r['status'] === 'approved'));
$rejected = count(array_filter($requests, fn($r) => $r['status'] === 'rejected'));
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-list-alt text-primary me-2"></i>My Requests</h2>
        <a href="<?= APP_URL ?>/student/request.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Request</a>
    </div>

    <!-- Mini stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="stat-card stat-emerald"><div class="stat-number"><?= $total ?></div><div class="stat-label">Total</div></div></div>
        <div class="col-md-3 col-6"><div class="stat-card stat-amber"><div class="stat-number"><?= $pending ?></div><div class="stat-label">Pending</div></div></div>
        <div class="col-md-3 col-6"><div class="stat-card stat-blue"><div class="stat-number"><?= $approved ?></div><div class="stat-label">Approved</div></div></div>
        <div class="col-md-3 col-6"><div class="stat-card stat-rose"><div class="stat-number"><?= $rejected ?></div><div class="stat-label">Rejected</div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>#</th><th>Item Requested</th><th>Qty</th><th>Urgency</th><th>Status</th><th>Admin Notes</th><th>Submitted</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No requests yet. <a href="<?= APP_URL ?>/student/request.php" class="fw-bold">Submit your first request</a>
                    </td></tr>
                    <?php else: foreach ($requests as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= sanitize($r['item_type']) ?></strong>
                            <?php if ($r['details']): ?><br><small class="text-muted"><?= sanitize(substr($r['details'], 0, 60)) ?></small><?php endif; ?>
                        </td>
                        <td><?= $r['quantity'] ?></td>
                        <td><?= urgency_badge($r['urgency']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                        <td><small><?= sanitize($r['admin_notes'] ?? '—') ?></small></td>
                        <td><small><?= date('M d, Y g:ia', strtotime($r['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
