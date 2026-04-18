<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
$id=(int)($_GET['id']??0);
$lead=$pdo->query("SELECT l.*,u.full_name assigned_name FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id WHERE l.id=$id AND l.deleted_at IS NULL")->fetch();
if(!$lead || !can_view_lead($lead)) die('Not found or access denied');

if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $action=$_POST['action']??'';
  if($action==='note'){
    $pdo->prepare('INSERT INTO lead_notes(lead_id,note,created_by,created_at) VALUES(?,?,?,NOW())')->execute([$id,$_POST['note'],current_user()['id']]);
  } elseif($action==='interaction'){
    $pdo->prepare('INSERT INTO lead_interactions(lead_id,interaction_type,note,next_action,next_follow_up_date,interaction_at,created_by) VALUES(?,?,?,?,?,NOW(),?)')->execute([$id,$_POST['interaction_type'],$_POST['note'],$_POST['next_action'],$_POST['next_follow_up_date']?:null,current_user()['id']]);
  } elseif($action==='task'){
    $pdo->prepare('INSERT INTO lead_tasks(lead_id,title,assigned_user_id,due_date,priority,status,note,created_at) VALUES(?,?,?,?,?,?,?,NOW())')->execute([$id,$_POST['title'],$_POST['assigned_user_id'],$_POST['due_date'],$_POST['priority'],'Pending',$_POST['note']]);
  } elseif($action==='meeting'){
    $pdo->prepare('INSERT INTO meetings(lead_id,meeting_title,meeting_type,meeting_date,meeting_time,meeting_notes,outcome,next_action,assigned_user_id,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())')->execute([$id,$_POST['meeting_title'],$_POST['meeting_type'],$_POST['meeting_date'],$_POST['meeting_time'],$_POST['meeting_notes'],$_POST['outcome'],$_POST['next_action'],$_POST['assigned_user_id']]);
  } elseif($action==='proposal'){
    $pdo->prepare('INSERT INTO proposals(lead_id,title,proposal_date,amount,status,description,notes,created_by,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())')->execute([$id,$_POST['title'],$_POST['proposal_date'],$_POST['amount'],$_POST['status'],$_POST['description'],$_POST['notes'],current_user()['id']]);
  } elseif($action==='attachment' && !empty($_FILES['file']['name'])){
    $allowed=['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','zip'];
    $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,$allowed,true)){
      $newName='lead_'.$id.'_'.time().'.'.$ext;
      $target=__DIR__.'/../assets/uploads/'.$newName;
      move_uploaded_file($_FILES['file']['tmp_name'],$target);
      $pdo->prepare('INSERT INTO lead_attachments(lead_id,file_name,file_path,category,note,uploaded_by,uploaded_at) VALUES(?,?,?,?,?,?,NOW())')->execute([$id,$_FILES['file']['name'],$newName,$_POST['category'],$_POST['note'],current_user()['id']]);
    }
  } elseif($action==='quick_update'){
    $oldAssigned = (int)$lead['assigned_user_id'];
    $pdo->prepare('UPDATE leads SET stage=?, priority=?, next_follow_up_date=?, assigned_user_id=?, updated_at=NOW() WHERE id=?')
      ->execute([$_POST['stage'], $_POST['priority'], $_POST['next_follow_up_date'] ?: null, $_POST['assigned_user_id'], $id]);
    if ((int)$_POST['assigned_user_id'] !== $oldAssigned) {
      $pdo->prepare('INSERT INTO lead_assignment_history (lead_id, old_user_id, new_user_id, changed_by, changed_at) VALUES (?,?,?,?,NOW())')
        ->execute([$id, $oldAssigned ?: null, $_POST['assigned_user_id'], current_user()['id']]);
    }
    $pdo->prepare('INSERT INTO lead_stage_history (lead_id,stage,changed_by,changed_at) VALUES (?,?,?,NOW())')
      ->execute([$id, $_POST['stage'], current_user()['id']]);
  }
  log_activity($pdo,(int)current_user()['id'],'lead_detail_action','lead',$id,$action);
  redirect_to('leads/view.php?id='.$id);
}

$notes=$pdo->query('SELECT n.*,u.full_name FROM lead_notes n LEFT JOIN users u ON u.id=n.created_by WHERE n.lead_id='.$id.' ORDER BY n.created_at DESC')->fetchAll();
$interactions=$pdo->query('SELECT i.*,u.full_name FROM lead_interactions i LEFT JOIN users u ON u.id=i.created_by WHERE i.lead_id='.$id.' ORDER BY i.interaction_at DESC')->fetchAll();
$history=$pdo->query('SELECT h.*,u.full_name FROM lead_stage_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.lead_id='.$id.' ORDER BY h.changed_at DESC')->fetchAll();
$tasks=$pdo->query('SELECT * FROM lead_tasks WHERE lead_id='.$id.' ORDER BY due_date DESC')->fetchAll();
$meetings=$pdo->query('SELECT * FROM meetings WHERE lead_id='.$id.' ORDER BY meeting_date DESC')->fetchAll();
$proposals=$pdo->query('SELECT * FROM proposals WHERE lead_id='.$id.' ORDER BY proposal_date DESC')->fetchAll();
$attachments=$pdo->query('SELECT a.*,u.full_name FROM lead_attachments a LEFT JOIN users u ON u.id=a.uploaded_by WHERE a.lead_id='.$id.' ORDER BY a.uploaded_at DESC')->fetchAll();
$assignmentHistory=$pdo->query('SELECT ah.*,o.full_name old_name,n.full_name new_name,u.full_name changed_by_name FROM lead_assignment_history ah LEFT JOIN users o ON o.id=ah.old_user_id LEFT JOIN users n ON n.id=ah.new_user_id LEFT JOIN users u ON u.id=ah.changed_by WHERE ah.lead_id='.$id.' ORDER BY ah.changed_at DESC')->fetchAll();
$users=$pdo->query("SELECT id,full_name FROM users WHERE status='Active' AND deleted_at IS NULL ORDER BY full_name")->fetchAll();
?>
<div class="d-flex justify-content-between mb-3"><h3>Lead #<?= (int)$lead['id'] ?> - <?= e($lead['full_name']) ?></h3><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('leads/edit.php')) ?>?id=<?= (int)$id ?>">Edit</a></div>
<div class="row g-3">
<div class="col-md-4"><div class="card"><div class="card-body">
    <h5><?= e($lead['company_name']) ?></h5>
    <p class="mb-1"><span class="badge bg-<?= badge_class('stage',$lead['stage']) ?>"><?= e($lead['stage']) ?></span> <span class="badge bg-<?= badge_class('priority',$lead['priority']) ?>"><?= e($lead['priority']) ?></span></p>
    <p class="small mb-1">Source: <span class="badge bg-<?= badge_class('source',$lead['source']) ?>"><?= e($lead['source']) ?></span></p>
    <p class="small mb-1">Service: <?= e($lead['service_interest']) ?></p>
    <p class="small mb-1">Assigned: <?= e($lead['assigned_name']) ?></p>
    <p class="small mb-0">Follow-up: <?= e($lead['next_follow_up_date']) ?></p>
</div></div></div>
<div class="col-md-8"><div class="card"><div class="card-body"><h6>Contact & Notes</h6><p><?= e($lead['phone']) ?> | <?= e($lead['email']) ?> | <?= e($lead['whatsapp']) ?></p><p><?= e($lead['pain_point']) ?></p></div></div></div>
</div>

<div class="row g-3 mt-1">
<div class="col-md-6"><div class="card"><div class="card-header">Interactions</div><div class="card-body timeline"><?php foreach($interactions as $x): ?><div class="timeline-item"><strong><?= e($x['interaction_type']) ?></strong> (<?= e($x['interaction_at']) ?>)<br><span class="small"><?= e($x['note']) ?></span></div><?php endforeach; ?></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Notes</div><div class="card-body timeline"><?php foreach($notes as $x): ?><div class="timeline-item"><span class="small text-muted"><?= e($x['created_at']) ?> by <?= e($x['full_name']) ?></span><br><?= e($x['note']) ?></div><?php endforeach; ?></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Stage History</div><ul class="list-group list-group-flush"><?php foreach($history as $x): ?><li class="list-group-item"><?= e($x['stage']) ?> - <?= e($x['changed_at']) ?></li><?php endforeach; ?></ul></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Attachments</div><ul class="list-group list-group-flush"><?php foreach($attachments as $x): ?><li class="list-group-item"><a href="<?= e(url('assets/uploads/' . $x['file_path'])) ?>" target="_blank"><?= e($x['file_name']) ?></a> (<?= e($x['category']) ?>)</li><?php endforeach; ?></ul></div></div>
</div>

<div class="row g-3 mt-1">
<div class="col-md-12"><div class="card card-body"><h6>Quick Update (Stage / Priority / Follow-up / Assign)</h6><form method="post" class="row g-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="quick_update"><div class="col-md-3"><select name="stage" class="form-select"><?php foreach(['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'] as $st): ?><option <?= $lead['stage']===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select name="priority" class="form-select"><?php foreach(['Low','Medium','High'] as $pr): ?><option <?= $lead['priority']===$pr?'selected':'' ?>><?= e($pr) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><input type="date" name="next_follow_up_date" value="<?= e($lead['next_follow_up_date']) ?>" class="form-control"></div><div class="col-md-3"><select name="assigned_user_id" class="form-select"><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (int)$lead['assigned_user_id']===(int)$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-1"><button class="btn btn-primary w-100">Save</button></div></form></div></div>
<div class="col-md-4"><div class="card card-body"><h6>Quick Add Note</h6><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="note"><textarea class="form-control mb-2" name="note" required></textarea><button class="btn btn-sm btn-primary">Add</button></form></div></div>
<div class="col-md-4"><div class="card card-body"><h6>Quick Interaction</h6><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="interaction"><select name="interaction_type" class="form-select mb-2"><?php foreach(['WhatsApp sent','Call made','Email sent','Replied','Follow-up done','Meeting scheduled','Meeting completed','Proposal sent','Proposal updated','Closed','Other'] as $t):?><option><?= e($t) ?></option><?php endforeach;?></select><textarea class="form-control mb-2" name="note" placeholder="Short note"></textarea><input class="form-control mb-2" name="next_action" placeholder="Next action"><input type="date" class="form-control mb-2" name="next_follow_up_date"><button class="btn btn-sm btn-primary">Add</button></form></div></div>
<div class="col-md-4"><div class="card card-body"><h6>Upload Attachment</h6><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="attachment"><input type="file" class="form-control mb-2" name="file" required><select name="category" class="form-select mb-2"><?php foreach(['Proposal','Company Profile','Portfolio','Screenshot','Document','Other'] as $c):?><option><?= e($c) ?></option><?php endforeach;?></select><input class="form-control mb-2" name="note" placeholder="Optional note"><button class="btn btn-sm btn-primary">Upload</button></form></div></div>
</div>
<div class="row g-3 mt-1">
<div class="col-md-12"><div class="card"><div class="card-header">Assignment History</div><ul class="list-group list-group-flush"><?php foreach($assignmentHistory as $ah): ?><li class="list-group-item small"><?= e($ah['changed_at']) ?>: <?= e($ah['old_name'] ?: 'Unassigned') ?> → <?= e($ah['new_name'] ?: 'Unassigned') ?> (by <?= e($ah['changed_by_name']) ?>)</li><?php endforeach; ?><?php if(!$assignmentHistory): ?><li class="list-group-item small text-muted">No assignment changes yet.</li><?php endif; ?></ul></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
