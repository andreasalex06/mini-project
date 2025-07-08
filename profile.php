<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$title = 'Edit Profil - Blog Sederhana';
$error = '';
$success = '';

// Ambil data user
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input dasar
    if (empty($nama_lengkap) || empty($username) || empty($email)) {
        $error = 'Nama lengkap, username, dan email harus diisi!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        try {
            // Cek apakah username atau email sudah digunakan user lain
            $sql_check = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$username, $email, $_SESSION['user_id']]);
            
            if ($stmt_check->fetch()) {
                $error = 'Username atau email sudah digunakan user lain!';
            } else {
                // Cek jika ada perubahan password
                $update_password = false;
                $password_hash = $user['password'];
                
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Password lama harus diisi untuk mengubah password!';
                    } elseif (!password_verify($current_password, $user['password'])) {
                        $error = 'Password lama tidak benar!';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'Password baru minimal 6 karakter!';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Password baru dan konfirmasi password tidak sama!';
                    } else {
                        $update_password = true;
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }
                
                if (!$error) {
                    // Update data user
                    $sql = "UPDATE users SET 
                            nama_lengkap = ?, username = ?, email = ?, bio = ?, password = ?, 
                            updated_at = CURRENT_TIMESTAMP 
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nama_lengkap, $username, $email, $bio, $password_hash, $_SESSION['user_id']]);
                    
                    // Update session data
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    // Update data user untuk form
                    $user['nama_lengkap'] = $nama_lengkap;
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['bio'] = $bio;
                    
                    $success = 'Profil berhasil diupdate!';
                    if ($update_password) {
                        $success .= ' Password juga berhasil diubah.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Ambil statistik user
$sql_stats = "SELECT 
                COUNT(*) as total_articles,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_articles,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_articles,
                SUM(views) as total_views
              FROM artikels WHERE user_id = ?";
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->execute([$_SESSION['user_id']]);
$stats = $stmt_stats->fetch();

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>👤 Edit Profil</h1>
        <p class="lead">Kelola informasi profil dan akun Anda</p>
        <hr>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Edit Profil -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>✏️ Edit Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" 
                                       required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       required maxlength="50">
                                <div class="form-text">Minimal 3 karakter, hanya huruf, angka, dan underscore</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                  placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        <div class="form-text">Deskripsi singkat tentang diri Anda (opsional)</div>
                    </div>
                    
                    <hr>
                    
                    <h6>🔐 Ubah Password (Opsional)</h6>
                    <p class="text-muted">Kosongkan jika tidak ingin mengubah password</p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Lama</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Minimal 6 karakter</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="admin.php" class="btn btn-secondary me-md-2">
                            ← Kembali ke Admin
                        </a>
                        <button type="submit" class="btn btn-primary">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Profil dan Statistik -->
    <div class="col-md-4">
        <!-- Info Akun -->
        <div class="card mb-4">
            <div class="card-header">
                <h6>ℹ️ Info Akun</h6>
            </div>
            <div class="card-body">
                <p><strong>Role:</strong> <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></p>
                <p><strong>Bergabung:</strong><br><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Terakhir update:</strong><br><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></p>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="card mb-4">
            <div class="card-header">
                <h6>📊 Statistik Artikel</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary"><?php echo $stats['total_articles'] ?: 0; ?></h4>
                        <p class="small">Total Artikel</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success"><?php echo $stats['published_articles'] ?: 0; ?></h4>
                        <p class="small">Dipublikasi</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning"><?php echo $stats['draft_articles'] ?: 0; ?></h4>
                        <p class="small">Draft</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info"><?php echo $stats['total_views'] ?: 0; ?></h4>
                        <p class="small">Total Views</p>
                    </div>
                </div>
                
                <?php if ($stats['total_articles'] > 0): ?>
                    <div class="d-grid">
                        <a href="artikel_manager.php" class="btn btn-outline-primary btn-sm">
                            📝 Kelola Artikel
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6>🚀 Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="artikel_add.php" class="btn btn-primary btn-sm">
                        ✍️ Tulis Artikel Baru
                    </a>
                    <a href="artikel_manager.php" class="btn btn-info btn-sm">
                        📝 Kelola Artikel
                    </a>
                    <?php if ($user['role'] == 'admin'): ?>
                        <a href="kategori_manager.php" class="btn btn-success btn-sm">
                            📂 Kelola Kategori
                        </a>
                    <?php endif; ?>
                    <hr>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tips -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6>💡 Tips Profil</h6>
                <ul class="small mb-0">
                    <li><strong>Username:</strong> Gunakan nama yang mudah diingat dan profesional</li>
                    <li><strong>Email:</strong> Pastikan email aktif untuk notifikasi</li>
                    <li><strong>Bio:</strong> Ceritakan keahlian atau minat Anda untuk pembaca</li>
                    <li><strong>Password:</strong> Gunakan kombinasi huruf, angka, dan simbol untuk keamanan</li>
                    <li><strong>Privasi:</strong> Jangan bagikan informasi sensitif di bio</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Validasi form
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const currentPasswordInput = document.getElementById('current_password');
    
    function validatePasswords() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (newPassword && newPassword !== confirmPassword) {
            confirmPasswordInput.setCustomValidity('Password tidak sama');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
        
        // Jika ada password baru, current password wajib diisi
        if (newPassword && !currentPasswordInput.value) {
            currentPasswordInput.setCustomValidity('Password lama harus diisi');
        } else {
            currentPasswordInput.setCustomValidity('');
        }
    }
    
    newPasswordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
    currentPasswordInput.addEventListener('input', validatePasswords);
});
</script>

<?php include 'footer.php'; ?> 