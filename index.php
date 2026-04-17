<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    redirect_to('dashboard/index.php');
} else {
    redirect_to('auth/login.php');
}
exit;
