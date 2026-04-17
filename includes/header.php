<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lead Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= e(url('dashboard/index.php')) ?>">LeadManager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('dashboard/index.php')) ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('leads/index.php')) ?>">Leads</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('tasks/index.php')) ?>">Tasks</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('meetings/index.php')) ?>">Meetings</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('proposals/index.php')) ?>">Proposals</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('templates/index.php')) ?>">Templates</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('reports/index.php')) ?>">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('dashboard/daily.php')) ?>">Daily Work</a></li>
                <?php if ($user['role'] === 'Admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('users/index.php')) ?>">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('settings/index.php')) ?>">Settings</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text text-light me-3"><?= e($user['full_name']) ?> (<?= e($user['role']) ?>)</span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>
<main class="container-fluid py-4">
