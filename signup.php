<?php
require_once 'config.php';

$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Check uniqueness and create user
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors['general'] = 'Username or email already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $hash])) {
                    $_SESSION['flash'] = 'Account created successfully! Please log in.';
                    header('Location: login.php');
                    exit;
                } else {
                    $errors['general'] = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors['general'] = 'An error occurred during registration. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= e($errors['general']) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= e($old['username'] ?? '') ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="error"><?= e($errors['username']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?= e($errors['confirm_password']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>

</html>