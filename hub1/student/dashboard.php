<?php
$page_title = 'My Dashboard - Campus Relief Hub';
$use_chartjs = true;
require_once __DIR__ . '/../includes/header.php';
require_student();

$uid = current_user_id();
$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$me = $user->fetch();

$stats = [];
$stats['my_requests'] = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id=?");
$stats['my_requests']->execute([$uid]);
$stats['my_requests'] = $stats['my_requests']->fetchColumn();

$stats['approved'] = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id=? AND status='approved'");
$stats['approved']->execute([$uid]);
$stats['approved'] = $stats['approved']->fetchColumn();

$stats['pending'] = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id=? AND status='pending'");
$stats['pending']->execute([$uid]);
$stats['pending'] = $stats['pending']->fetchColumn();

$stats['my_donations'] = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE user_id=?");
$stats['my_donations']->execute([$uid]);
$stats['my_donations'] = $stats['my_donations']->fetchColumn();

$stats['volunteering'] = $pdo->prepare("SELECT COUNT(*) FROM volunteers WHERE user_id=? AND status='active'");
$stats['volunteering']->execute([$uid]);
$stats['volunteering'] = $stats['volunteering']->fetchColumn();

$recent = $pdo->prepare("SELECT * FROM requests WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$recent->execute([$uid]);
$recent_requests = $recent->fetchAll();
?>
<div class="container py-4">
    <!-- Welcome -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex align-items-center gap-3 mb-2">
                <img src="<?= APP_URL ?>/uploads/profiles/<?= sanitize($me['profile_picture']) ?>"
                     alt="Profile" class="rounded-circle border border-3 border-warning" width="60" height="60" style="object-fit:cover;"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($me['first_name']) ?>&background=730000&color=ffbd00&size=60'">
                <div>
                    <h3 class="mb-0 fw-bold">Welcome, <?= sanitize($me['first_name']) ?>!</h3>
                    <small class="text-muted"><i class="fas fa-id-card me-1"></i><?= sanitize($me['student_id'] ?? 'N/A') ?> &bull; <?= sanitize($me['course'] ?? '') ?> <?= sanitize($me['year_level'] ?? '') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card stat-emerald"><div class="stat-icon"><i class="fas fa-clipboard-list"></i></div><div class="stat-number"><?= $stats['my_requests'] ?></div><div class="stat-label">My Requests</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card stat-amber"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-number"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card stat-blue"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number"><?= $stats['approved'] ?></div><div class="stat-label">Approved</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card stat-purple"><div class="stat-icon"><i class="fas fa-hand-holding-heart"></i></div><div class="stat-number"><?= $stats['my_donations'] ?></div><div class="stat-label">Donations</div></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="<?= APP_URL ?>/student/request.php" class="text-decoration-none">
                <div class="feature-card hover-lift"><div class="card-icon"><i class="fas fa-plus-circle"></i></div><h3>Request Assistance</h3><p>Submit a new food assistance request</p></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= APP_URL ?>/student/donate.php" class="text-decoration-none">
                <div class="feature-card hover-lift"><div class="card-icon"><i class="fas fa-box-open"></i></div><h3>Donate Items</h3><p>Pledge food or essential items</p></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= APP_URL ?>/student/volunteer.php" class="text-decoration-none">
                <div class="feature-card hover-lift"><div class="card-icon"><i class="fas fa-hands-helping"></i></div><h3>Volunteer</h3><p>Help organize & distribute relief</p></div>
            </a>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history text-primary me-2"></i>Recent Requests</h5>
            <a href="<?= APP_URL ?>/student/my-requests.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Item</th><th>Urgency</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($recent_requests)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No requests yet. <a href="<?= APP_URL ?>/student/request.php">Submit one now</a></td></tr>
                    <?php else: foreach ($recent_requests as $r): ?>
                    <tr>
                        <td><?= sanitize($r['item_type']) ?></td>
                        <td><?= urgency_badge($r['urgency']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                        <td><small><?= time_ago($r['created_at']) ?></small></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
