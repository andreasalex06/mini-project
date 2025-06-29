<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['errors'] = ['Artikel tidak ditemukan'];
    header("Location: index.php");
    exit();
}

// Get article details for authorization check
$artikel = getArtikelById($pdo, $id);

if (!$artikel) {
    $_SESSION['errors'] = ['Artikel tidak ditemukan'];
    header("Location: index.php");
    exit();
}

// Check authorization - only article owner can delete
if ($artikel['user_id'] != $current_user['id']) {
    $_SESSION['errors'] = ['Anda tidak memiliki izin untuk menghapus artikel ini'];
    header("Location: artikel_detail.php?id=" . $id);
    exit();
}

// Handle deletion
if (deleteArtikel($pdo, $id)) {
    $_SESSION['success_message'] = "Artikel '" . htmlspecialchars($artikel['judul']) . "' berhasil dihapus";
} else {
    $_SESSION['errors'] = ['Gagal menghapus artikel. Silakan coba lagi.'];
}

// Redirect to homepage
header("Location: index.php");
exit();
?> 