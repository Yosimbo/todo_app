<?php
require_once 'config.php';

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
    try {
        return $stmt->execute([$user_id, $title, $description, $due_date, $priority]);
    } catch (PDOException $e) {
        error_log("Error creating task: " . $e->getMessage());
        return false;
    }
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
 * Get a single deleted task by ID (and verify ownership)
 */
function getTask1($task_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM delete_tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    return $stmt->fetch();
}

/**
 * Get all deleted tasks for a user
 */
function getAllDeletedTasks($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM delete_tasks WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
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
 * Delete task (moves to delete_tasks table)
 */
function deleteTask($task_id, $user_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get the task to archive
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $task = $stmt->fetch();
        
        if (!$task) {
            $pdo->rollBack();
            return false;
        }
        
        // Archive to delete_tasks table
        $stmt = $pdo->prepare("INSERT INTO delete_tasks (user_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $task['title'], $task['description'], $task['due_date'], $task['priority']]);
        
        // Delete from tasks table
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting task: " . $e->getMessage());
        return false;
    }
}

/**
 * Restore task (moves from delete_tasks back to tasks table)
 */
function restoreTask($task_id, $user_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get the deleted task
        $stmt = $pdo->prepare("SELECT * FROM delete_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $task = $stmt->fetch();
        
        if (!$task) {
            $pdo->rollBack();
            return false;
        }
        
        // Restore to tasks table
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $task['title'], $task['description'], $task['due_date'], $task['priority']]);
        
        // Remove from delete_tasks table
        $stmt = $pdo->prepare("DELETE FROM delete_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error restoring task: " . $e->getMessage());
        return false;
    }
}

/**
 * Permanently delete task from delete_tasks table
 */
function permanentDeleteTask($task_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM delete_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error permanently deleting task: " . $e->getMessage());
        return false;
    }
}
