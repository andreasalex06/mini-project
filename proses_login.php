<?php
session_start();
require_once 'koneksi.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize input data
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $errors = array();
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!validate_email($email)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    }
    
    // If no validation errors, check credentials
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verify_password($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Update last login (optional)
                try {
                    $stmt = $pdo->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$user['id']]);
                } catch(PDOException $e) {
                    // Log error but don't fail login
                    error_log("Error updating last login: " . $e->getMessage());
                }
                
                // Redirect to dashboard or main page
                if (isset($_SESSION['redirect_url'])) {
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect_url);
                } else {
                    header("Location: index.php");
                }
                exit();
                
            } else {
                $errors[] = "Email atau password salah";
            }
            
        } catch(PDOException $e) {
            $errors[] = "Terjadi kesalahan sistem";
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: login.php");
        exit();
    }
    
} else {
    // If not POST request, redirect to login page
    header("Location: login.php");
    exit();
}
?> 