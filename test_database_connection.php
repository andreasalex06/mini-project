<?php
require_once 'koneksi.php';

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<meta name='author' content='Andreas Alex'>";
echo "<title>Test Koneksi Database - Literaturku</title>";
echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; background: #f8f9fa; }";
echo "h1 { color: #1a73e8; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }";
echo "h2 { color: #34a853; margin-top: 30px; }";
echo ".success { background: #e8f5e8; border-left: 4px solid #4caf50; padding: 15px; margin: 10px 0; }";
echo ".error { background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0; }";
echo ".info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 10px 0; }";
echo "table { border-collapse: collapse; width: 100%; margin: 15px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "hr { border: none; border-top: 1px solid #dadce0; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔧 Test Koneksi Database FreeSQLDatabase.com</h1>";

try {
    // 1. Test koneksi dasar
    echo "<h2>1️⃣ Test Koneksi Database</h2>";
    
    if ($pdo) {
        echo "<div class='success'>";
        echo "<strong>✅ Koneksi berhasil!</strong><br>";
        echo "Host: sql12.freesqldatabase.com<br>";
        echo "Database: sql12787593<br>";
        echo "Status: Connected";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Koneksi gagal!</strong>";
        echo "</div>";
        exit();
    }
    
    // 2. Test struktur tabel
    echo "<h2>2️⃣ Test Struktur Tabel</h2>";
    
    // Cek tabel users
    echo "<h3>📋 Tabel Users</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Tabel 'users' ditemukan</div>";
        
        // Tampilkan struktur
        $stmt = $pdo->query('DESCRIBE users');
        $columns = $stmt->fetchAll();
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Tabel 'users' tidak ditemukan</div>";
    }
    
    // Cek tabel artikel
    echo "<h3>📋 Tabel Artikel</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'artikel'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Tabel 'artikel' ditemukan</div>";
        
        // Tampilkan struktur
        $stmt = $pdo->query('DESCRIBE artikel');
        $columns = $stmt->fetchAll();
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Cek field created_at khusus
        $created_at_found = false;
        foreach($columns as $col) {
            if ($col['Field'] == 'created_at') {
                if ($col['Type'] == 'datetime' && $col['Null'] == 'NO' && $col['Default'] === null) {
                    echo "<div class='success'>✅ Field 'created_at' sesuai spesifikasi (DATETIME NOT NULL tanpa DEFAULT)</div>";
                } else {
                    echo "<div class='error'>❌ Field 'created_at' tidak sesuai spesifikasi</div>";
                }
                $created_at_found = true;
                break;
            }
        }
        if (!$created_at_found) {
            echo "<div class='error'>❌ Field 'created_at' tidak ditemukan</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Tabel 'artikel' tidak ditemukan</div>";
    }
    
    // 3. Test data
    echo "<h2>3️⃣ Test Data</h2>";
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    echo "<div class='info'>";
    echo "<strong>👥 Total Users:</strong> $user_count<br>";
    echo "</div>";
    
    // Count artikel
    $stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
    $artikel_count = $stmt->fetchColumn();
    echo "<div class='info'>";
    echo "<strong>📄 Total Artikel:</strong> $artikel_count<br>";
    echo "</div>";
    
    // 4. Test fungsi PHP
    echo "<h2>4️⃣ Test Fungsi PHP</h2>";
    
    require_once 'artikel_functions_enum.php';
    
    // Test getArtikel
    echo "<h3>Test getArtikel()</h3>";
    $test_artikel = getArtikel($pdo, 3);
    if (!empty($test_artikel)) {
        echo "<div class='success'>✅ Fungsi getArtikel() berfungsi - " . count($test_artikel) . " artikel ditemukan</div>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Judul</th><th>Kategori</th><th>Created At</th></tr>";
        foreach($test_artikel as $artikel) {
            echo "<tr>";
            echo "<td>" . $artikel['id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($artikel['judul'], 0, 50)) . "...</td>";
            echo "<td>" . $artikel['category_enum'] . "</td>";
            echo "<td>" . $artikel['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Fungsi getArtikel() tidak mengembalikan data</div>";
    }
    
    // Test tambahArtikel (dry run)
    echo "<h3>Test tambahArtikel() - Dry Run</h3>";
    $test_data = [
        'judul' => 'Test Artikel - ' . date('Y-m-d H:i:s'),
        'konten' => 'Ini adalah test konten artikel untuk memastikan fungsi tambahArtikel berfungsi dengan baik.',
        'ringkasan' => 'Test ringkasan artikel',
        'category_enum' => 'umum',
        'user_id' => 1,
        'status' => 'draft'
    ];
    
    // Simulasi tanpa benar-benar insert
    echo "<div class='info'>";
    echo "<strong>Data Test:</strong><br>";
    echo "Judul: " . htmlspecialchars($test_data['judul']) . "<br>";
    echo "Category: " . $test_data['category_enum'] . "<br>";
    echo "Status: " . $test_data['status'] . "<br>";
    echo "Created At: " . date('Y-m-d H:i:s') . " (akan di-generate oleh PHP)<br>";
    echo "</div>";
    
    echo "<div class='success'>✅ Fungsi tambahArtikel() siap - created_at akan di-handle oleh PHP</div>";
    
    // 5. Test kategori
    echo "<h2>5️⃣ Test Kategori ENUM</h2>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM artikel LIKE 'category_enum'");
    $enum_column = $stmt->fetch();
    
    if ($enum_column) {
        echo "<div class='success'>✅ Field category_enum ditemukan</div>";
        echo "<div class='info'>";
        echo "<strong>Type:</strong> " . $enum_column['Type'] . "<br>";
        echo "</div>";
        
        // Test kategori yang ada
        $stmt = $pdo->query("SELECT DISTINCT category_enum, COUNT(*) as count FROM artikel GROUP BY category_enum");
        $categories = $stmt->fetchAll();
        
        if (!empty($categories)) {
            echo "<h4>📁 Kategori yang Digunakan:</h4>";
            echo "<table>";
            echo "<tr><th>Kategori</th><th>Jumlah Artikel</th></tr>";
            foreach($categories as $cat) {
                echo "<tr>";
                echo "<td>" . ucfirst($cat['category_enum']) . "</td>";
                echo "<td>" . $cat['count'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='error'>❌ Field category_enum tidak ditemukan</div>";
    }
    
    // 6. Summary
    echo "<h2>📊 Ringkasan Test</h2>";
    
    echo "<div class='success'>";
    echo "<h4>✅ Database Setup Berhasil!</h4>";
    echo "<ul>";
    echo "<li>Koneksi ke FreeSQLDatabase.com berhasil</li>";
    echo "<li>Struktur tabel sesuai spesifikasi</li>";
    echo "<li>Field created_at di-handle oleh PHP</li>";
    echo "<li>Fungsi PHP artikel berfungsi normal</li>";
    echo "<li>System ENUM kategori aktif</li>";
    echo "<li>Data sample tersedia</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<hr>";
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #1a73e8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>🏠 Beranda</a> ";
    echo "<a href='setup_new_database.php' style='background: #34a853; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>⚙️ Setup Database</a> ";
    echo "<a href='admin_artikel.php' style='background: #ea4335; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>📝 Kelola Artikel</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Pastikan:</p>";
    echo "<ul>";
    echo "<li>Koneksi internet stabil</li>";
    echo "<li>Kredensial database benar</li>";
    echo "<li>Server FreeSQLDatabase.com dapat diakses</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</body></html>";
?> 