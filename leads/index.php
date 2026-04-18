<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ids = array_map('intval', $_POST['lead_ids'] ?? []);
    $ids = array_values(array_filter($ids));
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
        } elseif ($action === 'assign_update') {
            $assignUser = (int)($_POST['bulk_assigned_user_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE leads SET assigned_user_id=? WHERE id IN ($in)");
            $stmt->execute(array_merge([$assignUser], $ids));
            foreach ($ids as $leadId) {
                $pdo->prepare('INSERT INTO lead_assignment_history (lead_id,old_user_id,new_user_id,changed_by,changed_at) VALUES (?,?,?,?,NOW())')
                    ->execute([$leadId, null, $assignUser, $user['id']]);
            }
        } elseif ($action === 'priority_update') {
            $priority = $_POST['bulk_priority'] ?? 'Medium';
            $stmt = $pdo->prepare("UPDATE leads SET priority=? WHERE id IN ($in)");
            $stmt->execute(array_merge([$priority], $ids));
        } elseif ($action === 'tag_update') {
            $tagName = trim($_POST['bulk_tag'] ?? '');
            if ($tagName !== '') {
                $pdo->prepare('INSERT IGNORE INTO tags(name) VALUES(?)')->execute([$tagName]);
                $tagId = (int)$pdo->query('SELECT id FROM tags WHERE name=' . $pdo->quote($tagName))->fetch()['id'];
                foreach ($ids as $leadId) {
                    $pdo->prepare('INSERT IGNORE INTO lead_tags(lead_id,tag_id) VALUES(?,?)')->execute([$leadId, $tagId]);
                }
            }
        } elseif ($action === 'bulk_export') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="selected_leads.csv"');
            $stmt = $pdo->prepare("SELECT id,full_name,company_name,phone,email,source,service_interest,stage,priority FROM leads WHERE id IN ($in)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Name','Company','Phone','Email','Source','Service','Stage','Priority']);
            foreach ($rows as $r) fputcsv($out, $r);
            fclose($out);
            exit;
        }

        log_activity($pdo, (int)$user['id'], 'bulk_lead_action', 'lead', null, $action . ' on ' . count($ids) . ' leads');
    }
}

$where = ["l.deleted_at IS NULL"];
$params = [];
if ($user['role'] === 'Staff') {
    $where[] = 'l.assigned_user_id=?';
    $params[] = $user['id'];
}
if (!empty($_GET['q'])) {
    $where[] = '(l.full_name LIKE ? OR l.company_name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)';
    for ($i=0; $i<4; $i++) $params[] = '%' . $_GET['q'] . '%';
}
foreach (['source','service_interest','stage','priority','city','lead_type'] as $f) {
    if (!empty($_GET[$f])) {
        $where[] = "l.$f=?";
        $params[] = $_GET[$f];
    }
}
if (!empty($_GET['assigned_user_id'])) {
    $where[] = 'l.assigned_user_id=?';
    $params[] = $_GET['assigned_user_id'];
}
if (!empty($_GET['date_from'])) {
    $where[] = 'DATE(l.created_at) >= ?';
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where[] = 'DATE(l.created_at) <= ?';
    $params[] = $_GET['date_to'];
}

$allowedSort = ['created_at','full_name','company_name','priority','stage','next_follow_up_date'];
$sort = $_GET['sort'] ?? 'created_at';
$sort = in_array($sort, $allowedSort, true) ? $sort : 'created_at';
$order = strtoupper($_GET['order'] ?? 'DESC');
$order = in_array($order, ['ASC','DESC'], true) ? $order : 'DESC';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)setting($pdo, 'default_pagination', '15');
$perPage = max(10, min(100, $perPage));
$offset = ($page - 1) * $perPage;

$sql = 'SELECT l.*,u.full_name assigned_name FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id WHERE ' . implode(' AND ', $where) . " ORDER BY l.$sort $order LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$countStmt = $pdo->prepare('SELECT COUNT(*) c FROM leads l WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$pages = (int)ceil($total / $perPage);

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
    <div class="col-md-2"><input class="form-control" name="q" placeholder="Search..." value="<?= e($_GET['q'] ?? '') ?>"></div>
    <div class="col-md-2"><select class="form-select" name="stage"><option value="">Stage</option><?php foreach(['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'] as $x): ?><option <?= ($_GET['stage']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-1"><select class="form-select" name="priority"><option value="">Priority</option><?php foreach(['Low','Medium','High'] as $x): ?><option <?= ($_GET['priority']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-1"><select class="form-select" name="lead_type"><option value="">Type</option><?php foreach(['Business','Student'] as $x): ?><option <?= ($_GET['lead_type']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="assigned_user_id"><option value="">Assigned</option><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (string)($_GET['assigned_user_id']??'')===(string)$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><input type="date" class="form-control" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>"></div>
    <div class="col-md-2"><input type="date" class="form-control" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>"></div>
    <div class="col-md-2"><select class="form-select" name="sort"><?php foreach($allowedSort as $s): ?><option value="<?= e($s) ?>" <?= $sort===$s?'selected':'' ?>>Sort: <?= e($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-1"><select class="form-select" name="order"><option <?= $order==='DESC'?'selected':'' ?>>DESC</option><option <?= $order==='ASC'?'selected':'' ?>>ASC</option></select></div>
    <div class="col-md-1"><button class="btn btn-dark w-100">Go</button></div>
</form>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
    <th><input type="checkbox" onclick="document.querySelectorAll('.lead-check').forEach(x=>x.checked=this.checked)"></th>
    <th>Name</th><th>Company</th><th>Source</th><th>Service</th><th>Stage</th><th>Priority</th><th>Assigned</th><th>Follow-up</th><th></th>
</tr>
</thead>
<tbody>
<?php foreach($leads as $l): ?>
<tr>
    <td><input class="lead-check" type="checkbox" name="lead_ids[]" value="<?= (int)$l['id'] ?>"></td>
    <td><?= e($l['full_name']) ?></td>
    <td><?= e($l['company_name']) ?></td>
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
<div class="row g-2 mt-2">
    <div class="col-md-2"><select name="bulk_action" class="form-select"><option value="">Bulk action</option><option value="assign_update">Bulk Assign</option><option value="stage_update">Bulk Stage</option><option value="priority_update">Bulk Priority</option><option value="tag_update">Bulk Tag</option><option value="bulk_export">Bulk Export CSV</option><option value="soft_delete">Bulk Soft Delete</option></select></div>
    <div class="col-md-2"><select name="bulk_assigned_user_id" class="form-select"><option value="">Assign to</option><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select name="bulk_stage" class="form-select"><option>Follow-up</option><option>Contacted</option><option>Interested</option><option>Meeting / Discussion</option><option>Closed Won</option><option>Closed Lost</option></select></div>
    <div class="col-md-2"><select name="bulk_priority" class="form-select"><option>Low</option><option selected>Medium</option><option>High</option></select></div>
    <div class="col-md-2"><input name="bulk_tag" class="form-control" placeholder="Tag name"></div>
    <div class="col-md-2"><button class="btn btn-secondary w-100">Apply</button></div>
</div>
</form>
<nav class="mt-3"><?php for($i=1;$i<=$pages;$i++): ?><a class="btn btn-sm <?= $i===$page?'btn-dark':'btn-outline-dark' ?>" href="?<?= e(http_build_query(array_merge($_GET,['page'=>$i]))) ?>"><?= $i ?></a><?php endfor; ?></nav>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
