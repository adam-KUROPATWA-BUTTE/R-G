<?php
require_once '../src/auth.php';

logout_user();
header('Location: /');
exit;