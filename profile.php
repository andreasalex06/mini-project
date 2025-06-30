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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body class="bg-light">

<!-- Bootstrap Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand text-primary d-flex align-items-center" href="index.php">
            <i class="bi bi-book-fill me-2 fs-3"></i>
            <span>Literaturku</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="ms-auto">
                <?php include 'navigasi.php'; ?>
            </div>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                
                <!-- Profile Header -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <div class="profile-avatar">
                            <?php echo $avatar_initial; ?>
                        </div>
                        <h1 class="h3 mb-2">Profile Pengguna</h1>
                        <p class="text-muted">Kelola informasi akun dan pengaturan Anda</p>
                    </div>
                </div>

                <!-- User Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-circle me-2"></i>
                            Informasi Akun
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong class="text-muted">Username:</strong>
                                <div class="fs-5"><?php echo htmlspecialchars($current_user['username']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-muted">Email:</strong>
                                <div class="fs-5"><?php echo htmlspecialchars($current_user['email']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-muted">ID User:</strong>
                                <div class="fs-5 font-monospace">#<?php echo htmlspecialchars($current_user['id']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-muted">Status:</strong>
                                <div class="fs-5">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Aktif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Stats -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>
                            Statistik Akun
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <div class="card text-center bg-primary text-white">
                                    <div class="card-body">
                                        <h3 class="card-title">1</h3>
                                        <p class="card-text">Akun Aktif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h3 class="card-title">0</h3>
                                        <p class="card-text">Artikel</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card text-center bg-warning text-white">
                                    <div class="card-body">
                                        <h3 class="card-title">0</h3>
                                        <p class="card-text">Favorit</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Today</h5>
                                        <p class="card-text">Terakhir Login</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Aksi Pengguna
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>Kembali ke Beranda
                            </a>
                            <a href="#" onclick="editProfile()" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </a>
                            <a href="#" onclick="confirmLogout()" class="btn btn-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                        
                        <!-- Success Badge -->
                        <div class="alert alert-success" role="alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle me-2"></i>
                                Sistem Authentication Berhasil!
                            </h6>
                            <p class="mb-1">Anda berhasil login dan dapat melihat halaman yang dilindungi ini.</p>
                            <hr>
                            <small class="text-muted">Halaman ini hanya bisa diakses oleh user yang sudah login.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<!-- Bootstrap Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <?php include 'footer.php'; ?>
    </div>
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
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Logging out...';
        button.style.pointerEvents = 'none';
        
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 500);
    }
}

function editProfile() {
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal') || createEditModal());
    modal.show();
}

function createEditModal() {
    const modalHTML = `
        <div class="modal fade" id="editProfileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil me-2"></i>
                            Edit Profile
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Fitur Segera Hadir!</strong>
                        </div>
                        <p>Fitur edit profile akan segera tersedia. Untuk saat ini, Anda dapat:</p>
                        <ul>
                            <li>Melihat informasi akun</li>
                            <li>Logout dari sistem</li>
                            <li>Kembali ke beranda</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" disabled>
                            <i class="bi bi-gear me-2"></i>
                            Pengaturan (Segera)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    return document.getElementById('editProfileModal');
}

// Add smooth scroll animation for cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    
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

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 