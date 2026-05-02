<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
$_current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (isset($use_leaflet) && $use_leaflet): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>
    <?php if (isset($use_chartjs) && $use_chartjs): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <?php if (isset($extra_css)): ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/<?= $extra_css ?>">
    <?php endif; ?>
</head>
<body>
<?php if (!isset($hide_navbar)): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/index.php">
            <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo" width="40" height="40" class="rounded-circle border border-2 border-warning" onerror="this.style.display='none'">
            <span class="fw-bold"><?= APP_NAME ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php#map-section"><i class="fas fa-map-marker-alt"></i> Find Food Nearby</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php#ways-to-give"><i class="fas fa-heart"></i> Donate</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (current_user_role() === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link btn btn-outline-warning btn-sm px-3" href="<?= APP_URL ?>/admin/index.php"><i class="fas fa-tachometer-alt"></i> Admin Panel</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/student/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= sanitize(current_user_name()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (current_user_role() === 'student'): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/student/profile.php"><i class="fas fa-id-card me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/student/my-requests.php"><i class="fas fa-list me-2"></i>My Requests</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn btn-outline-warning btn-sm px-3" href="<?= APP_URL ?>/auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="<?= isset($no_container) ? '' : 'main-wrapper' ?>">
<div class="crh-toast-container" id="crhToastContainer">
<?= flash_message() ?>
</div>
