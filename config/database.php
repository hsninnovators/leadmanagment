<?php
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    die('Missing config/config.php. Copy config/config.sample.php to config/config.php and update values.');
}

$config = require $configFile;
date_default_timezone_set($config['timezone'] ?? 'UTC');

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
