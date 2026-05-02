<?php
$page_title = 'My Profile - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_student();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $first_name = sanitize($_POST['first_name']);
    $last_name  = sanitize($_POST['last_name']);
    $email      = sanitize($_POST['email']);
    $gender     = sanitize($_POST['gender'] ?? '');
    $dob        = sanitize($_POST['date_of_birth'] ?? '');
    $course     = sanitize($_POST['course'] ?? '');
    $year_level = sanitize($_POST['year_level'] ?? '');
    $contact    = sanitize($_POST['contact_number'] ?? '');
    $address    = sanitize($_POST['address'] ?? '');

    // Check email uniqueness
    $check = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
    $check->execute([$email, current_user_id()]);
    if ($check->fetch()) {
        set_flash('danger', 'Email already in use.');
        redirect(APP_URL . '/student/profile.php');
    }

    $pic = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_profile_picture($_FILES['profile_picture']);
        if ($uploaded) $pic = $uploaded;
    }

    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, gender=?, date_of_birth=?, course=?, year_level=?, contact_number=?, address=?, profile_picture=? WHERE id=?")
        ->execute([$first_name, $last_name, $email, $gender ?: null, $dob ?: null, $course ?: null, $year_level ?: null, $contact ?: null, $address ?: null, $pic, current_user_id()]);

    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            set_flash('danger', 'Password must be at least 6 characters.');
            redirect(APP_URL . '/student/profile.php');
        }
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, current_user_id()]);
    }

    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['profile_picture'] = $pic;
    set_flash('success', 'Profile updated successfully!');
    redirect(APP_URL . '/student/profile.php');
}
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="profile-header">
                    <img src="<?= APP_URL ?>/uploads/profiles/<?= sanitize($user['profile_picture']) ?>"
                         alt="Profile" class="profile-avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['first_name'] . '+' . $user['last_name']) ?>&background=730000&color=ffbd00&size=120'">
                    <h4 class="mb-0"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                    <p class="mb-0 mt-1 opacity-75"><i class="fas fa-id-card me-1"></i><?= sanitize($user['student_id'] ?? 'N/A') ?></p>
                    <span class="badge bg-warning mt-2"><?= sanitize(($user['course'] ?? '') . ' ' . ($user['year_level'] ?? '')) ?></span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fas fa-user text-primary me-2"></i>Personal Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?= sanitize($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?= sanitize($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Student ID</label>
                                <input type="text" class="form-control bg-light" value="<?= sanitize($user['student_id'] ?? 'N/A') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['Male','Female','Other'] as $g): ?>
                                    <option value="<?= $g ?>" <?= ($user['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?= sanitize($user['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="tel" name="contact_number" class="form-control" value="<?= sanitize($user['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fas fa-graduation-cap text-primary me-2"></i>Academic Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Course</label>
                                <input type="text" name="course" class="form-control" value="<?= sanitize($user['course'] ?? '') ?>" placeholder="e.g. BSIT, BSCS">
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
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="City, Province"><?= sanitize($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fas fa-cog text-primary me-2"></i>Account Settings</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control" accept="image/*">
                                <small class="text-muted">Max 2MB (JPG, PNG, GIF)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Password <small class="text-muted">(leave blank to keep)</small></label>
                                <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Profile</button>
                        <a href="<?= APP_URL ?>/student/dashboard.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Account Info</h5>
                    <div class="row mt-3">
                        <div class="col-md-4"><p><strong>Role:</strong> Student</p></div>
                        <div class="col-md-4"><p><strong>Status:</strong> <?= status_badge($user['status']) ?></p></div>
                        <div class="col-md-4"><p><strong>Member Since:</strong> <?= date('F d, Y', strtotime($user['created_at'])) ?></p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
