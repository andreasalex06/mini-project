<?php
session_start();
require_once 'koneksi.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize input data
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    $errors = array();
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username maksimal 50 karakter";
    }
    
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!validate_email($email)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak sesuai";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username atau email sudah terdaftar";
            }
        } catch(PDOException $e) {
            $errors[] = "Terjadi kesalahan sistem";
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        try {
            $hashed_password = hash_password($password);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashed_password]);
            
            if ($result) {
                $_SESSION['success_message'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Gagal mendaftarkan akun. Silakan coba lagi.";
            }
            
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Username atau email sudah terdaftar";
            } else {
                $errors[] = "Terjadi kesalahan sistem";
                error_log("Database error: " . $e->getMessage());
            }
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: register.php");
        exit();
    }
    
} else {
    // If not POST request, redirect to register page
    header("Location: register.php");
    exit();
}
?> 