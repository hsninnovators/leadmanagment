<?php require_once __DIR__ . '/../includes/header.php'; require_role('Admin');
$rows=$pdo->query('SELECT a.*,u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 300')->fetchAll();
?><h3>Activity Logs</h3><div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>User</th><th>Action</th><th>Entity</th><th>Description</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?= e($r['created_at']) ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['action']) ?></td><td><?= e(($r['entity_type']??'').'#'.($r['entity_id']??'')) ?></td><td><?= e($r['description']) ?></td></tr><?php endforeach;?></tbody></table></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
