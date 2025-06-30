<?php
session_start();

// Get errors and old data from session
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : array();
$old_data = isset($_SESSION['old_data']) ? $_SESSION['old_data'] : array();
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['old_data']);
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="Daftar akun baru di Literaturku - Platform literasi modern untuk menambah dan membagikan literasi kepada dunia">
    <title>Halaman Registrasi - Literaturku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus-fill text-success fs-1 mb-3"></i>
                            <h2 class="card-title text-success">Buat Akun Baru</h2>
                            <p class="text-muted">Bergabung dengan Literaturku</p>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <form action="proses_register.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-2"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($old_data['username']) ? htmlspecialchars($old_data['username']) : ''; ?>" 
                                       placeholder="Masukkan username Anda" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($old_data['email']) ? htmlspecialchars($old_data['email']) : ''; ?>" 
                                       placeholder="Masukkan email Anda" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password Anda" required>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>Konfirmasi Password
                                </label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       placeholder="Ulangi password Anda" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                                <i class="bi bi-person-plus me-2"></i>Register
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted mb-0">
                                Sudah punya akun? 
                                <a href="login.php" class="text-success text-decoration-none fw-bold">Login di sini</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>