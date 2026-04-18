<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';

if($_SERVER['REQUEST_METHOD']==='POST' && current_user()['role']==='Admin'){
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $pdo->prepare('INSERT INTO message_templates(channel,category,title,body,created_by,created_at) VALUES (?,?,?,?,?,NOW())')
            ->execute([$_POST['channel'],$_POST['category'],$_POST['title'],$_POST['body'],current_user()['id']]);
        log_activity($pdo, (int)current_user()['id'], 'template_created', 'template', (int)$pdo->lastInsertId(), 'Template created');
    } elseif ($action === 'update') {
        $pdo->prepare('UPDATE message_templates SET channel=?,category=?,title=?,body=? WHERE id=?')
            ->execute([$_POST['channel'],$_POST['category'],$_POST['title'],$_POST['body'],$_POST['id']]);
        log_activity($pdo, (int)current_user()['id'], 'template_updated', 'template', (int)$_POST['id'], 'Template updated');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM message_templates WHERE id=?')->execute([$_POST['id']]);
        log_activity($pdo, (int)current_user()['id'], 'template_deleted', 'template', (int)$_POST['id'], 'Template deleted');
    }

    redirect_to('templates/index.php');
}

$templates=$pdo->query('SELECT * FROM message_templates ORDER BY channel,title')->fetchAll();
$channels = ['WhatsApp','Email','Facebook/Instagram DM','LinkedIn'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Outreach Templates</h3>
    <?php if(current_user()['role']==='Admin'): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateCreateModal">+ New Template</button>
    <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach($templates as $t): ?>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><?= e($t['title']) ?></h6>
                        <div class="small text-muted mb-2"><?= e($t['channel']) ?> / <?= e($t['category']) ?></div>
                    </div>
                    <?php if(current_user()['role']==='Admin'): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">Manage</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#templateEditModal<?= (int)$t['id'] ?>">Edit</button></li>
                                <li>
                                    <form method="post" class="px-2 py-1">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                        <button class="btn btn-sm btn-danger w-100" onclick="return confirm('Delete this template?')">Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <pre id="tpl<?= (int)$t['id'] ?>" class="bg-light p-2 rounded" style="white-space: pre-wrap"><?= e($t['body']) ?></pre>
                <button class="btn btn-sm btn-outline-secondary" data-copy-target="tpl<?= (int)$t['id'] ?>">Copy</button>
            </div>
        </div>
    </div>

    <?php if(current_user()['role']==='Admin'): ?>
    <div class="modal fade" id="templateEditModal<?= (int)$t['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg"><div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <div class="modal-header"><h5 class="modal-title">Edit Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label>Channel</label><select class="form-select" name="channel"><?php foreach($channels as $c): ?><option <?= $t['channel']===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label>Category</label><input class="form-control" name="category" value="<?= e($t['category']) ?>"></div>
                        <div class="col-md-4"><label>Title</label><input class="form-control" name="title" value="<?= e($t['title']) ?>" required></div>
                        <div class="col-md-12"><label>Body</label><textarea class="form-control" rows="6" name="body" required><?= e($t['body']) ?></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save Changes</button></div>
            </form>
        </div></div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>

<?php if(current_user()['role']==='Admin'): ?>
<div class="modal fade" id="templateCreateModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <div class="modal-header"><h5 class="modal-title">Create Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-4"><label>Channel</label><select class="form-select" name="channel"><?php foreach($channels as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label>Category</label><input class="form-control" name="category"></div>
                    <div class="col-md-4"><label>Title</label><input class="form-control" name="title" required></div>
                    <div class="col-md-12"><label>Body</label><textarea class="form-control" rows="6" name="body" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
