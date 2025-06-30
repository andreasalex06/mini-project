<?php
// Cek apakah user sudah login
$is_logged_in = is_logged_in();
$current_user = null;

if ($is_logged_in) {
    $current_user = get_logged_in_user();
}
?>

<!-- Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        
        <!-- Brand/Logo -->
        <!-- <a class="navbar-brand fw-bold text-primary" href="index.php">
            <i class="bi bi-journal-bookmark me-2"></i>
            Literaturku
        </a> -->

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <!-- Main Navigation Links -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active fw-semibold' : ''; ?>" 
                       href="index.php">
                        <i class="bi bi-house me-1"></i>
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active fw-semibold' : ''; ?>" 
                       href="kategori.php">
                        <i class="bi bi-folder me-1"></i>
                        Kategori
                    </a>
                </li>
                <?php if ($is_logged_in): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tambah_artikel.php' ? 'active fw-semibold' : ''; ?>" 
                       href="tambah_artikel.php">
                        <i class="bi bi-plus-circle me-1"></i>
                        Tulis Artikel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_artikel.php' ? 'active fw-semibold' : ''; ?>" 
                       href="admin_artikel.php">
                        <i class="bi bi-gear me-1"></i>
                        Kelola Artikel
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- User Authentication Section -->
            <div class="navbar-nav">
                <?php if ($is_logged_in): ?>
                    <!-- User Dropdown Menu -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                 style="width: 32px; height: 32px; font-size: 14px;">
                                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                            </div>
                            <span class="d-none d-lg-inline text-muted">
                                <?php echo htmlspecialchars($current_user['username']); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo htmlspecialchars($current_user['username']); ?>
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person me-2"></i>
                                    Profile Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="admin_artikel.php">
                                    <i class="bi bi-file-text me-2"></i>
                                    Artikel Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tambah_artikel.php">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Tulis Artikel Baru
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" onclick="confirmLogout()">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest User Buttons -->
                    <div class="d-flex gap-2">
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            <span class="d-none d-sm-inline">Masuk</span>
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i>
                            <span class="d-none d-sm-inline">Daftar</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>

<?php if ($is_logged_in): ?>
<!-- Enhanced JavaScript untuk user interactions -->
<script>
// Logout confirmation with loading state
function confirmLogout() {
    // Create custom Bootstrap modal for better UX
    if (confirm('🚪 Apakah Anda yakin ingin logout?\n\nSesi Anda akan berakhir dan Anda perlu login kembali.')) {
        // Show loading state
        const userDropdown = document.getElementById('userDropdown');
        const originalHTML = userDropdown.innerHTML;
        
        userDropdown.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted">Logging out...</span>
            </div>
        `;
        
        // Add loading overlay
        document.body.style.pointerEvents = 'none';
        document.body.style.opacity = '0.8';
        
        // Redirect after short delay for UX
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 800);
    }
}

// Enhanced session management
let sessionWarningShown = false;
let sessionCheckInterval;

// Check session every 25 minutes
sessionCheckInterval = setInterval(function() {
    if (!sessionWarningShown) {
        sessionWarningShown = true;
        
        // Show warning 5 minutes before session expires
        if (confirm('⏰ Sesi Anda akan berakhir dalam 5 menit.\n\nKlik OK untuk memperpanjang sesi atau Cancel untuk logout.')) {
            // Refresh page to extend session
            sessionWarningShown = false;
            fetch(window.location.href, { method: 'HEAD' })
                .then(() => {
                    showSessionToast('success', 'Sesi berhasil diperpanjang');
                })
                .catch(() => {
                    showSessionToast('warning', 'Gagal memperpanjang sesi');
                });
        } else {
            // User chose to logout
            window.location.href = 'logout.php';
        }
    }
}, 25 * 60 * 1000); // 25 minutes

// Auto-save functionality for forms (if applicable)
document.addEventListener('DOMContentLoaded', function() {
    // Add active class styling to current page
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active', 'fw-semibold');
        }
    });

    // Add smooth scroll behavior to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Close mobile menu when clicking on nav links
    const navLinks2 = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks2.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
    });
});

// Utility function to show session-related toasts
function showSessionToast(type, message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('session-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'session-toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1056';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'session-toast-' + Date.now();
    const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi ${iconClass} text-${type} me-2"></i>
                <strong class="me-auto">Sistem</strong>
                <small class="text-muted">Sekarang</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Handle online/offline status
window.addEventListener('online', () => {
    showSessionToast('success', 'Koneksi internet tersambung kembali');
});

window.addEventListener('offline', () => {
    showSessionToast('warning', 'Koneksi internet terputus');
});

// Cleanup when page unloads
window.addEventListener('beforeunload', () => {
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
});
</script>
<?php endif; ?>
