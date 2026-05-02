<?php

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function flash_message(): string {
    if (!isset($_SESSION['flash'])) return '';
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = sanitize($flash['type']);
    $msg = sanitize($flash['message']);

    $icons = [
        'success' => 'fa-check-circle',
        'danger'  => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle',
    ];
    $titles = [
        'success' => 'Success',
        'danger'  => 'Error',
        'warning' => 'Warning',
        'info'    => 'Information',
    ];
    $icon = $icons[$type] ?? 'fa-bell';
    $title = $titles[$type] ?? 'Notice';

    return "<div class='crh-toast crh-toast--{$type}' role='alert' data-auto-dismiss='5000'>
                <div class='crh-toast__icon'><i class='fas {$icon}'></i></div>
                <div class='crh-toast__body'>
                    <strong class='crh-toast__title'>{$title}</strong>
                    <p class='crh-toast__msg'>{$msg}</p>
                </div>
                <button type='button' class='crh-toast__close' aria-label='Close'>
                    <i class='fas fa-times'></i>
                </button>
                <div class='crh-toast__progress'></div>
            </div>";
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function upload_profile_picture(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return null;

    $filename = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return null;
}

function time_ago(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function status_badge(string $status): string {
    $map = [
        'pending'     => 'warning',
        'approved'    => 'success',
        'rejected'    => 'danger',
        'fulfilled'   => 'info',
        'received'    => 'success',
        'distributed' => 'primary',
        'active'      => 'success',
        'inactive'    => 'secondary',
    ];
    $class = $map[$status] ?? 'secondary';
    return "<span class='badge bg-{$class}'>" . ucfirst($status) . "</span>";
}

function urgency_badge(string $urgency): string {
    $map = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
    $class = $map[$urgency] ?? 'secondary';
    return "<span class='badge bg-{$class}'>" . ucfirst($urgency) . "</span>";
}
