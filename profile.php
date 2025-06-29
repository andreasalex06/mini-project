<?php
session_start();
require_once 'auth_helper.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();

// Generate avatar initial
$avatar_initial = strtoupper(substr($current_user['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Literaturku</title>
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

<header>
  <div class="logo">
    <h1>Literaturku</h1>
  </div>
  <nav>
    <?php include 'navigasi.php'; ?>
  </nav>
</header>

<main class="profile-main">
    <div class="profile-container">
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo $avatar_initial; ?>
            </div>
            <h1 class="profile-title">Profile Pengguna</h1>
            <p class="profile-subtitle">Kelola informasi akun dan pengaturan Anda</p>
        </div>

        <!-- User Information Card -->
        <div class="profile-card">
            <h3>Informasi Akun</h3>
            <div class="profile-info">
                <div class="label">Username:</div>
                <div class="value"><?php echo htmlspecialchars($current_user['username']); ?></div>
                
                <div class="label">Email:</div>
                <div class="value"><?php echo htmlspecialchars($current_user['email']); ?></div>
                
                <div class="label">ID User:</div>
                <div class="value">#<?php echo htmlspecialchars($current_user['id']); ?></div>
                
                <div class="label">Status:</div>
                <div class="value">✅ Aktif</div>
            </div>
        </div>

        <!-- Profile Stats -->
        <div class="profile-card">
            <h3>Statistik Akun</h3>
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Akun Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Artikel</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Favorit</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Today</div>
                    <div class="stat-label">Terakhir Login</div>
                </div>
            </div>
        </div>

        <!-- Profile Actions -->
        <div class="profile-actions">
            <h3>🛠️ Aksi Pengguna</h3>
            <div class="action-buttons">
                <a href="index.php" class="action-btn secondary">
                    🏠 Kembali ke Beranda
                </a>
                <a href="#" onclick="editProfile()" class="action-btn primary">
                    ✏️ Edit Profile
                </a>
                <a href="#" onclick="confirmLogout()" class="action-btn danger">
                    🚪 Logout
                </a>
            </div>
            
            <!-- Success Badge -->
            <div class="success-badge">
                <h4>🎉 Sistem Authentication Berhasil!</h4>
                <p>Anda berhasil login dan dapat melihat halaman yang dilindungi ini.</p>
                <small>Halaman ini hanya bisa diakses oleh user yang sudah login.</small>
            </div>
        </div>

    </div>
</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>

<script>
function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        // Loading effect
        document.body.style.opacity = '0.7';
        document.body.style.transition = 'opacity 0.3s ease';
        
        // Show loading message
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Logging out...';
        button.style.pointerEvents = 'none';
        
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 500);
    }
}

function editProfile() {
    alert('🚧 Fitur edit profile akan segera hadir!\n\nUntuk saat ini, Anda dapat:\n• Melihat informasi akun\n• Logout dari sistem\n• Kembali ke beranda');
}

// Add smooth scroll animation for cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.profile-card, .profile-header');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

</body>
</html> 