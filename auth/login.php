<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_logged_in()) {
    header('Location: /dashboard/index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'Active' AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        header('Location: /dashboard/index.php');
        exit;
    }
    $error = 'Invalid credentials or inactive user.';
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - LeadManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<div class="container"><div class="row justify-content-center"><div class="col-md-4">
    <div class="card shadow-sm"><div class="card-body">
        <h4 class="mb-3">LeadManager Login</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
            <div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control" required></div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
        <small class="d-block mt-3 text-muted">Default admin: admin@example.com / Admin@123</small>
    </div></div>
</div></div></div>
</body>
</html>
