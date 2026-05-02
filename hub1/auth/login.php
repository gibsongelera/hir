<?php
$hide_navbar = true;
$hide_footer = true;
$page_title = 'Login - Campus Relief Hub';
$extra_css = 'login.css';
require_once __DIR__ . '/../includes/header.php';

if (is_logged_in()) {
    redirect(current_user_role() === 'admin' ? APP_URL . '/admin/index.php' : APP_URL . '/student/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect(APP_URL . '/auth/login.php');
    }

    $login    = sanitize($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        set_flash('danger', 'All fields are required.');
        redirect(APP_URL . '/auth/login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR student_id = ?) AND status = 'active' LIMIT 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']         = $user['id'];
        $_SESSION['user_name']       = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

        set_flash('success', 'Welcome back, ' . sanitize($user['first_name']) . '!');
        redirect($user['role'] === 'admin' ? APP_URL . '/admin/index.php' : APP_URL . '/student/dashboard.php');
    } else {
        set_flash('danger', 'Invalid credentials or account inactive.');
        redirect(APP_URL . '/auth/login.php');
    }
}
?>

<div class="auth-page-wrapper">

    <!-- Back to Home -->
    <a href="<?= APP_URL ?>/index.php" class="auth-back-home" title="Back to Home">
        <i class="fas fa-arrow-left"></i>
        <span>Home</span>
    </a>

    <div class="auth-particles" aria-hidden="true">
        <?php for ($i = 0; $i < 12; $i++): ?>
            <span class="particle" style="--size:<?= rand(6,18) ?>px;--x:<?= rand(0,100) ?>%;--y:<?= rand(0,100) ?>%;--dur:<?= rand(14,28) ?>s;--delay:-<?= rand(0,18) ?>s;--opacity:<?= rand(3,8)/100 ?>;"></span>
        <?php endfor; ?>
    </div>

    <div class="auth-orb auth-orb--1" aria-hidden="true"></div>
    <div class="auth-orb auth-orb--2" aria-hidden="true"></div>
    <div class="auth-orb auth-orb--3" aria-hidden="true"></div>

    <div class="auth-lines" aria-hidden="true">
        <div class="auth-line auth-line--1"></div>
        <div class="auth-line auth-line--2"></div>
        <div class="auth-line auth-line--3"></div>
    </div>

    <!-- Left panel — branding -->
    <div class="auth-brand-panel">
        <div class="auth-brand-inner">
            <div class="auth-brand-seal">
                <img src="<?= APP_URL ?>/assets/img/logo.png" alt="ZPPSU Logo"
                     onerror="this.src='https://ui-avatars.com/api/?name=CRH&background=730000&color=ffbd00&size=100&bold=true'">
            </div>
            <div class="auth-brand-badge">SDG 2 — Zero Hunger</div>
            <h1 class="auth-brand-title">Campus<br><em>Relief Hub</em></h1>
            <p class="auth-brand-sub">Zamboanga Peninsula Polytechnic<br>State University</p>
            <div class="auth-brand-divider"></div>
        </div>
    </div>

    <!-- Right panel — login form -->
    <div class="auth-form-panel">
        <div class="auth-card-wrap">
            <div class="auth-card-accent"></div>

            <div class="crh-toast-container" id="crhToastContainer"><?= flash_message() ?></div>

            <div class="auth-welcome">
                <span class="auth-welcome__tag">Welcome back</span>
                <h2 class="auth-welcome__title">Sign in to your account</h2>
                <p class="auth-welcome__sub">Enter your Student ID or email to continue</p>
            </div>

            <form method="POST" class="auth-form" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="auth-field">
                    <label class="auth-field__label" for="loginInput">Student ID or Email</label>
                    <div class="auth-field__wrap">
                        <div class="auth-field__icon"><i class="fas fa-user"></i></div>
                        <input type="text" id="loginInput" name="login" class="auth-field__input"
                               placeholder="e.g. 2024-0001 or you@zppsu.edu.ph" required autofocus autocomplete="username">
                        <div class="auth-field__focus-bar"></div>
                    </div>
                </div>

                <div class="auth-field">
                    <label class="auth-field__label" for="passwordInput">Password</label>
                    <div class="auth-field__wrap">
                        <div class="auth-field__icon"><i class="fas fa-lock"></i></div>
                        <input type="password" id="passwordInput" name="password" class="auth-field__input"
                               placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="auth-field__toggle" id="togglePwd" aria-label="Show password" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                        <div class="auth-field__focus-bar"></div>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn" id="submitBtn">
                    <span class="auth-submit-btn__bg"></span>
                    <span class="auth-submit-btn__shine"></span>
                    <span class="auth-submit-btn__content">
                        <i class="fas fa-sign-in-alt"></i> <span>Sign In</span>
                    </span>
                    <span class="auth-submit-btn__loader" aria-hidden="true">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </span>
                </button>
            </form>

            <p class="auth-footer-link">
                Don't have an account?
                <a href="<?= APP_URL ?>/auth/register.php">Register here <i class="fas fa-arrow-right"></i></a>
            </p>

            <div class="auth-card-bottom">
                <i class="fas fa-shield-alt me-1"></i> Secured &amp; Confidential Platform
            </div>
        </div>
    </div>
</div>

<script>
/* ---- Toggle password visibility ---- */
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd  = document.getElementById('passwordInput');
    const icon = document.getElementById('toggleIcon');
    const show = pwd.type === 'password';
    pwd.type   = show ? 'text' : 'password';
    icon.classList.toggle('fa-eye',      !show);
    icon.classList.toggle('fa-eye-slash', show);
});

/* ---- Submit loading state ---- */
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;
});

/* ---- Field focus label float ---- */
document.querySelectorAll('.auth-field__input').forEach(input => {
    const wrap = input.closest('.auth-field__wrap');
    input.addEventListener('focus',  () => wrap.classList.add('focused'));
    input.addEventListener('blur',   () => wrap.classList.remove('focused'));
    input.addEventListener('input',  () => wrap.classList.toggle('has-value', input.value.length > 0));
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>