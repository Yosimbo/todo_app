<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$priority = $_GET['priority'] ?? 'all';
$status   = $_GET['status'] ?? 'all';

// Get tasks
$tasks = getTasks($user_id, $priority, $status);

// Handle toggle via GET (simple, with CSRF check later in toggle.php)
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Tasks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome, <?= e($_SESSION['username']) ?></h2>
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <div class="actions">
            <a href="add_task.php" class="btn">+ Add Task</a>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="get" action="">
                <label>Priority:</label>
                <select name="priority">
                    <option value="all" <?= $priority === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="High" <?= $priority === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= $priority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= $priority === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>

                <label>Status:</label>
                <select name="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="incomplete" <?= $status === 'incomplete' ? 'selected' : '' ?>>Incomplete</option>
                </select>

                <button type="submit">Filter</button>
                <a href="dashboard.php" class="btn-clear">Clear Filters</a>
            </form>
        </div>

        <!-- Task List -->
        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <p>No tasks found.</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                        <div class="task-info">
                            <h3><?= e($task['title']) ?></h3>
                            <p><?= e($task['description']) ?></p>
                            <span class="priority priority-<?= strtolower($task['priority']) ?>"><?= e($task['priority']) ?></span>
                            <span class="due-date">Due: <?= $task['due_date'] ? e($task['due_date']) : 'No date' ?></span>
                            <span class="status"><?= $task['completed'] ? '✓ Completed' : '◻ Incomplete' ?></span>
                        </div>
                        <div class="task-actions">
                            <a href="toggle_complete.php?id=<?= $task['id'] ?>&csrf=<?= csrf_token() ?>" class="toggle">
                                <?= $task['completed'] ? 'Undo' : 'Complete' ?>
                            </a>
                            <a href="edit_task.php?id=<?= $task['id'] ?>" class="edit">Edit</a>
                            <a href="delete_task.php?id=<?= $task['id'] ?>&csrf=<?= csrf_token() ?>" class="delete" onclick="return confirm('Delete this task?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
