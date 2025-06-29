<?php
// Cek apakah user sudah login
$is_logged_in = is_logged_in();
$current_user = null;

if ($is_logged_in) {
    $current_user = get_logged_in_user();
}
?>

<ul class="nav-menu">
    <?php if ($is_logged_in): ?>
        <!-- Menu untuk user yang sudah login -->
        <li class="user-info">
            <span class="welcome-text">Selamat datang, <strong><?php echo htmlspecialchars($current_user['username']); ?></strong></span>
        </li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="#" onclick="confirmLogout()" class="logout-btn">Logout</a></li>
    <?php else: ?>
        <!-- Menu untuk user yang belum login -->
    <li><a href="login.php">Masuk</a></li>
    <li><a id="daftar-btn" href="register.php">Daftar</a></li>
    <?php endif; ?>
</ul>

<?php if ($is_logged_in): ?>
<!-- JavaScript untuk konfirmasi logout -->
<script>
function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        // Tampilkan loading sebentar
        document.body.style.opacity = '0.7';
        window.location.href = 'logout.php';
    }
}

// Tambahan: Auto refresh untuk cek session (opsional)
// Refresh setiap 30 menit untuk memastikan session masih aktif
setTimeout(function() {
    if (confirm('Session akan berakhir. Ingin memperpanjang session?')) {
        window.location.reload();
    }
}, 30 * 60 * 1000); // 30 menit
</script>
<?php endif; ?>
