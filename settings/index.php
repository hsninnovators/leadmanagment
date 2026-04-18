<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
require_role('Admin');

function upload_branding_file(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }
    $allowed = ['png','jpg','jpeg','gif','ico','svg','webp'];
    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    $newName = 'assets/uploads/branding/' . $fieldName . '_' . time() . '.' . $ext;
    $target = __DIR__ . '/../' . $newName;
    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $target)) {
        return $newName;
    }
    return null;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    foreach($_POST as $k=>$v){
        if($k==='csrf_token') continue;
        $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute([$k,$v]);
    }

    $logoPath = upload_branding_file('company_logo_file');
    if ($logoPath) {
        $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute(['company_logo', $logoPath]);
    }

    $faviconPath = upload_branding_file('company_favicon_file');
    if ($faviconPath) {
        $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute(['company_favicon', $faviconPath]);
    }

    redirect_to('settings/index.php');
}
$rows=$pdo->query('SELECT `key`,`value` FROM settings')->fetchAll();
$set=[];foreach($rows as $r){$set[$r['key']]=$r['value'];}
?>
<h3>System Settings</h3>
<form method="post" enctype="multipart/form-data" class="card card-body">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="row g-3">
        <div class="col-md-4"><label>Company Name</label><input name="company_name" class="form-control" value="<?= e($set['company_name']??'Startup Team') ?>"></div>
        <div class="col-md-4"><label>System Title</label><input name="system_title" class="form-control" value="<?= e($set['system_title']??'Lead Management CRM') ?>"></div>
        <div class="col-md-4"><label>Timezone</label><input name="timezone" class="form-control" value="<?= e($set['timezone']??'UTC') ?>"></div>

        <div class="col-md-3"><label>Default Pagination</label><input name="default_pagination" class="form-control" value="<?= e($set['default_pagination']??'15') ?>"></div>
        <div class="col-md-3"><label>Allowed Upload Size MB</label><input name="upload_size_mb" class="form-control" value="<?= e($set['upload_size_mb']??'5') ?>"></div>
        <div class="col-md-3"><label>Currency Symbol</label><input name="currency_symbol" class="form-control" value="<?= e($set['currency_symbol']??'$') ?>"></div>
        <div class="col-md-3"><label>Report Brand Text</label><input name="report_branding" class="form-control" value="<?= e($set['report_branding']??'Professional Lead Report') ?>"></div>

        <div class="col-md-6">
            <label>Company Logo Upload</label>
            <input type="file" class="form-control" name="company_logo_file" accept=".png,.jpg,.jpeg,.gif,.svg,.webp">
            <?php if (!empty($set['company_logo'])): ?><small class="text-muted">Current: <?= e($set['company_logo']) ?></small><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label>Favicon Upload</label>
            <input type="file" class="form-control" name="company_favicon_file" accept=".ico,.png,.jpg,.jpeg,.svg,.webp">
            <?php if (!empty($set['company_favicon'])): ?><small class="text-muted">Current: <?= e($set['company_favicon']) ?></small><?php endif; ?>
        </div>
    </div>
    <button class="btn btn-primary mt-3">Save Settings</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
