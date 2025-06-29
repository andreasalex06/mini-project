<?php
require_once 'koneksi.php';

try {
    echo "<h2>🗄️ Setup Tabel Artikel - db_artikel</h2>";
    
    // 1. Cek apakah tabel artikel sudah ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'artikel'");
    $artikel_exists = $stmt->rowCount() > 0;
    
    if ($artikel_exists) {
        echo "✅ Tabel 'artikel' sudah ada<br>";
        
        // Tampilkan struktur tabel
        echo "<h3>Struktur Tabel Artikel:</h3>";
        $stmt = $pdo->query('DESCRIBE artikel');
        $columns = $stmt->fetchAll();
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "❌ Tabel 'artikel' belum ada<br>";
        echo "<p>Membuat tabel artikel...</p>";
        
        // Buat tabel artikel
        $sql_artikel = "CREATE TABLE IF NOT EXISTS artikel (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            konten TEXT NOT NULL,
            ringkasan TEXT,
            kategori_id INT,
            user_id INT,
            gambar VARCHAR(255),
            status ENUM('draft', 'published', 'archived') DEFAULT 'published',
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_kategori (kategori_id),
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        )";
        
        $pdo->exec($sql_artikel);
        echo "✅ Tabel 'artikel' berhasil dibuat<br>";
    }
    
    // 2. Cek apakah tabel kategori sudah ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'kategori'");
    $kategori_exists = $stmt->rowCount() > 0;
    
    if (!$kategori_exists) {
        echo "<p>Membuat tabel kategori...</p>";
        
        // Buat tabel kategori
        $sql_kategori = "CREATE TABLE IF NOT EXISTS kategori (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL UNIQUE,
            deskripsi TEXT,
            warna VARCHAR(7) DEFAULT '#10367d',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql_kategori);
        echo "✅ Tabel 'kategori' berhasil dibuat<br>";
        
        // Insert kategori default
        $default_categories = [
            ['Teknologi', 'Artikel tentang teknologi terbaru', '#667eea'],
            ['Pendidikan', 'Artikel seputar dunia pendidikan', '#2ecc71'],
            ['Bisnis', 'Artikel tentang dunia bisnis dan ekonomi', '#e74c3c'],
            ['Kesehatan', 'Artikel tentang kesehatan dan gaya hidup', '#f39c12'],
            ['Sains', 'Artikel ilmu pengetahuan', '#9b59b6']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO kategori (nama, deskripsi, warna) VALUES (?, ?, ?)");
        foreach ($default_categories as $cat) {
            $stmt->execute($cat);
        }
        echo "✅ Kategori default berhasil ditambahkan<br>";
    } else {
        echo "✅ Tabel 'kategori' sudah ada<br>";
    }
    
    // 3. Insert artikel sample jika belum ada
    $stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
    $artikel_count = $stmt->fetchColumn();
    
    if ($artikel_count == 0) {
        echo "<p>Menambahkan artikel sample...</p>";
        
        $sample_articles = [
            [
                'judul' => 'Selamat Datang di Literaturku',
                'konten' => 'Literaturku adalah platform untuk berbagi pengetahuan dan literasi. Di sini Anda dapat membaca berbagai artikel menarik dari berbagai kategori seperti teknologi, pendidikan, bisnis, kesehatan, dan sains. Mari bersama-sama membangun komunitas yang gemar membaca dan berbagi pengetahuan!',
                'ringkasan' => 'Platform untuk berbagi pengetahuan dan literasi dengan berbagai kategori artikel menarik.',
                'kategori_id' => 2, // Pendidikan
                'user_id' => 1, // Admin
                'status' => 'published'
            ],
            [
                'judul' => 'Pentingnya Literasi Digital di Era Modern',
                'konten' => 'Literasi digital menjadi sangat penting di era modern ini. Dengan kemajuan teknologi yang pesat, setiap orang perlu memiliki kemampuan untuk menggunakan teknologi digital dengan bijak dan efektif. Literasi digital tidak hanya tentang cara menggunakan komputer atau smartphone, tetapi juga tentang memahami informasi digital, keamanan online, dan etika digital.',
                'ringkasan' => 'Pentingnya memahami dan menguasai literasi digital di era teknologi modern.',
                'kategori_id' => 1, // Teknologi
                'user_id' => 1, // Admin
                'status' => 'published'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO artikel (judul, konten, ringkasan, kategori_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($sample_articles as $article) {
            $stmt->execute(array_values($article));
        }
        echo "✅ Artikel sample berhasil ditambahkan<br>";
    }
    
    // 4. Tampilkan statistik
    echo "<h3>📊 Statistik Database:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
    $total_artikel = $stmt->fetchColumn();
    echo "📄 Total Artikel: $total_artikel<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM kategori");
    $total_kategori = $stmt->fetchColumn();
    echo "📁 Total Kategori: $total_kategori<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    echo "👥 Total Users: $total_users<br>";
    
    echo "<br><h3>🎉 Setup Selesai!</h3>";
    echo "<p>Database siap untuk sistem CRUD artikel.</p>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #10367d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>← Kembali ke Beranda</a>";
    echo "<a href='admin_artikel.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Kelola Artikel</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 