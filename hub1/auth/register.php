<?php
$hide_navbar = true;
$hide_footer = true;
$page_title = 'Register - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';

if (is_logged_in()) {
    redirect(APP_URL . '/student/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect(APP_URL . '/auth/register.php');
    }

    $student_id    = sanitize($_POST['student_id'] ?? '');
    $first_name    = sanitize($_POST['first_name'] ?? '');
    $last_name     = sanitize($_POST['last_name'] ?? '');
    $email         = sanitize($_POST['email'] ?? '');
    $gender        = sanitize($_POST['gender'] ?? '');
    $dob           = sanitize($_POST['date_of_birth'] ?? '');
    $course        = sanitize($_POST['course'] ?? '');
    $year_level    = sanitize($_POST['year_level'] ?? '');
    $contact       = sanitize($_POST['contact_number'] ?? '');
    $address       = sanitize($_POST['address'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        set_flash('danger', 'Please fill in all required fields.');
        redirect(APP_URL . '/auth/register.php');
    }

    if ($password !== $confirm) {
        set_flash('danger', 'Passwords do not match.');
        redirect(APP_URL . '/auth/register.php');
    }

    if (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters.');
        redirect(APP_URL . '/auth/register.php');
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
    $check->execute([$email, $student_id]);
    if ($check->fetch()) {
        set_flash('danger', 'Student ID or Email already registered.');
        redirect(APP_URL . '/auth/register.php');
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (student_id, first_name, last_name, email, password, gender, date_of_birth, course, year_level, contact_number, address)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $first_name, $last_name, $email, $hashed, $gender ?: null, $dob ?: null, $course ?: null, $year_level ?: null, $contact ?: null, $address ?: null]);

    set_flash('success', 'Registration successful! You can now log in.');
    redirect(APP_URL . '/auth/login.php');
}
?>
<div class="auth-page" style="padding: 2rem 1rem;">

    <a href="<?= APP_URL ?>/index.php" class="auth-back-home" title="Back to Home">
        <i class="fas fa-arrow-left"></i> <span>Home</span>
    </a>

    <div class="auth-card" style="max-width: 520px;">
        <div class="text-center">
            <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo" class="auth-logo"
                 onerror="this.src='https://ui-avatars.com/api/?name=CRH&background=730000&color=ffbd00&size=70'">
            <h2 class="fw-bold">Create Account</h2>
            <p class="auth-subtitle">Join the Campus Relief Hub community</p>
        </div>

        <div class="crh-toast-container" id="crhToastContainer"><?= flash_message() ?></div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
                    <input type="text" name="student_id" class="form-control" placeholder="e.g. 2024-0001" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="you@email.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course</label>
                    <input type="text" name="course" class="form-control" placeholder="e.g. BSIT">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Year Level</label>
                    <select name="year_level" class="form-select">
                        <option value="">Select</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" placeholder="09XXXXXXXXX">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address</label>
                    <input type="text" name="address" class="form-control" placeholder="City, Province">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fas fa-user-plus me-1"></i> Create Account
                    </button>
                </div>
            </div>
        </form>

        <p class="text-center mt-3 mb-0">Already have an account?
            <a href="<?= APP_URL ?>/auth/login.php" class="fw-bold">Sign in</a>
        </p>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
