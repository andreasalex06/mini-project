<?php
require_once 'koneksi.php';

// Tentukan halaman tujuan setelah logout
$redirect_to = 'index.php'; // default ke halaman utama

if (isset($_GET['redirect'])) {
    // Jika ada parameter redirect
    $redirect_to = $_GET['redirect'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    // Cek apakah referer dari domain yang sama dan bukan halaman admin
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $current_host = $_SERVER['HTTP_HOST'];
    $referer_path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    
    // Daftar halaman yang tidak boleh jadi tujuan redirect setelah logout
    $restricted_pages = ['/admin.php', '/profile.php', '/artikel_add.php', '/artikel_edit.php', '/kategori_manager.php', '/artikel_manager.php'];
    
    if ($referer_host === $current_host && !in_array($referer_path, $restricted_pages)) {
        $redirect_to = $_SERVER['HTTP_REFERER'];
    }
}

// Hapus semua session
session_destroy();

// Redirect ke halaman tujuan
header('Location: ' . $redirect_to);
exit();
?> 