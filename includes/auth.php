<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit;
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
