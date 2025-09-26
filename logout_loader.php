<?php
require_once __DIR__ . '/includes/helpers/Session.php';
$session = new Session();
$session->logout();
header("Location: login_loader.php");
exit;