<?php
$db_host = 'sql110.infinityfree.com';
$db_name = 'if0_41802178_campusrelief_db';
$db_user = 'if0_41802178';
$db_pass = 'lPqw1i7u9b6jUx';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
