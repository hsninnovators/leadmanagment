<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/settings_helper.php';
require_login();
$id=(int)($_GET['id']??0);
$lead=$pdo->query('SELECT l.*,u.full_name assignee FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id WHERE l.id='.$id)->fetch();
if(!$lead) die('Lead not found');
$company = setting($pdo,'company_name','Startup Team');
$brand = setting($pdo,'report_branding','Lead Report');
$logo = setting($pdo,'company_logo','');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Lead Detail Print</title><style>body{font-family:Arial,sans-serif;padding:24px;color:#111}.head{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:12px}.logo{height:40px}.meta{font-size:12px;color:#555}.tbl{width:100%;border-collapse:collapse}.tbl th,.tbl td{border:1px solid #ddd;padding:8px;font-size:13px}.tbl th{background:#f4f4f4;text-align:left}</style></head><body><div class="head"><div><?php if($logo): ?><img class="logo" src="<?= htmlspecialchars(url($logo)) ?>"><?php endif; ?><h3 style="margin:4px 0 0"><?= htmlspecialchars($company) ?></h3><div class="meta"><?= htmlspecialchars($brand) ?> - Lead Detail</div></div><div class="meta">Printed: <?= date('Y-m-d H:i') ?></div></div><table class="tbl"><?php foreach($lead as $k=>$v):?><tr><th><?= htmlspecialchars($k) ?></th><td><?= htmlspecialchars((string)$v) ?></td></tr><?php endforeach;?></table><script>window.print()</script></body></html>
