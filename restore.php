<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$task_id = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$task_id) {
    $_SESSION['flash'] = 'Invalid task ID.';
    header('Location: trash.php');
    exit;
}

if (!verify_csrf($csrf)) {
    $_SESSION['flash'] = 'Invalid request.';
    header('Location: trash.php');
    exit;
}

$user_id = $_SESSION['user_id'];
if (restoreTask($task_id, $user_id)) {
    $_SESSION['flash'] = 'Task restored successfully.';
} else {
    $_SESSION['flash'] = 'Failed to restore task.';
}

header('Location: trash.php');
exit;