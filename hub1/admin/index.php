<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$use_chartjs = true;
$page_title = 'Admin Dashboard - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_admin();

// Fetch stats
$stats = [];
$stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$stats['pending_requests'] = $pdo->query("SELECT COUNT(*) FROM requests WHERE status='pending'")->fetchColumn();
$stats['approved_requests'] = $pdo->query("SELECT COUNT(*) FROM requests WHERE status='approved'")->fetchColumn();
$stats['total_donations'] = $pdo->query("SELECT COUNT(*) FROM donations WHERE donation_type='item'")->fetchColumn();
$stats['total_funds'] = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='monetary'")->fetchColumn();
$stats['active_volunteers'] = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE status='active'")->fetchColumn();
$stats['total_requests'] = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$stats['rejected_requests'] = $pdo->query("SELECT COUNT(*) FROM requests WHERE status='rejected'")->fetchColumn();

$recent_requests = $pdo->query("SELECT r.*, CONCAT(u.first_name,' ',u.last_name) as student_name, u.student_id FROM requests r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</h4>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-shield me-1"></i> Admin
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
        <div class="admin-content">
            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-amber">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-number"><?= $stats['pending_requests'] ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-emerald">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-number"><?= $stats['approved_requests'] ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-blue">
                        <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                        <div class="stat-number"><?= $stats['total_donations'] ?></div>
                        <div class="stat-label">Donations</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-teal">
                        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                        <div class="stat-number">&#8369;<?= number_format($stats['total_funds']) ?></div>
                        <div class="stat-label">Funds</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-purple">
                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                        <div class="stat-number"><?= $stats['total_students'] ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card stat-rose">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-number"><?= $stats['active_volunteers'] ?></div>
                        <div class="stat-label">Volunteers</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h5><i class="fas fa-chart-pie text-primary me-2"></i>Request Status Distribution</h5>
                        <canvas id="requestChart" height="220"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h5><i class="fas fa-chart-bar text-warning me-2"></i>Relief Overview</h5>
                        <canvas id="overviewChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="chart-card">
                <h5><i class="fas fa-history text-primary me-2"></i>Recent Requests</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Item</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_requests)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No requests yet</td></tr>
                            <?php else: foreach ($recent_requests as $r): ?>
                            <tr>
                                <td><strong><?= sanitize($r['student_name']) ?></strong><br><small class="text-muted"><?= sanitize($r['student_id']) ?></small></td>
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
    </div>
</div>

<?php
$inline_js = "
const ctx1 = document.getElementById('requestChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{
            data: [{$stats['pending_requests']}, {$stats['approved_requests']}, {$stats['rejected_requests']}],
            backgroundColor: ['#ffbd00', '#059669', '#9b000a'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

const ctx2 = document.getElementById('overviewChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Students', 'Requests', 'Item Donations', 'Volunteers'],
        datasets: [{
            label: 'Count',
            data: [{$stats['total_students']}, {$stats['total_requests']}, {$stats['total_donations']}, {$stats['active_volunteers']}],
            backgroundColor: ['#730000', '#059669', '#ffbd00', '#9b000a'],
            borderRadius: 6, borderWidth: 0
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
