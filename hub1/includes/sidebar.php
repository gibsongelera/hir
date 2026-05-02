<?php
$admin_page = basename($_SERVER['PHP_SELF'], '.php');
$menu = [
    ['url' => 'index.php',      'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard',    'key' => 'index'],
    ['url' => 'requests.php',   'icon' => 'fa-utensils',       'label' => 'Requests',     'key' => 'requests'],
    ['url' => 'donations.php',  'icon' => 'fa-box-open',       'label' => 'Donations',    'key' => 'donations'],
    ['url' => 'volunteers.php', 'icon' => 'fa-users',          'label' => 'Volunteers',   'key' => 'volunteers'],
    ['url' => 'users.php',      'icon' => 'fa-user-graduate',  'label' => 'Students',     'key' => 'users'],
    ['url' => 'profile.php',    'icon' => 'fa-user-shield',    'label' => 'My Profile',   'key' => 'profile'],
];
?>
<div class="admin-sidebar d-flex flex-column flex-shrink-0 text-white">
    <div class="sidebar-brand text-center py-4 border-bottom border-secondary">
        <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo" width="60" height="60" class="rounded-circle border border-2 border-warning mb-2" onerror="this.style.display='none'">
        <h5 class="mb-0 fw-bold">ADMIN HUB</h5>
        <small class="text-warning">Campus Relief</small>
    </div>
    <ul class="nav nav-pills flex-column flex-grow-1 pt-2">
            
        <li><hr class="border-secondary my-1 mx-3"></li>
        <?php foreach ($menu as $item): ?>
        <li class="nav-item">
            <a class="nav-link <?= $admin_page === $item['key'] ? 'active-link' : 'text-white' ?>" href="<?= APP_URL ?>/admin/<?= $item['url'] ?>">
                <i class="fas <?= $item['icon'] ?> me-2"></i> <?= $item['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class="border-top border-secondary p-3">
    </div>
</div>
