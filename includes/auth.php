<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function app_base_path(): string
{
    static $basePath = null;
    if ($basePath !== null) {
        return $basePath;
    }

    $configFile = __DIR__ . '/../config/config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
        $path = parse_url($config['base_url'] ?? '', PHP_URL_PATH);
        $basePath = rtrim((string)$path, '/');
        return $basePath ?: '';
    }

    return '';
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return app_base_path() . $path;
}

function redirect_to(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_to('auth/login.php');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_role(string $role): void
{
    require_login();
    if ((current_user()['role'] ?? '') !== $role) {
        http_response_code(403);
        die('Access denied');
    }
}

function can_view_lead(array $lead): bool
{
    $user = current_user();
    if (!$user) return false;
    if ($user['role'] === 'Admin') return true;
    return (int)$lead['assigned_user_id'] === (int)$user['id'];
}
