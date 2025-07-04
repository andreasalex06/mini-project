<?php
session_start();

// Destroy all session data
$_SESSION = array();

// Delete session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page with logout message
session_start();
$_SESSION['success_message'] = "Anda berhasil logout";
header("Location: login.php");
exit();
?> 