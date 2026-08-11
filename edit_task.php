<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$task_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$task = getTask($task_id, $user_id);

if (!$task) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';

    if (empty($title)) {
        $errors['title'] = 'Title is required.';
    }

    if (empty($errors)) {
        if (updateTask($task_id, $user_id, $title, $description, $due_date ?: null, $priority)) {
            $_SESSION['flash'] = 'Task updated!';
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Failed to update task.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Task</h2>
        <?php if (!empty($errors['general'])): ?><div class="error"><?= e($errors['general']) ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" value="<?= e($old['title'] ?? $task['title']) ?>" required>
                <?php if (isset($errors['title'])): ?><span class="error"><?= e($errors['title']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= e($old['description'] ?? $task['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= e($old['due_date'] ?? $task['due_date']) ?>">
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="High" <?= ($old['priority'] ?? $task['priority']) === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= ($old['priority'] ?? $task['priority']) === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= ($old['priority'] ?? $task['priority']) === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <button type="submit">Update Task</button>
            <a href="dashboard.php">Cancel</a>
        </form>
    </div>
</body>
</html>
