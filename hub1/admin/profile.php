<?php
$hide_navbar = true;
$hide_footer = true;
$extra_css = 'admin.css';
$page_title = 'Admin Profile';
require_once __DIR__ . '/../includes/header.php';
require_admin();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([current_user_id()]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $contact = sanitize($_POST['contact_number'] ?? '');

    $pic = $admin['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_profile_picture($_FILES['profile_picture']);
        if ($uploaded) $pic = $uploaded;
    }

    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, contact_number=?, profile_picture=? WHERE id=?")
        ->execute([$first_name, $last_name, $email, $contact, $pic, current_user_id()]);

    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            set_flash('danger', 'Password must be at least 6 characters.');
            redirect(APP_URL . '/admin/profile.php');
        }
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, current_user_id()]);
    }

    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['profile_picture'] = $pic;
    set_flash('success', 'Profile updated successfully.');
    redirect(APP_URL . '/admin/profile.php');
}
?>
<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-topbar">
            <h4><i class="fas fa-user-shield me-2 text-primary"></i>Admin Profile</h4>
        </div>
        <div class="admin-content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="profile-header">
                            <img src="<?= APP_URL ?>/uploads/profiles/<?= sanitize($admin['profile_picture']) ?>"
                                 alt="Profile" class="profile-avatar"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($admin['first_name']) ?>&background=730000&color=ffbd00&size=120'">
                            <h4 class="mb-0"><?= sanitize($admin['first_name'] . ' ' . $admin['last_name']) ?></h4>
                            <span class="badge bg-warning mt-2"><i class="fas fa-shield-alt me-1"></i>Administrator</span>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= sanitize($admin['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= sanitize($admin['last_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= sanitize($admin['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Contact Number</label>
                                        <input type="tel" name="contact_number" class="form-control" value="<?= sanitize($admin['contact_number'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Profile Picture</label>
                                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password <small class="text-muted">(leave blank to keep)</small></label>
                                        <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h5 class="fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Account Info</h5>
                            <div class="row mt-3">
                                <div class="col-md-6"><p><strong>Role:</strong> Administrator</p></div>
                                <div class="col-md-6"><p><strong>Member Since:</strong> <?= date('F d, Y', strtotime($admin['created_at'])) ?></p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
