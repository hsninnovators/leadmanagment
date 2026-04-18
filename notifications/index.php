<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_read_all') {
        if (current_user()['role'] === 'Admin') {
            $pdo->exec('UPDATE notifications SET is_read = 1');
        } else {
            $pdo->exec('UPDATE notifications SET is_read = 1 WHERE user_id IS NULL OR user_id=' . (int)current_user()['id']);
        }
    }
    if ($action === 'create' && current_user()['role'] === 'Admin') {
        $pdo->prepare('INSERT INTO notifications (user_id,title,body,is_read,created_at) VALUES (?,?,?,?,NOW())')
            ->execute([$_POST['user_id'] ?: null, trim($_POST['title']), trim($_POST['body']), 0]);
        log_activity($pdo, (int)current_user()['id'], 'notification_created', 'notification', (int)$pdo->lastInsertId(), 'Notification created');
    }
    redirect_to('notifications/index.php');
}

$where = current_user()['role'] === 'Admin' ? '' : ' WHERE n.user_id IS NULL OR n.user_id=' . (int)current_user()['id'];
$rows = $pdo->query('SELECT n.*,u.full_name target_user FROM notifications n LEFT JOIN users u ON u.id=n.user_id' . $where . ' ORDER BY n.created_at DESC LIMIT 200')->fetchAll();
$users = $pdo->query("SELECT id,full_name FROM users WHERE status='Active' AND deleted_at IS NULL ORDER BY full_name")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Notifications</h3>
    <form method="post" class="d-inline">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="mark_read_all">
        <button class="btn btn-outline-secondary btn-sm">Mark all as read</button>
    </form>
</div>

<?php if (current_user()['role'] === 'Admin'): ?>
<div class="card card-body mb-3">
    <h6>Create Notification</h6>
    <form method="post" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3">
            <select class="form-select" name="user_id">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><input class="form-control" name="title" placeholder="Title" required></div>
        <div class="col-md-4"><input class="form-control" name="body" placeholder="Message" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Send</button></div>
    </form>
</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Time</th><th>Title</th><th>Message</th><th>Target</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['created_at']) ?></td>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['body']) ?></td>
                <td><?= e($r['target_user'] ?: 'All Users') ?></td>
                <td><?= (int)$r['is_read'] ? '<span class="badge bg-secondary">Read</span>' : '<span class="badge bg-primary">Unread</span>' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
