<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';
require_role('Admin');

if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $action=$_POST['action'] ?? '';

  if($action==='create'){
    $pdo->prepare("INSERT INTO users(full_name,email,phone,role,password_hash,status,created_at) VALUES (?,?,?,?,?,'Active',NOW())")
      ->execute([$_POST['full_name'],$_POST['email'],$_POST['phone'],$_POST['role'],password_hash($_POST['password'],PASSWORD_DEFAULT)]);
    log_activity($pdo,current_user()['id'],'user_added','user',(int)$pdo->lastInsertId(),'User created');
  } elseif($action==='edit'){
    $pdo->prepare('UPDATE users SET full_name=?,email=?,phone=?,role=? WHERE id=?')->execute([$_POST['full_name'],$_POST['email'],$_POST['phone'],$_POST['role'],$_POST['user_id']]);
    log_activity($pdo,current_user()['id'],'user_updated','user',(int)$_POST['user_id'],'User updated');
  } elseif($action==='status'){
    $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute([$_POST['status'],$_POST['user_id']]);
    log_activity($pdo,current_user()['id'],'user_status_changed','user',(int)$_POST['user_id'],'Status changed');
  } elseif($action==='reset_pw'){
    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($_POST['password'],PASSWORD_DEFAULT),$_POST['user_id']]);
    log_activity($pdo,current_user()['id'],'user_password_reset','user',(int)$_POST['user_id'],'Password reset');
  } elseif($action==='soft_delete'){
    $pdo->prepare('UPDATE users SET deleted_at=NOW(), status="Inactive" WHERE id=?')->execute([$_POST['user_id']]);
    log_activity($pdo,current_user()['id'],'user_soft_deleted','user',(int)$_POST['user_id'],'Soft deleted');
  }
  redirect_to('users/index.php');
}

$users=$pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>
<h3>User Management</h3>
<div class="card card-body mb-3"><h6>Add User</h6><form method="post" class="row g-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><div class="col-md-3"><input class="form-control" name="full_name" placeholder="Full name" required></div><div class="col-md-2"><input class="form-control" name="email" type="email" placeholder="Email" required></div><div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone"></div><div class="col-md-2"><select class="form-select" name="role"><option>Staff</option><option>Admin</option></select></div><div class="col-md-2"><input class="form-control" name="password" type="text" placeholder="Password" required></div><div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></form></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['role']) ?></td><td><span class="badge bg-<?= $u['status']==='Active'?'success':'secondary' ?>"><?= e($u['status']) ?></span></td><td><?= e($u['created_at']) ?></td><td><details><summary class="btn btn-sm btn-outline-dark">Manage</summary><div class="mt-2 p-2 border rounded bg-light"><form method="post" class="row g-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="edit"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><div class="col-md-6"><input class="form-control form-control-sm" name="full_name" value="<?= e($u['full_name']) ?>"></div><div class="col-md-6"><input class="form-control form-control-sm" name="email" value="<?= e($u['email']) ?>"></div><div class="col-md-4"><input class="form-control form-control-sm" name="phone" value="<?= e($u['phone']) ?>"></div><div class="col-md-4"><select class="form-select form-select-sm" name="role"><option <?= $u['role']==='Staff'?'selected':'' ?>>Staff</option><option <?= $u['role']==='Admin'?'selected':'' ?>>Admin</option></select></div><div class="col-md-4"><button class="btn btn-sm btn-primary w-100">Save</button></div></form><form method="post" class="d-flex gap-1 mt-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="status" value="<?= $u['status']==='Active'?'Inactive':'Active' ?>"><button class="btn btn-sm btn-outline-secondary"><?= $u['status']==='Active'?'Deactivate':'Reactivate' ?></button></form><form method="post" class="d-flex gap-1 mt-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="reset_pw"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><input class="form-control form-control-sm" name="password" placeholder="New password" required><button class="btn btn-sm btn-warning">Reset PW</button></form><form method="post" class="mt-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="soft_delete"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-sm btn-outline-danger">Soft Delete</button></form></div></details></td></tr><?php endforeach; ?></tbody></table></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
