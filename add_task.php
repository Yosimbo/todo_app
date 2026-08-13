<?php
require_once 'config.php';
require_once 'functions.php';

require_login();

$errors = [];
$old = $_POST;
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';

    if (empty($title)) {
        $errors['title'] = 'Title is required.';
    }

    if (empty($errors)) {
        if (createTask($user_id, $title, $description, $due_date ?: null, $priority)) {
            $_SESSION['flash'] = 'Task created successfully!';
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Failed to create task. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Task</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Add New Task</h2>
        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= e($errors['general']) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" value="<?= e($old['title'] ?? '') ?>" required>
                <?php if (isset($errors['title'])): ?>
                    <span class="error"><?= e($errors['title']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= e($old['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= e($old['due_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="High" <?= ($old['priority'] ?? 'Medium') === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= ($old['priority'] ?? 'Medium') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= ($old['priority'] ?? 'Medium') === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <button type="submit">Save Task</button>
            <a href="index.php" class="btn-clear">Cancel</a>
        </form>
    </div>
</body>

</html>