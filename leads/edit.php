<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
$id = (int)($_GET['id'] ?? 0);
$lead = $pdo->query('SELECT * FROM leads WHERE id='.$id.' AND deleted_at IS NULL')->fetch();
if (!$lead || !can_view_lead($lead)) { die('Lead not found or access denied'); }
$users = $pdo->query("SELECT id,full_name FROM users WHERE status='Active' AND deleted_at IS NULL ORDER BY full_name")->fetchAll();
$tagsCsv = implode(',', array_column($pdo->query('SELECT t.name FROM lead_tags lt JOIN tags t ON t.id=lt.tag_id WHERE lt.lead_id='.$id)->fetchAll(),'name'));
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $oldAssigned = (int)$lead['assigned_user_id'];
    $fields = ['lead_type','full_name','company_name','category','city','country','phone','whatsapp','email','website','facebook_link','instagram_link','linkedin_link','google_maps_link','source','service_interest','budget_range','priority','stage','assigned_user_id','preferred_contact_method','pain_point','owner_remarks','approval_flag','closed_reason','next_follow_up_date'];
    $set = implode(',', array_map(fn($f)=>"$f=?",$fields));
    $values=[]; foreach($fields as $f){$values[]=$_POST[$f]??null;} $values[]=$id;
    $pdo->prepare("UPDATE leads SET $set,updated_at=NOW() WHERE id=?")->execute($values);
    $pdo->prepare('DELETE FROM lead_tags WHERE lead_id=?')->execute([$id]);
    foreach(array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))) as $tag){
      $pdo->prepare('INSERT IGNORE INTO tags(name) VALUES(?)')->execute([$tag]);
      $tagId = $pdo->query("SELECT id FROM tags WHERE name=".$pdo->quote($tag))->fetch()['id'];
      $pdo->prepare('INSERT IGNORE INTO lead_tags(lead_id,tag_id) VALUES(?,?)')->execute([$id,$tagId]);
    }
    $pdo->prepare('INSERT INTO lead_stage_history (lead_id,stage,changed_by,changed_at) VALUES (?,?,?,NOW())')->execute([$id,$_POST['stage'],current_user()['id']]);
    if ((int)$_POST['assigned_user_id'] !== $oldAssigned) {
      $pdo->prepare('INSERT INTO lead_assignment_history (lead_id, old_user_id, new_user_id, changed_by, changed_at) VALUES (?,?,?,?,NOW())')->execute([$id, $oldAssigned ?: null, $_POST['assigned_user_id'], current_user()['id']]);
    }
    log_activity($pdo,(int)current_user()['id'],'lead_updated','lead',$id,'Lead updated');
    redirect_to('leads/view.php?id='.$id);
}
?>
<h3 class="mb-3">Edit Lead</h3>
<form method="post" class="card card-body">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<?php include __DIR__ . '/_form.php'; ?>
<div class="mt-3"><button class="btn btn-primary">Update Lead</button></div>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
