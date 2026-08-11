<?php
require_once 'config.php';

/**
 * Get user by username or email (for login)
 */
// function getUserByLogin($login) {
//     global $pdo;
//     $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :login OR email = :login");
//     $stmt->execute(['login' => $login]);
//     return $stmt->fetch();
// }

/**
 * Get all tasks for a user with optional filters
 */
function getTasks($user_id, $priority = null, $status = null) {
    global $pdo;
    $sql = "SELECT * FROM tasks WHERE user_id = :user_id";
    $params = ['user_id' => $user_id];

    if ($priority && $priority !== 'all') {
        $sql .= " AND priority = :priority";
        $params['priority'] = $priority;
    }

    if ($status && $status !== 'all') {
        if ($status === 'completed') {
            $sql .= " AND completed = 1";
        } elseif ($status === 'incomplete') {
            $sql .= " AND completed = 0";
        }
    }

    $sql .= " ORDER BY due_date ASC, priority DESC, created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Create a new task
 */
function createTask($user_id, $title, $description, $due_date, $priority) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $description, $due_date, $priority]);
}

/**
 * Get a single task by ID (and verify ownership)
 */
function getTask($task_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    return $stmt->fetch();
}

/**
 * Update task
 */
function updateTask($task_id, $user_id, $title, $description, $due_date, $priority) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ? WHERE id = ? AND user_id = ?");
    return $stmt->execute([$title, $description, $due_date, $priority, $task_id, $user_id]);
}

/**
 * Toggle completion status
 */
function toggleTask($task_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tasks SET completed = NOT completed WHERE id = ? AND user_id = ?");
    return $stmt->execute([$task_id, $user_id]);
}

/**
 * Delete task
 */
function deleteTask($task_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    return $stmt->execute([$task_id, $user_id]);
}
