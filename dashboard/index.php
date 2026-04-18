<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/activity.php';

$user = current_user();
$whereOwn = $user['role'] === 'Staff' ? ' AND assigned_user_id = ' . (int)$user['id'] : '';
$stats = [];
$stageList = ['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'];
$totalStmt = $pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL $whereOwn");
$stats['Total Leads'] = (int)$totalStmt->fetch()['c'];

foreach ($stageList as $stage) {
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND stage = ? $whereOwn");
    $stmt->execute([$stage]);
    $stats[$stage] = (int)$stmt->fetch()['c'];
}
$dueToday = $pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND DATE(next_follow_up_date)=CURDATE() $whereOwn")->fetch()['c'];
$overdue = $pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND next_follow_up_date < CURDATE() $whereOwn")->fetch()['c'];
$weekCount = $pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1) $whereOwn")->fetch()['c'];
$monthCount = $pdo->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE()) $whereOwn")->fetch()['c'];

$recentActivities = $pdo->query("SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();
$tasksToday = $pdo->query("SELECT t.*, l.full_name lead_name FROM lead_tasks t LEFT JOIN leads l ON l.id=t.lead_id WHERE DATE(t.due_date)=CURDATE() ORDER BY t.due_date ASC LIMIT 10")->fetchAll();
$meetingsUpcoming = $pdo->query("SELECT m.*, l.full_name lead_name FROM meetings m LEFT JOIN leads l ON l.id=m.lead_id WHERE m.meeting_date>=CURDATE() ORDER BY m.meeting_date,m.meeting_time LIMIT 10")->fetchAll();
$pendingProposals = $pdo->query("SELECT COUNT(*) c FROM proposals WHERE status IN ('Draft','Sent','Under Review')")->fetch()['c'];
$sourceSummary = $pdo->query("SELECT source,COUNT(*) c FROM leads WHERE deleted_at IS NULL $whereOwn GROUP BY source ORDER BY c DESC LIMIT 6")->fetchAll();
$serviceSummary = $pdo->query("SELECT service_interest,COUNT(*) c FROM leads WHERE deleted_at IS NULL $whereOwn GROUP BY service_interest ORDER BY c DESC LIMIT 6")->fetchAll();
$staffSummary = $pdo->query("SELECT u.full_name,COUNT(l.id) c FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id WHERE l.deleted_at IS NULL GROUP BY l.assigned_user_id ORDER BY c DESC LIMIT 6")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Dashboard</h3>
    <div class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('leads/create.php')) ?>">+ New Lead</a>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('leads/pipeline.php')) ?>">Open Pipeline</a>
    </div>
</div>
<div class="row g-3 mb-4">
    <?php foreach (['Total Leads','New Lead','Contacted','Interested','Proposal Sent','Closed Won'] as $k): ?>
    <div class="col-md-2"><div class="card card-stat"><div class="card-body"><div class="text-muted small"><?= e($k) ?></div><h3><?= e((string)($stats[$k] ?? 0)) ?></h3></div></div></div>
    <?php endforeach; ?>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Follow-up Today</div><h4><?= e((string)$dueToday) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Overdue Follow-up</div><h4><?= e((string)$overdue) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Pending Proposals</div><h4><?= e((string)$pendingProposals) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Tasks Due Today</div><h4><?= count($tasksToday) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Leads Added This Week</div><h4><?= e((string)$weekCount) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Leads Added This Month</div><h4><?= e((string)$monthCount) ?></h4></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-header">Recent Activity</div><div class="card-body timeline"><?php foreach($recentActivities as $a): ?><div class="timeline-item"><strong><?= e($a['full_name'] ?? 'System') ?></strong><br><span class="small text-muted"><?= e($a['action']) ?> - <?= e($a['description'] ?? '') ?></span></div><?php endforeach; ?></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-header">Today's Tasks</div><ul class="list-group list-group-flush"><?php foreach($tasksToday as $t): ?><li class="list-group-item"><?= e($t['title']) ?> - <span class="badge bg-<?= badge_class('task', $t['status']) ?>"><?= e($t['status']) ?></span></li><?php endforeach; ?></ul></div></div>
    <div class="col-md-4"><div class="card"><div class="card-header">Upcoming Meetings</div><ul class="list-group list-group-flush"><?php foreach($meetingsUpcoming as $m): ?><li class="list-group-item"><?= e($m['meeting_title']) ?> (<?= e($m['meeting_date']) ?> <?= e($m['meeting_time']) ?>)</li><?php endforeach; ?></ul></div></div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4"><div class="card"><div class="card-header">Leads by Source</div><ul class="list-group list-group-flush"><?php foreach($sourceSummary as $s): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['source']) ?></span><strong><?= (int)$s['c'] ?></strong></li><?php endforeach; ?></ul></div></div>
    <div class="col-md-4"><div class="card"><div class="card-header">Leads by Service</div><ul class="list-group list-group-flush"><?php foreach($serviceSummary as $s): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['service_interest']) ?></span><strong><?= (int)$s['c'] ?></strong></li><?php endforeach; ?></ul></div></div>
    <div class="col-md-4"><div class="card"><div class="card-header">Leads by Staff</div><ul class="list-group list-group-flush"><?php foreach($staffSummary as $s): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['full_name'] ?: 'Unassigned') ?></span><strong><?= (int)$s['c'] ?></strong></li><?php endforeach; ?></ul></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
