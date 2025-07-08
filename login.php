<?php
require_once 'koneksi.php';

$title = 'Login - Blog Sederhana';
$error = '';

// Jika sudah login, redirect ke halaman tujuan atau admin
if (isset($_SESSION['user_id'])) {
    $redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin.php';
    header('Location: ' . $redirect_to);
    exit();
}

// Simpan halaman asal jika ada
$redirect_after_login = 'admin.php'; // default
if (isset($_GET['redirect'])) {
    $redirect_after_login = $_GET['redirect'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    // Cek apakah referer dari domain yang sama
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $current_host = $_SERVER['HTTP_HOST'];
    
    if ($referer_host === $current_host) {
        $redirect_after_login = $_SERVER['HTTP_REFERER'];
    }
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'admin.php';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect ke halaman tujuan atau admin
                header('Location: ' . $redirect_to);
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h4>🔑 Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <!-- Hidden field untuk menyimpan tujuan redirect -->
                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_after_login); ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username atau Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 