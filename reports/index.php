<?php require_once __DIR__ . '/../includes/header.php';
$brandText = setting($pdo, 'report_branding', 'Professional Lead Report');
$companyName = setting($pdo, 'company_name', 'Startup Team');
$logo = setting($pdo, 'company_logo', '');
$from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');
$filterSql=' WHERE l.deleted_at IS NULL AND DATE(l.created_at) BETWEEN ? AND ? ';$params=[$from,$to];
foreach(['source','service_interest','stage','lead_type','city','assigned_user_id','priority'] as $f){if(!empty($_GET[$f])){$filterSql.=" AND l.$f=?";$params[]=$_GET[$f];}}

function single_metric(PDO $pdo, string $sql, array $params): int { $s=$pdo->prepare($sql);$s->execute($params);return (int)$s->fetch()['c']; }
$total = single_metric($pdo,'SELECT COUNT(*) c FROM leads l'.$filterSql,$params);
$contacted = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.stage IN ('Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won')",$params);
$interested = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.stage IN ('Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won')",$params);
$proposalSent = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.stage='Proposal Sent'",$params);
$won = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.stage='Closed Won'",$params);
$lost = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.stage IN ('Closed Lost','Not Interested')",$params);
$followPending = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.next_follow_up_date IS NOT NULL",$params);
$followOverdue = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.next_follow_up_date < CURDATE()",$params);
$businessCount = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.lead_type='Business'",$params);
$studentCount = single_metric($pdo,"SELECT COUNT(*) c FROM leads l$filterSql AND l.lead_type='Student'",$params);
$reportUsers = $pdo->query("SELECT id,full_name FROM users WHERE deleted_at IS NULL ORDER BY full_name")->fetchAll();
$sourcesList = $pdo->query("SELECT DISTINCT source FROM leads WHERE source IS NOT NULL ORDER BY source")->fetchAll();
$servicesList = $pdo->query("SELECT DISTINCT service_interest FROM leads WHERE service_interest IS NOT NULL ORDER BY service_interest")->fetchAll();
$citiesList = $pdo->query("SELECT DISTINCT city FROM leads WHERE city IS NOT NULL AND city<>'' ORDER BY city")->fetchAll();

$sourceData=$pdo->prepare('SELECT source,COUNT(*) c FROM leads l'.$filterSql.' GROUP BY source ORDER BY c DESC');$sourceData->execute($params);$sources=$sourceData->fetchAll();
$serviceData=$pdo->prepare('SELECT service_interest,COUNT(*) c FROM leads l'.$filterSql.' GROUP BY service_interest ORDER BY c DESC');$serviceData->execute($params);$services=$serviceData->fetchAll();
$staffData=$pdo->prepare('SELECT COALESCE(u.full_name,"Unassigned") staff_name, COUNT(*) c FROM leads l LEFT JOIN users u ON u.id=l.assigned_user_id'.$filterSql.' GROUP BY l.assigned_user_id ORDER BY c DESC');$staffData->execute($params);$staffRows=$staffData->fetchAll();
$closedReasonData=$pdo->prepare('SELECT COALESCE(closed_reason,"N/A") reason,COUNT(*) c FROM leads l'.$filterSql.' AND l.stage IN ("Closed Lost","Not Interested") GROUP BY closed_reason ORDER BY c DESC');$closedReasonData->execute($params);$closedReasons=$closedReasonData->fetchAll();
$conversionRate = $total > 0 ? round(($won / $total) * 100, 2) : 0;

if(($_GET['export']??'')==='csv'){
 header('Content-Type:text/csv');header('Content-Disposition: attachment; filename="report.csv"');$o=fopen('php://output','w');
 foreach([
   ['Total Leads',$total],['Contacted',$contacted],['Interested',$interested],['Proposal Sent',$proposalSent],['Closed Won',$won],['Lost',$lost],['Pending Follow-up',$followPending],['Overdue Follow-up',$followOverdue],['Business Leads',$businessCount],['Student Leads',$studentCount],['Conversion Rate %',$conversionRate]
 ] as $r) fputcsv($o,$r);
 foreach($sources as $s)fputcsv($o,['Source: '.$s['source'],$s['c']]);
 foreach($services as $s)fputcsv($o,['Service: '.$s['service_interest'],$s['c']]);
 foreach($staffRows as $s)fputcsv($o,['Staff: '.$s['staff_name'],$s['c']]);
 fclose($o);exit;
}
?>
<div class="card card-body mb-3"><div class="d-flex align-items-center gap-3"><?php if($logo): ?><img src="<?= e(url($logo)) ?>" style="height:40px"><?php endif; ?><div><h4 class="mb-0"><?= e($companyName) ?></h4><small class="text-muted"><?= e($brandText) ?></small></div></div></div>
<h3>Reports</h3>
<form class="row g-2 mb-3"><div class="col-md-2"><input type="date" class="form-control" name="from" value="<?= e($from) ?>"></div><div class="col-md-2"><input type="date" class="form-control" name="to" value="<?= e($to) ?>"></div><div class="col-md-2"><select class="form-select" name="source"><option value="">All Sources</option><?php foreach($sourcesList as $x): ?><option <?= ($_GET['source']??'')===$x['source']?'selected':'' ?>><?= e($x['source']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="service_interest"><option value="">All Services</option><?php foreach($servicesList as $x): ?><option <?= ($_GET['service_interest']??'')===$x['service_interest']?'selected':'' ?>><?= e($x['service_interest']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="stage"><option value="">All Stages</option><?php foreach(['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'] as $x): ?><option <?= ($_GET['stage']??'')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="lead_type"><option value="">All Types</option><option <?= ($_GET['lead_type']??'')==='Business'?'selected':'' ?>>Business</option><option <?= ($_GET['lead_type']??'')==='Student'?'selected':'' ?>>Student</option></select></div><div class="col-md-2"><select class="form-select" name="city"><option value="">All Cities</option><?php foreach($citiesList as $x): ?><option <?= ($_GET['city']??'')===$x['city']?'selected':'' ?>><?= e($x['city']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="assigned_user_id"><option value="">All Staff</option><?php foreach($reportUsers as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (string)($_GET['assigned_user_id']??'')===(string)$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="priority"><option value="">All Priorities</option><option <?= ($_GET['priority']??'')==='Low'?'selected':'' ?>>Low</option><option <?= ($_GET['priority']??'')==='Medium'?'selected':'' ?>>Medium</option><option <?= ($_GET['priority']??'')==='High'?'selected':'' ?>>High</option></select></div><div class="col-md-2"><button class="btn btn-dark w-100">Apply Filter</button></div><div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="?<?= e(http_build_query(array_merge($_GET,['export'=>'csv']))) ?>">Export CSV</a></div><div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="#" onclick="window.print()">Print</a></div></form>

<div class="row g-3 mb-3">
  <?php foreach([
    'Total Leads'=>$total,'Contacted'=>$contacted,'Interested'=>$interested,'Proposal Sent'=>$proposalSent,
    'Closed Won'=>$won,'Lost'=>$lost,'Pending Follow-up'=>$followPending,'Overdue Follow-up'=>$followOverdue,
    'Business Leads'=>$businessCount,'Student Leads'=>$studentCount,'Conversion %'=>$conversionRate
  ] as $k=>$v): ?>
    <div class="col-md-3"><div class="card card-body"><div class="small text-muted"><?= e($k) ?></div><h5><?= e((string)$v) ?></h5></div></div>
  <?php endforeach; ?>
</div>

<div class="row g-3"><div class="col-md-4"><div class="card"><div class="card-header">Source-wise</div><ul class="list-group list-group-flush"><?php foreach($sources as $s):?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['source']) ?></span><strong><?= e((string)$s['c']) ?></strong></li><?php endforeach;?></ul></div></div><div class="col-md-4"><div class="card"><div class="card-header">Service-wise</div><ul class="list-group list-group-flush"><?php foreach($services as $s):?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['service_interest']) ?></span><strong><?= e((string)$s['c']) ?></strong></li><?php endforeach;?></ul></div></div><div class="col-md-4"><div class="card"><div class="card-header">Staff-wise</div><ul class="list-group list-group-flush"><?php foreach($staffRows as $s):?><li class="list-group-item d-flex justify-content-between"><span><?= e($s['staff_name']) ?></span><strong><?= e((string)$s['c']) ?></strong></li><?php endforeach;?></ul></div></div></div>
<div class="row g-3 mt-1"><div class="col-md-12"><div class="card"><div class="card-header">Closed Reason Summary</div><ul class="list-group list-group-flush"><?php foreach($closedReasons as $r):?><li class="list-group-item d-flex justify-content-between"><span><?= e($r['reason']) ?></span><strong><?= (int)$r['c'] ?></strong></li><?php endforeach; ?><?php if(!$closedReasons): ?><li class="list-group-item text-muted">No closed reasons in selected range.</li><?php endif; ?></ul></div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
