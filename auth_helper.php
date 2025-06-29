<?php
// Authentication helper functions

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Get current user data
function get_logged_in_user() {
    if (is_logged_in()) {
        return array(
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        );
    }
    return null;
}

// Require login (redirect to login if not logged in)
function require_login($redirect_url = null) {
    if (!is_logged_in()) {
        if ($redirect_url) {
            $_SESSION['redirect_url'] = $redirect_url;
        }
        header("Location: login.php");
        exit();
    }
}

// Require guest (redirect to index if already logged in)
function require_guest() {
    if (is_logged_in()) {
        header("Location: index.php");
        exit();
    }
}

// Get user by ID
function get_user_by_id($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Error getting user: " . $e->getMessage());
        return null;
    }
}

// Update user profile
function update_user_profile($user_id, $username, $email, $pdo) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $user_id]);
    } catch(PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

// Change password
function change_password($user_id, $new_password, $pdo) {
    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed_password, $user_id]);
    } catch(PDOException $e) {
        error_log("Error changing password: " . $e->getMessage());
        return false;
    }
}

// Check if email exists (for registration validation)
function email_exists($email, $pdo, $exclude_user_id = null) {
    try {
        if ($exclude_user_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $exclude_user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Error checking email: " . $e->getMessage());
        return false;
    }
}

// Check if username exists (for registration validation)
function username_exists($username, $pdo, $exclude_user_id = null) {
    try {
        if ($exclude_user_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $exclude_user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Error checking username: " . $e->getMessage());
        return false;
    }
}
?> 