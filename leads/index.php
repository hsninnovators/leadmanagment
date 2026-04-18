<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ids = $_POST['lead_ids'] ?? [];
    $action = $_POST['bulk_action'] ?? '';
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'soft_delete') {
            $stmt = $pdo->prepare("UPDATE leads SET deleted_at=NOW() WHERE id IN ($in)");
            $stmt->execute($ids);
        } elseif ($action === 'stage_update') {
            $stage = $_POST['bulk_stage'] ?? 'Follow-up';
            $stmt = $pdo->prepare("UPDATE leads SET stage=? WHERE id IN ($in)");
            $stmt->execute(array_merge([$stage], $ids));
        }
        log_activity($pdo, (int)$user['id'], 'bulk_lead_action', 'lead', null, $action . ' on '.count($ids).' leads');
    }
}

$where = ["l.deleted_at IS NULL"];
$params = [];
if ($user['role'] === 'Staff') { $where[] = 'l.assigned_user_id=?'; $params[] = $user['id']; }
if (!empty($_GET['q'])) {
    $where[] = '(l.full_name LIKE ? OR l.company_name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)';
    for ($i=0;$i<4;$i++) $params[] = '%' . $_GET['q'] . '%';
}
foreach (['source','service_interest','stage','priority','city','lead_type'] as $f) {
    if (!empty($_GET[$f])) { $where[] = "l.$f=?"; $params[] = $_GET[$f]; }
}
if (!empty($_GET['assigned_user_id'])) { $where[] = 'l.assigned_user_id=?'; $params[] = $_GET['assigned_user_id']; }

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15; $offset = ($page-1)*$perPage;
$sql = 'SELECT l.*,u.full_name assigned_name FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id WHERE '.implode(' AND ',$where).' ORDER BY l.created_at DESC LIMIT '.$perPage.' OFFSET '.$offset;
$stmt = $pdo->prepare($sql); $stmt->execute($params); $leads = $stmt->fetchAll();

$countStmt = $pdo->prepare('SELECT COUNT(*) c FROM leads l WHERE '.implode(' AND ',$where));
$countStmt->execute($params); $total = (int)$countStmt->fetch()['c'];
$pages = (int)ceil($total/$perPage);

$users = $pdo->query("SELECT id,full_name FROM users WHERE deleted_at IS NULL AND status='Active' ORDER BY full_name")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Leads</h3>
    <div>
        <a class="btn btn-outline-dark btn-sm" href="<?= e(url('leads/pipeline.php')) ?>">Pipeline</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('leads/import.php')) ?>">CSV Import</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('leads/export.php')) ?>?<?= e(http_build_query($_GET)) ?>">CSV Export</a>
        <a class="btn btn-primary btn-sm" href="<?= e(url('leads/create.php')) ?>">Add Lead</a>
    </div>
</div>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-3"><input class="form-control" name="q" placeholder="Search..." value="<?= e($_GET['q'] ?? '') ?>"></div>
    <div class="col-md-2"><select class="form-select" name="stage"><option value="">Stage</option><?php foreach(['New Lead','Contacted','Interested','Follow-up','Closed Won'] as $x): ?><option <?= ($_GET['stage']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="priority"><option value="">Priority</option><?php foreach(['Low','Medium','High'] as $x): ?><option <?= ($_GET['priority']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="lead_type"><option value="">Lead Type</option><?php foreach(['Business','Student'] as $x): ?><option <?= ($_GET['lead_type']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="assigned_user_id"><option value="">Assigned</option><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (string)($_GET['assigned_user_id']??'')===(string)$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-1"><button class="btn btn-dark w-100">Go</button></div>
</form>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.lead-check').forEach(x=>x.checked=this.checked)"></th><th>Name</th><th>Company</th><th>Source</th><th>Service</th><th>Stage</th><th>Priority</th><th>Assigned</th><th>Follow-up</th><th></th></tr></thead>
<tbody>
<?php foreach($leads as $l): ?>
<tr>
<td><input class="lead-check" type="checkbox" name="lead_ids[]" value="<?= (int)$l['id'] ?>"></td>
<td><?= e($l['full_name']) ?></td><td><?= e($l['company_name']) ?></td>
<td><span class="badge bg-<?= badge_class('source',$l['source']) ?>"><?= e($l['source']) ?></span></td>
<td><?= e($l['service_interest']) ?></td>
<td><span class="badge bg-<?= badge_class('stage',$l['stage']) ?>"><?= e($l['stage']) ?></span></td>
<td><span class="badge bg-<?= badge_class('priority',$l['priority']) ?>"><?= e($l['priority']) ?></span></td>
<td><?= e($l['assigned_name']) ?></td>
<td><?= e($l['next_follow_up_date']) ?></td>
<td><a class="btn btn-sm btn-outline-primary" href="<?= e(url('leads/view.php')) ?>?id=<?= (int)$l['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div class="d-flex gap-2 mt-2">
<select name="bulk_action" class="form-select" style="max-width:220px;"><option value="">Bulk action</option><option value="stage_update">Update Stage</option><option value="soft_delete">Soft Delete</option></select>
<select name="bulk_stage" class="form-select" style="max-width:220px;"><option>Follow-up</option><option>Contacted</option><option>Interested</option><option>Closed Won</option><option>Closed Lost</option></select>
<button class="btn btn-secondary">Apply</button>
</div>
</form>
<nav class="mt-3"><?php for($i=1;$i<=$pages;$i++): ?><a class="btn btn-sm <?= $i===$page?'btn-dark':'btn-outline-dark' ?>" href="?<?= e(http_build_query(array_merge($_GET,['page'=>$i]))) ?>"><?= $i ?></a><?php endfor; ?></nav>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
