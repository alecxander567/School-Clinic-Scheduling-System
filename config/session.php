<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions for session management
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function logout()
{
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
