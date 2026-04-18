<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/settings_helper.php';
require_login();
$user = current_user();
$settings = get_settings_map($pdo);
$logo = $settings['company_logo'] ?? '';
$favicon = $settings['company_favicon'] ?? '';
$systemTitle = $settings['system_title'] ?? 'Lead Management';

$notifSql = $user['role'] === 'Admin'
    ? "SELECT id,title,body,is_read,created_at FROM notifications ORDER BY created_at DESC LIMIT 8"
    : "SELECT id,title,body,is_read,created_at FROM notifications WHERE user_id IS NULL OR user_id=" . (int)$user['id'] . " ORDER BY created_at DESC LIMIT 8";
$notifRows = $pdo->query($notifSql)->fetchAll();
$notifUnread = 0;
foreach ($notifRows as $n) {
    if (!(int)$n['is_read']) $notifUnread++;
}
$extraAlerts = [];
$taskOverdue = (int)$pdo->query("SELECT COUNT(*) c FROM lead_tasks WHERE status IN ('Pending','In Progress') AND due_date < CURDATE()")->fetch()['c'];
$followupOverdue = (int)$pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND next_follow_up_date < CURDATE()")->fetch()['c'];
if ($taskOverdue > 0) $extraAlerts[] = ['title' => 'Overdue Tasks', 'body' => $taskOverdue . ' tasks are overdue.'];
if ($followupOverdue > 0) $extraAlerts[] = ['title' => 'Overdue Follow-ups', 'body' => $followupOverdue . ' leads need follow-up.'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($systemTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if ($favicon): ?>
        <link rel="icon" type="image/x-icon" href="<?= e(url($favicon)) ?>">
    <?php endif; ?>
    <link href="<?= e(url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar bg-dark text-light">
        <div class="sidebar-brand px-3 py-3 border-bottom border-secondary-subtle">
            <?php if ($logo): ?>
                <img src="<?= e(url($logo)) ?>" alt="Logo" class="brand-logo me-2">
            <?php endif; ?>
            <a class="text-decoration-none text-light fw-bold" href="<?= e(url('dashboard/index.php')) ?>">LeadManager CRM</a>
        </div>
        <nav class="nav flex-column px-2 py-3">
            <a class="nav-link text-light" href="<?= e(url('dashboard/index.php')) ?>">Dashboard</a>
            <a class="nav-link text-light" href="<?= e(url('leads/index.php')) ?>">Leads</a>
            <a class="nav-link text-light" href="<?= e(url('leads/pipeline.php')) ?>">Pipeline Board</a>
            <a class="nav-link text-light" href="<?= e(url('tasks/index.php')) ?>">Tasks</a>
            <a class="nav-link text-light" href="<?= e(url('meetings/index.php')) ?>">Meetings</a>
            <a class="nav-link text-light" href="<?= e(url('proposals/index.php')) ?>">Proposals</a>
            <a class="nav-link text-light" href="<?= e(url('notifications/index.php')) ?>">Notifications</a>
            <a class="nav-link text-light" href="<?= e(url('templates/index.php')) ?>">Templates</a>
            <a class="nav-link text-light" href="<?= e(url('reports/index.php')) ?>">Reports</a>
            <a class="nav-link text-light" href="<?= e(url('dashboard/daily.php')) ?>">Daily Work</a>
            <?php if ($user['role'] === 'Admin'): ?>
                <a class="nav-link text-light" href="<?= e(url('users/index.php')) ?>">Users</a>
                <a class="nav-link text-light" href="<?= e(url('settings/index.php')) ?>">Settings</a>
                <a class="nav-link text-light" href="<?= e(url('dashboard/activity.php')) ?>">Activity Logs</a>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="content-wrap">
        <header class="topbar bg-white border-bottom d-flex justify-content-between align-items-center px-3 py-2">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">Menu</button>
                <span class="fw-semibold"><?= e($systemTitle) ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="dropdown">
                        🔔
                        <?php if ($notifUnread + count($extraAlerts) > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $notifUnread + count($extraAlerts) ?></span><?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="width: 320px;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php foreach ($extraAlerts as $a): ?>
                            <div class="small px-2 py-1 border-bottom"><strong><?= e($a['title']) ?>:</strong> <?= e($a['body']) ?></div>
                        <?php endforeach; ?>
                        <?php foreach ($notifRows as $n): ?>
                            <div class="small px-2 py-1 border-bottom <?= (int)$n['is_read'] ? 'text-muted' : '' ?>">
                                <strong><?= e($n['title']) ?></strong><br><?= e($n['body']) ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$notifRows && !$extraAlerts): ?><div class="small text-muted px-2 py-1">No new notifications.</div><?php endif; ?>
                        <div class="pt-2 text-end"><a href="<?= e(url('notifications/index.php')) ?>" class="btn btn-sm btn-primary">Manage</a></div>
                    </div>
                </div>
                <span class="small"><?= e($user['full_name']) ?> (<?= e($user['role']) ?>)</span>
                <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-outline-dark btn-sm">Logout</a>
            </div>
        </header>
<main class="content-area p-3">
