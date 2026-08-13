<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    // Clear all session data
    $_SESSION = [];
    
    // Destroy the session
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}

header('Location: login.php');
exit;