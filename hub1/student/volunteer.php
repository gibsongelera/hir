<?php
$page_title = 'Volunteer - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_student();

$me = $pdo->prepare("SELECT * FROM users WHERE id=?");
$me->execute([current_user_id()]);
$user = $me->fetch();

// Check if already registered
$existing = $pdo->prepare("SELECT * FROM volunteers WHERE user_id=?");
$existing->execute([current_user_id()]);
$already = $existing->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    if ($already) {
        set_flash('warning', 'You are already registered as a volunteer.');
        redirect(APP_URL . '/student/volunteer.php');
    }

    $name = sanitize($_POST['name'] ?? ($user['first_name'] . ' ' . $user['last_name']));
    $course = sanitize($_POST['course'] ?? '');
    $year_level = sanitize($_POST['year_level'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $availability = sanitize($_POST['availability'] ?? '');

    $pdo->prepare("INSERT INTO volunteers (user_id, name, course, year_level, phone, availability) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([current_user_id(), $name, $course, $year_level, $phone, $availability]);

    set_flash('success', 'Thank you for volunteering! You are now registered.');
    redirect(APP_URL . '/student/dashboard.php');
}
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <h2 class="fw-bold"><i class="fas fa-hands-helping text-primary me-2"></i>Become a Volunteer</h2>
                <p class="text-muted">Help organize, pack, and distribute relief goods to fellow students.</p>
            </div>

            <?php if ($already): ?>
            <div class="form-card text-center">
                <div class="mb-3"><i class="fas fa-check-circle fa-3x text-success"></i></div>
                <h4 class="fw-bold text-success">You're Already Registered!</h4>
                <p class="text-muted">Thank you for being a volunteer, <?= sanitize($user['first_name']) ?>.</p>
                <div class="bg-light p-3 rounded mb-3 text-start">
                    <p class="mb-1"><strong>Status:</strong> <?= status_badge($already['status']) ?></p>
                    <p class="mb-1"><strong>Availability:</strong> <?= sanitize($already['availability'] ?? 'Not set') ?></p>
                    <p class="mb-0"><strong>Registered:</strong> <?= date('M d, Y', strtotime($already['created_at'])) ?></p>
                </div>
                <a href="<?= APP_URL ?>/student/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
            <?php else: ?>
            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course</label>
                            <input type="text" name="course" class="form-control" value="<?= sanitize($user['course'] ?? '') ?>" placeholder="e.g. BSIT">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Year Level</label>
                            <select name="year_level" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $y): ?>
                                <option value="<?= $y ?>" <?= ($user['year_level'] ?? '') === $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Preferred Schedule <span class="text-danger">*</span></label>
                        <select name="availability" class="form-select" required>
                            <option value="" disabled selected>Select availability</option>
                            <option value="Morning (8AM-12PM)">Morning (8AM-12PM)</option>
                            <option value="Afternoon (1PM-5PM)">Afternoon (1PM-5PM)</option>
                            <option value="Anytime">Anytime</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fas fa-user-plus me-1"></i> Join Volunteer Team
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
