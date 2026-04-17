<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_FILES['csv']['tmp_name'])){
  csrf_check();
  $fh=fopen($_FILES['csv']['tmp_name'],'r');
  $header=fgetcsv($fh);$count=0;
  while(($row=fgetcsv($fh))!==false){
    [$lead_type,$full_name,$company_name,$phone,$email,$source,$service,$stage,$priority]=array_pad($row,9,'');
    $pdo->prepare('INSERT INTO leads (lead_type,full_name,company_name,phone,email,source,service_interest,stage,priority,assigned_user_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')
      ->execute([$lead_type?:'Business',$full_name,$company_name,$phone,$email,$source?:'Other',$service?:'Other',$stage?:'New Lead',$priority?:'Medium',current_user()['id']]);
    $count++;
  }
  fclose($fh);$msg="$count leads imported.";
}
?>
<h3>CSV Import</h3>
<?php if($msg):?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<div class="card card-body">
<p class="small">CSV columns order: lead_type, full_name, company_name, phone, email, source, service_interest, stage, priority</p>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="file" name="csv" class="form-control mb-2" accept=".csv" required><button class="btn btn-primary">Import</button></form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
