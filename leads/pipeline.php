<?php
require_once __DIR__ . '/../includes/header.php';

$user = current_user();
$whereOwn = $user['role'] === 'Staff' ? ' AND l.assigned_user_id=' . (int)$user['id'] : '';
$stages = ['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'];

$stageData = [];
foreach ($stages as $stage) {
    $stmt = $pdo->prepare("SELECT l.id,l.full_name,l.company_name,l.priority,l.next_follow_up_date,u.full_name assigned_name
        FROM leads l
        LEFT JOIN users u ON u.id=l.assigned_user_id
        WHERE l.deleted_at IS NULL AND l.stage=? $whereOwn
        ORDER BY l.updated_at DESC LIMIT 40");
    $stmt->execute([$stage]);
    $stageData[$stage] = $stmt->fetchAll();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Lead Pipeline Board</h3>
    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('leads/index.php')) ?>">Back to Lead List</a>
</div>
<div class="row g-3">
<?php foreach ($stages as $stage): ?>
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="badge bg-<?= badge_class('stage', $stage) ?>"><?= e($stage) ?></span>
                <span class="small text-muted"><?= count($stageData[$stage]) ?></span>
            </div>
            <div class="card-body p-2" style="max-height:70vh;overflow:auto;">
                <?php if (!$stageData[$stage]): ?><div class="text-muted small p-2">No leads</div><?php endif; ?>
                <?php foreach ($stageData[$stage] as $lead): ?>
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="fw-semibold small"><?= e($lead['full_name']) ?></div>
                        <div class="text-muted small"><?= e($lead['company_name']) ?></div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="badge bg-<?= badge_class('priority', $lead['priority']) ?>"><?= e($lead['priority']) ?></span>
                            <a class="small" href="<?= e(url('leads/view.php')) ?>?id=<?= (int)$lead['id'] ?>">Open</a>
                        </div>
                        <div class="text-muted" style="font-size:11px;">Follow-up: <?= e($lead['next_follow_up_date']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
