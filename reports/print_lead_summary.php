<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/settings_helper.php';
require_login();
$rows=$pdo->query('SELECT id,full_name,company_name,source,service_interest,stage,priority,next_follow_up_date FROM leads WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 1000')->fetchAll();
$company = setting($pdo,'company_name','Startup Team');
$brand = setting($pdo,'report_branding','Lead Report');
$logo = setting($pdo,'company_logo','');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Lead Summary</title><style>body{font-family:Arial,sans-serif;padding:24px}.head{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:12px}.logo{height:40px}.meta{font-size:12px;color:#555}.tbl{width:100%;border-collapse:collapse}.tbl th,.tbl td{border:1px solid #ddd;padding:7px;font-size:12px}.tbl th{background:#f4f4f4;text-align:left}</style></head><body><div class="head"><div><?php if($logo): ?><img class="logo" src="<?= htmlspecialchars(url($logo)) ?>"><?php endif; ?><h3 style="margin:4px 0 0"><?= htmlspecialchars($company) ?></h3><div class="meta"><?= htmlspecialchars($brand) ?> - Lead Summary</div></div><div class="meta">Printed: <?= date('Y-m-d H:i') ?></div></div><table class="tbl"><thead><tr><th>ID</th><th>Name</th><th>Company</th><th>Source</th><th>Service</th><th>Stage</th><th>Priority</th><th>Follow-up</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?= (int)$r['id'] ?></td><td><?= htmlspecialchars($r['full_name']) ?></td><td><?= htmlspecialchars($r['company_name']) ?></td><td><?= htmlspecialchars($r['source']) ?></td><td><?= htmlspecialchars($r['service_interest']) ?></td><td><?= htmlspecialchars($r['stage']) ?></td><td><?= htmlspecialchars($r['priority']) ?></td><td><?= htmlspecialchars((string)$r['next_follow_up_date']) ?></td></tr><?php endforeach;?></tbody></table><script>window.print()</script></body></html>
