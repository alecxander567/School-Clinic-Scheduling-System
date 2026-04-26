<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/session.php';

$auth->logout();
header('Location: login.php');
exit();
