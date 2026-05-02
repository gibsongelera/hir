<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Please log in to continue.'];
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Access denied.'];
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function require_student(): void {
    require_login();
    if ($_SESSION['role'] !== 'student') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Access denied.'];
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_user_name(): string {
    return $_SESSION['user_name'] ?? 'Guest';
}

function current_user_role(): string {
    return $_SESSION['role'] ?? '';
}
