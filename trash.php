<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$task_id = (int)($_GET['task_id'] ?? 0);

// Get deleted task or fetch all deleted tasks
$tasks = $task_id ? getTask1($task_id, $user_id) : getAllDeletedTasks($user_id);
$tasks = $tasks ?: [];
$tasks = is_array($tasks) ? $tasks : [$tasks];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Trash - Deleted Tasks</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Trash</h2>
            <a href="index.php" class="logout">Back to Tasks</a>
        </div>

        <!-- Task List -->
        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <p>No deleted tasks found.</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): 
                    $priority_class = strtolower($task['priority']);
                    $is_completed = $task['completed'] ?? false;
                    $csrf = csrf_token();
                ?>
                    <div class="task-item <?= $is_completed ? 'completed' : '' ?>">
                        <div class="task-info">
                            <h3><?= e($task['title']) ?></h3>
                            <p><?= e($task['description']) ?></p>
                            <span class="priority priority-<?= $priority_class ?>"><?= e($task['priority']) ?></span>
                            <span class="due-date">Due: <?= $task['due_date'] ? e($task['due_date']) : 'No date' ?></span>
                        </div>
                        <div class="task-actions">
                            <a href="restore.php?id=<?= $task['id'] ?>&csrf=<?= $csrf ?>" class="edit">Restore</a>
                            <a href="permanent_delete.php?id=<?= $task['id'] ?>&csrf=<?= $csrf ?>" class="delete"
                                onclick="return confirm('Permanently delete this task?')">Permanent Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>