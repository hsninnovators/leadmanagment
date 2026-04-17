<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
require_role('Admin');
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $action=$_POST['action'];
  if($action==='create'){
    $pdo->prepare("INSERT INTO users(full_name,email,phone,role,password_hash,status,created_at) VALUES (?,?,?,?,?,'Active',NOW())")
      ->execute([$_POST['full_name'],$_POST['email'],$_POST['phone'],$_POST['role'],password_hash($_POST['password'],PASSWORD_DEFAULT)]);
    log_activity($pdo,current_user()['id'],'user_added','user',(int)$pdo->lastInsertId(),'User created');
  } elseif($action==='status'){
    $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute([$_POST['status'],$_POST['user_id']]);
  } elseif($action==='reset_pw'){
    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($_POST['password'],PASSWORD_DEFAULT),$_POST['user_id']]);
  } elseif($action==='soft_delete'){
    $pdo->prepare('UPDATE users SET deleted_at=NOW(), status="Inactive" WHERE id=?')->execute([$_POST['user_id']]);
  }
  redirect_to('users/index.php');
}
$users=$pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>
<h3>User Management</h3>
<div class="card card-body mb-3"><h6>Add User</h6><form method="post" class="row g-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><div class="col-md-3"><input class="form-control" name="full_name" placeholder="Full name" required></div><div class="col-md-2"><input class="form-control" name="email" type="email" placeholder="Email" required></div><div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone"></div><div class="col-md-2"><select class="form-select" name="role"><option>Staff</option><option>Admin</option></select></div><div class="col-md-2"><input class="form-control" name="password" type="text" placeholder="Password" required></div><div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></form></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['status']) ?></td><td><form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="status" value="<?= $u['status']==='Active'?'Inactive':'Active' ?>"><button class="btn btn-sm btn-outline-secondary"><?= $u['status']==='Active'?'Deactivate':'Reactivate' ?></button></form> <form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="soft_delete"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-sm btn-outline-danger">Soft Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
