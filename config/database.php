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

// Lightweight auto-migration for backward compatibility with older database.sql imports.
$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(150) NOT NULL,
    body VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
