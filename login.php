<?php
require_once 'config.php';

$errors = [];
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $errors['general'] = 'Please enter both username/email and password.';
    } else {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors['general'] = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Log In</h2>
        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= e($errors['general']) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="login" value="<?= e($login) ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>No account? <a href="signup.php">Sign Up</a></p>
    </div>
</body>

</html>