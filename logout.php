<?php
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session cookie (Security Best Practice)
// This ensures the browser doesn't hold onto the old session ID
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to Login Page
// You can change 'login.php' to 'index.php' if you want them to go to the homepage instead.
header("Location: login.php");
exit();
?>