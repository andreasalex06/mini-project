<?php
// SQL untuk database db_artikel - Sistem Login/Register
// Database: db_artikel

/*
-- Pastikan database db_artikel sudah dibuat
USE db_artikel;

-- Membuat tabel users untuk sistem login/register
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index untuk performa yang lebih baik
CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_username ON users(username);

-- Insert admin default (opsional)
INSERT IGNORE INTO users (username, email, password) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Password default: 'password'
*/

// Function untuk setup database otomatis
function setupDatabase($pdo) {
    try {
        echo "<h2>🔧 Setting up Database...</h2>";
        
        // 1. Buat tabel users
        $sql_users = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql_users);
        echo "✅ Tabel 'users' berhasil dibuat/sudah ada<br>";
        
        // 2. Tambahkan index
        try {
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON users(email)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_username ON users(username)");
            echo "✅ Index berhasil ditambahkan<br>";
        } catch(PDOException $e) {
            // Index mungkin sudah ada
            echo "ℹ️ Index sudah ada atau error: " . $e->getMessage() . "<br>";
        }
        
        // 3. Cek apakah ada user admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin_exists = $stmt->fetchColumn() > 0;
        
        if (!$admin_exists) {
            // Buat user admin default
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute(['admin', 'admin@artikel.com', $admin_password]);
            
            if ($result) {
                echo "✅ User admin default berhasil dibuat<br>";
                echo "&nbsp;&nbsp;&nbsp;Username: admin<br>";
                echo "&nbsp;&nbsp;&nbsp;Email: admin@artikel.com<br>";
                echo "&nbsp;&nbsp;&nbsp;Password: admin123<br>";
            }
        } else {
            echo "ℹ️ User admin sudah ada<br>";
        }
        
        echo "<br><h3>🎉 Database setup selesai!</h3>";
        echo "<p>Sistem login/register siap digunakan.</p>";
        
        return true;
        
    } catch(PDOException $e) {
        echo "<h3>❌ Error setup database:</h3>";
        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
        return false;
    }
}

// Function untuk reset database (hati-hati!)
function resetDatabase($pdo) {
    try {
        echo "<h2>⚠️ Resetting Database...</h2>";
        
        // Hapus dan buat ulang tabel users
        $pdo->exec("DROP TABLE IF EXISTS users");
        echo "🗑️ Tabel users lama dihapus<br>";
        
        // Setup ulang
        return setupDatabase($pdo);
        
    } catch(PDOException $e) {
        echo "<h3>❌ Error reset database:</h3>";
        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
        return false;
    }
}

// Jika file diakses langsung, jalankan setup
if (basename($_SERVER['PHP_SELF']) == 'database.php') {
    require_once 'koneksi.php';
    
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Database Setup - db_artikel</title></head><body>";
    echo "<h1>🗄️ Database Setup - db_artikel</h1>";
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'setup':
                setupDatabase($pdo);
                break;
            case 'reset':
                if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
                    resetDatabase($pdo);
                } else {
                    echo "<h3>⚠️ Konfirmasi Reset Database</h3>";
                    echo "<p>Ini akan menghapus SEMUA data user yang ada!</p>";
                    echo "<a href='?action=reset&confirm=yes' style='background: red; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Ya, Reset Database</a> ";
                    echo "<a href='database.php' style='background: gray; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Batal</a>";
                }
                break;
            default:
                echo "<p>Action tidak dikenal.</p>";
        }
    } else {
        echo "<h3>Pilih Action:</h3>";
        echo "<a href='?action=setup' style='background: green; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>Setup Database</a><br><br>";
        echo "<a href='check_database.php' style='background: blue; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>Cek Status Database</a><br><br>";
        echo "<a href='?action=reset' style='background: red; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>Reset Database (Hati-hati!)</a><br><br>";
        echo "<a href='index.php' style='background: gray; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>Kembali ke Halaman Utama</a>";
    }
    
    echo "</body></html>";
}
?>
