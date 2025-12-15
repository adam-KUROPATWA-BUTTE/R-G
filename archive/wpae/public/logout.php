<?php
// Handle both direct access and routing from root
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
require_once $auth_path;

logout_user();
header('Location: /');
exit;