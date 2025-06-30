<?php
require_once 'koneksi.php';

try {
    echo "<h2>🗄️ Setup Database Baru - FreeSQLDatabase.com</h2>";
    echo "<p><strong>Host:</strong> sql12.freesqldatabase.com</p>";
    echo "<p><strong>Database:</strong> sql12787593</p>";
    echo "<hr>";
    
    // 1. Buat tabel users jika belum ada
    echo "<h3>1️⃣ Membuat Tabel Users</h3>";
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL UNIQUE,
        `email` varchar(100) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_username` (`username`),
        KEY `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_users);
    echo "✅ Tabel 'users' berhasil dibuat/diupdate<br>";
    
    // 2. Buat tabel artikel dengan struktur baru
    echo "<h3>2️⃣ Membuat Tabel Artikel (Struktur Baru)</h3>";
    $sql_artikel = "CREATE TABLE IF NOT EXISTS `artikel` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `judul` varchar(255) NOT NULL,
        `konten` text NOT NULL,
        `ringkasan` text DEFAULT NULL,
        `kategori_id` int(11) DEFAULT NULL,
        `category_enum` enum('teknologi','pendidikan','bisnis','kesehatan','sains','lifestyle','olahraga','hiburan','umum') DEFAULT 'umum',
        `user_id` int(11) DEFAULT NULL,
        `gambar` varchar(255) DEFAULT NULL,
        `status` enum('draft','published','archived') DEFAULT 'published',
        `views` int(11) DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_category_enum` (`category_enum`),
        KEY `idx_user` (`user_id`),
        KEY `idx_status` (`status`),
        KEY `idx_created` (`created_at`),
        KEY `idx_views` (`views`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_artikel);
    echo "✅ Tabel 'artikel' berhasil dibuat dengan struktur baru<br>";
    echo "<small>🔹 Field 'created_at' dihandle oleh PHP (tanpa DEFAULT)</small><br>";
    
    // 3. Cek apakah ada user admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $admin_exists = $stmt->fetchColumn() > 0;
    
    if (!$admin_exists) {
        echo "<h3>3️⃣ Membuat User Admin Default</h3>";
        
        $admin_data = [
            'username' => 'admin',
            'email' => 'admin@literaturku.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT)
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(array_values($admin_data));
        echo "✅ User admin berhasil dibuat<br>";
        echo "<small>🔹 Username: admin, Password: admin123</small><br>";
    } else {
        echo "<h3>3️⃣ User Admin</h3>";
        echo "✅ User admin sudah ada<br>";
    }
    
    // 4. Insert artikel sample
    $stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
    $artikel_count = $stmt->fetchColumn();
    
    if ($artikel_count == 0) {
        echo "<h3>4️⃣ Menambahkan Artikel Sample</h3>";
        
        // Get admin user ID
        $stmt = $pdo->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
        $admin_id = $stmt->fetchColumn();
        
        $sample_articles = [
            [
                'judul' => 'Selamat Datang di Literaturku Platform Digital',
                'konten' => 'Literaturku adalah platform digital terdepan untuk berbagi pengetahuan dan literasi di Indonesia. Platform ini dirancang khusus untuk memenuhi kebutuhan modern akan akses informasi yang berkualitas dan mudah dijangkau.

Di Literaturku, Anda dapat menemukan berbagai artikel berkualitas tinggi dari berbagai kategori seperti teknologi, pendidikan, bisnis, kesehatan, sains, lifestyle, olahraga, dan hiburan.

Mari bersama-sama membangun komunitas literasi digital yang cerdas dan produktif. Bergabunglah dengan ribuan pembaca lainnya untuk memperluas wawasan dan pengetahuan!

Developed by Andreas Alex dengan teknologi PHP modern dan Bootstrap UI framework.',
                'ringkasan' => 'Platform literasi digital terdepan Indonesia untuk berbagi pengetahuan berkualitas dengan 9 kategori artikel lengkap.',
                'category_enum' => 'pendidikan',
                'user_id' => $admin_id,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'judul' => 'Revolusi Artificial Intelligence: Transformasi Digital di Era Modern',
                'konten' => 'Artificial Intelligence (AI) telah menjadi kekuatan transformatif yang mengubah cara kita bekerja, belajar, dan berinteraksi di era digital modern. Teknologi ini tidak lagi menjadi fantasi science fiction, melainkan realitas yang mempengaruhi kehidupan sehari-hari.

Machine Learning dan Deep Learning kini telah mencapai tingkat sofistikasi yang memungkinkan komputer untuk mengenali pola kompleks dalam data besar, memproses bahasa natural dengan akurasi tinggi, dan membuat prediksi yang personal.

Ekspektasi untuk masa depan AI sangat menjanjikan dengan integrasi AI dalam IoT, AI yang lebih ethical dan transparent, serta kolaborasi human-AI yang lebih seamless.',
                'ringkasan' => 'Eksplorasi mendalam tentang revolusi AI dan transformasi digital yang mengubah berbagai aspek kehidupan modern.',
                'category_enum' => 'teknologi',
                'user_id' => $admin_id,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'judul' => 'Strategi Bisnis Digital: Membangun Startup Sukses',
                'konten' => 'Era ekonomi digital telah membuka peluang tak terbatas bagi entrepreneur untuk membangun bisnis inovatif. Namun, kesuksesan di dunia digital memerlukan strategi yang tepat dan pemahaman mendalam tentang ekosistem digital.

Fondasi bisnis digital yang kuat meliputi validasi ide bisnis, riset mendalam tentang target market, analisis kompetitor, dan model bisnis yang scalable seperti freemium model dan subscription-based revenue stream.

Kunci sukses mendapatkan investasi adalah traction dan growth metrics yang solid, clear business model, strong team dengan track record, dan market opportunity yang besar.',
                'ringkasan' => 'Panduan komprehensif membangun startup sukses dengan strategi bisnis digital modern, dari validasi ide hingga scaling bisnis.',
                'category_enum' => 'bisnis',
                'user_id' => $admin_id,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO artikel (judul, konten, ringkasan, category_enum, user_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sample_articles as $article) {
            $stmt->execute(array_values($article));
        }
        echo "✅ " . count($sample_articles) . " artikel sample berhasil ditambahkan<br>";
    } else {
        echo "<h3>4️⃣ Artikel Sample</h3>";
        echo "✅ Artikel sample sudah ada ($artikel_count artikel)<br>";
    }
    
    // 5. Tampilkan statistik
    echo "<h3>📊 Statistik Database Baru:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
    $total_artikel = $stmt->fetchColumn();
    echo "📄 Total Artikel: <strong>$total_artikel</strong><br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    echo "👥 Total Users: <strong>$total_users</strong><br>";
    
    // Statistik per kategori
    echo "<h4>📁 Artikel per Kategori:</h4>";
    $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total FROM artikel WHERE status = 'published' GROUP BY category_enum ORDER BY total DESC");
    $category_stats = $stmt->fetchAll();
    
    if (!empty($category_stats)) {
        echo "<ul>";
        foreach ($category_stats as $stat) {
            $kategori_nama = ucfirst($stat['category_enum']);
            echo "<li>$kategori_nama: <strong>{$stat['total']}</strong> artikel</li>";
        }
        echo "</ul>";
    }
    
    // 6. Test fungsi
    echo "<h3>🧪 Test Fungsi PHP</h3>";
    
    require_once 'artikel_functions_enum.php';
    
    // Test getArtikel function
    $test_articles = getArtikel($pdo, 3);
    if (!empty($test_articles)) {
        echo "✅ Fungsi getArtikel() berfungsi normal<br>";
        echo "<small>🔹 Berhasil mengambil " . count($test_articles) . " artikel</small><br>";
    } else {
        echo "⚠️ Fungsi getArtikel() perlu diperiksa<br>";
    }
    
    echo "✅ Fungsi tambahArtikel() telah diupdate untuk handle created_at<br>";
    echo "<small>🔹 Field created_at sekarang dihandle oleh PHP</small><br>";
    
    echo "<br><h3>🎉 Setup Database Baru Selesai!</h3>";
    echo "<p>✅ Database FreeSQLDatabase.com siap digunakan</p>";
    echo "<p>✅ Struktur tabel sesuai spesifikasi</p>";
    echo "<p>✅ Fungsi PHP telah disesuaikan</p>";
    echo "<p>✅ Field created_at dihandle oleh PHP</p>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #e8f5e8; border-left: 4px solid #4caf50;'>";
    echo "<h4 style='margin: 0 0 10px 0; color: #2e7d32;'>🔐 Informasi Login Admin:</h4>";
    echo "<p style='margin: 5px 0;'><strong>Username:</strong> admin</p>";
    echo "<p style='margin: 5px 0;'><strong>Password:</strong> admin123</p>";
    echo "<p style='margin: 5px 0; font-size: 0.9em; color: #666;'>Silakan ganti password setelah login pertama</p>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #1a73e8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>🏠 Beranda</a>";
    echo "<a href='login.php' style='background: #34a853; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>🔐 Login Admin</a>";
    echo "<a href='admin_artikel.php' style='background: #ea4335; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>⚙️ Kelola Artikel</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3>❌ Database Error:</h3>";
    echo "<div style='background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<p style='color: #c62828; margin: 0;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='margin: 10px 0 0 0; font-size: 0.9em; color: #666;'>Pastikan koneksi database sudah benar dan server database dapat diakses.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andreas Alex">
    <title>Setup Database Baru - Literaturku</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 20px; 
            line-height: 1.6; 
            background: #f8f9fa;
        }
        h2 { color: #1a73e8; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
        h3 { color: #34a853; margin-top: 30px; }
        h4 { color: #ea4335; }
        small { color: #5f6368; }
        hr { border: none; border-top: 1px solid #dadce0; margin: 20px 0; }
    </style>
</head>
<body>
</body>
</html> 