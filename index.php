<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    header('Location: /dashboard/index.php');
} else {
    header('Location: /auth/login.php');
}
exit;
