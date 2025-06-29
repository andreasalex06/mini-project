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
    <title>Halaman Login</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

    <div class="auth-container">
    <h2>Login Akun</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
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

    <form action="proses_login.php" method="POST">
        
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo isset($old_data['email']) ? htmlspecialchars($old_data['email']) : ''; ?>" 
                       required>
        </div>
        
            <div class="form-group">
                <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

            <button type="submit" class="btn-submit">Login</button>

        </form>

        <div class="auth-link">
            <a href="index.php" class="btn-home">← Kembali ke Beranda</a>
        </div>

        <div class="auth-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>

</body>
</html>