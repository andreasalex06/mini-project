<?php
/**
 * Koneksi Database Sederhana
 * File ini berisi konfigurasi database yang mudah dipahami
 */

// Konfigurasi Database
$host = 'localhost';
$database = 'literat2_db';
$username = 'root';
$password = '';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set charset
    $pdo->exec("SET NAMES utf8");
    
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi helper sederhana
function getConnection() {
    global $pdo;
    return $pdo;
}

// Fungsi helper untuk redirect
function redirectTo($url, $exit = true) {
    header('Location: ' . $url);
    if ($exit) exit();
}

// Fungsi helper untuk membuat URL redirect dengan parameter
function loginRedirectUrl($target_page = null) {
    if ($target_page === null) {
        $target_page = $_SERVER['REQUEST_URI'];
    }
    return 'login.php?redirect=' . urlencode($target_page);
}

// Fungsi helper untuk redirect ke login dengan parameter
function redirectToLogin($target_page = null) {
    redirectTo(loginRedirectUrl($target_page));
}

// Base URL untuk project - otomatis deteksi hosting atau localhost
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);
$path = ($path === '\\' || $path === '/') ? '' : $path; // Handle root directory
define('BASE_URL', $protocol . $host . $path . '/');

// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}