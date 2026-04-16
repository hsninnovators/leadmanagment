<?php
require_once __DIR__ . '/../includes/header.php';
$user = current_user();
$scope = $user['role'] === 'Staff' ? ' AND assigned_user_id='.(int)$user['id'] : '';
$cards = [
  'Leads Added Today' => "SELECT COUNT(*) c FROM leads WHERE DATE(created_at)=CURDATE() AND deleted_at IS NULL $scope",
  'Leads Contacted Today' => "SELECT COUNT(*) c FROM lead_interactions WHERE interaction_type IN ('WhatsApp sent','Call made','Email sent') AND DATE(interaction_at)=CURDATE()",
  'Follow-ups Due Today' => "SELECT COUNT(*) c FROM leads WHERE DATE(next_follow_up_date)=CURDATE() AND deleted_at IS NULL $scope",
  'Overdue Follow-ups' => "SELECT COUNT(*) c FROM leads WHERE next_follow_up_date<CURDATE() AND deleted_at IS NULL $scope",
  'Meetings Today' => "SELECT COUNT(*) c FROM meetings WHERE meeting_date=CURDATE()",
  'Tasks Due Today' => "SELECT COUNT(*) c FROM lead_tasks WHERE DATE(due_date)=CURDATE()",
  'Proposals Sent Today' => "SELECT COUNT(*) c FROM proposals WHERE DATE(proposal_date)=CURDATE() AND status='Sent'",
];
?>
<h3 class="mb-3">Daily Work Summary</h3>
<div class="row g-3">
<?php foreach ($cards as $title => $query): $value = $pdo->query($query)->fetch()['c']; ?>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted"><?= e($title) ?></div><h4><?= e((string)$value) ?></h4></div></div></div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
