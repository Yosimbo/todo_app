<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$task_id = $_GET['id'] ?? 0;
$csrf = $_GET['csrf'] ?? '';

if (!verify_csrf($csrf)) {
    die('Invalid request.');
}

$user_id = $_SESSION['user_id'];
if (toggleTask($task_id, $user_id)) {
    $_SESSION['flash'] = 'Status toggled.';
} else {
    $_SESSION['flash'] = 'Failed to toggle status.';
}

header('Location: dashboard.php');
exit;
