<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user=current_user();
$where=['deleted_at IS NULL'];$params=[];
if($user['role']==='Staff'){ $where[]='assigned_user_id=?'; $params[]=$user['id']; }
if(!empty($_GET['q'])){ $where[]='(full_name LIKE ? OR company_name LIKE ? OR phone LIKE ? OR email LIKE ?)'; for($i=0;$i<4;$i++) $params[]='%'.$_GET['q'].'%'; }
$stmt=$pdo->prepare('SELECT id,lead_type,full_name,company_name,phone,email,source,service_interest,stage,priority,next_follow_up_date FROM leads WHERE '.implode(' AND ',$where).' ORDER BY id DESC');
$stmt->execute($params);$rows=$stmt->fetchAll();
header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="leads_export.csv"');
$out=fopen('php://output','w');
fputcsv($out,['ID','Type','Name','Company','Phone','Email','Source','Service','Stage','Priority','Next Follow-up']);
foreach($rows as $r) fputcsv($out,$r);
fclose($out); exit;
