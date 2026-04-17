<?php
require_once __DIR__ . '/../includes/auth.php';
$_SESSION = [];
session_destroy();
redirect_to('auth/login.php');
exit;
