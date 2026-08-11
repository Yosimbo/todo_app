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
if (deleteTask($task_id, $user_id)) {
    $_SESSION['flash'] = 'Task deleted.';
} else {
    $_SESSION['flash'] = 'Failed to delete task.';
}

header('Location: dashboard.php');
exit;
