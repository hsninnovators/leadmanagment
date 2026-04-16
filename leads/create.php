<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
$users = $pdo->query("SELECT id,full_name FROM users WHERE status='Active' AND deleted_at IS NULL ORDER BY full_name")->fetchAll();
$lead = [];$tagsCsv='';$warning='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lead = $_POST;
    $stmt = $pdo->prepare('SELECT id,full_name FROM leads WHERE deleted_at IS NULL AND (phone=? OR email=?) LIMIT 1');
    $stmt->execute([$_POST['phone'] ?: '-', $_POST['email'] ?: '-']);
    $dup = $stmt->fetch();
    if ($dup) {
        $warning = 'Potential duplicate found: ' . $dup['full_name'];
    } else {
        $fields = ['lead_type','full_name','company_name','category','city','country','phone','whatsapp','email','website','facebook_link','instagram_link','linkedin_link','google_maps_link','source','service_interest','budget_range','priority','stage','assigned_user_id','preferred_contact_method','pain_point','owner_remarks','approval_flag','closed_reason','next_follow_up_date'];
        $values = []; foreach($fields as $f){$values[]=$_POST[$f]??null;}
        $sql = 'INSERT INTO leads ('.implode(',',$fields).',created_at,updated_at) VALUES ('.implode(',',array_fill(0,count($fields),'?')).',NOW(),NOW())';
        $pdo->prepare($sql)->execute($values);
        $leadId = (int)$pdo->lastInsertId();
        foreach(array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))) as $tag){
            $pdo->prepare('INSERT IGNORE INTO tags(name) VALUES(?)')->execute([$tag]);
            $tagId = $pdo->query("SELECT id FROM tags WHERE name=".$pdo->quote($tag))->fetch()['id'];
            $pdo->prepare('INSERT IGNORE INTO lead_tags(lead_id,tag_id) VALUES(?,?)')->execute([$leadId,$tagId]);
        }
        $pdo->prepare('INSERT INTO lead_stage_history (lead_id,stage,changed_by,changed_at) VALUES (?,?,?,NOW())')->execute([$leadId,$_POST['stage'],current_user()['id']]);
        log_activity($pdo,(int)current_user()['id'],'lead_created','lead',$leadId,'Lead created');
        header('Location: /leads/view.php?id='.$leadId); exit;
    }
}
?>
<h3 class="mb-3">Add Lead</h3>
<?php if($warning): ?><div class="alert alert-warning"><?= e($warning) ?></div><?php endif; ?>
<form method="post" class="card card-body">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<?php include __DIR__ . '/_form.php'; ?>
<div class="mt-3"><button class="btn btn-primary">Save Lead</button></div>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
