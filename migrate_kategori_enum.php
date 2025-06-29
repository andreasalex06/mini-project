<?php
/**
 * MIGRATION SCRIPT: Kategori to ENUM
 * Mengubah struktur kategori dari foreign key ke ENUM
 */

require_once 'koneksi.php';

try {
    echo "<h2>🔄 Migrasi Kategori ke ENUM</h2>";
    echo "<p>Mengubah struktur database untuk menggunakan ENUM kategori...</p>";
    
    // 1. Backup data kategori existing
    echo "<h3>📋 Step 1: Backup Data Kategori</h3>";
    $backup_data = [];
    
    try {
        $stmt = $pdo->query("SELECT a.id, a.kategori_id, k.nama as kategori_nama 
                            FROM artikel a 
                            LEFT JOIN kategori k ON a.kategori_id = k.id");
        $backup_data = $stmt->fetchAll();
        echo "✅ Backup " . count($backup_data) . " artikel dengan kategori<br>";
    } catch(PDOException $e) {
        echo "⚠️ Tidak ada data untuk di-backup: " . $e->getMessage() . "<br>";
    }
    
    // 2. Mapping kategori ID ke ENUM values
    echo "<h3>🗺️ Step 2: Mapping Kategori</h3>";
    $kategori_mapping = [
        1 => 'teknologi',
        2 => 'pendidikan', 
        3 => 'bisnis',
        4 => 'kesehatan',
        5 => 'sains',
        6 => 'lifestyle',
        7 => 'olahraga',
        8 => 'hiburan'
    ];
    
    foreach ($kategori_mapping as $id => $enum) {
        echo "ID $id → $enum<br>";
    }
    
    // 3. Alter table artikel - tambah kolom category_enum
    echo "<h3>🔧 Step 3: Alter Table Artikel</h3>";
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM artikel LIKE 'category_enum'");
    if ($stmt->rowCount() == 0) {
        $sql_alter = "ALTER TABLE artikel 
                     ADD COLUMN category_enum ENUM('teknologi', 'pendidikan', 'bisnis', 'kesehatan', 'sains', 'lifestyle', 'olahraga', 'hiburan', 'umum') 
                     DEFAULT 'umum' AFTER kategori_id";
        
        $pdo->exec($sql_alter);
        echo "✅ Kolom category_enum berhasil ditambahkan<br>";
    } else {
        echo "✅ Kolom category_enum sudah ada<br>";
    }
    
    // 4. Migrate data from kategori_id to category_enum
    echo "<h3>📊 Step 4: Migrasi Data</h3>";
    
    foreach ($backup_data as $data) {
        $kategori_id = $data['kategori_id'];
        $enum_value = 'umum'; // default
        
        if (isset($kategori_mapping[$kategori_id])) {
            $enum_value = $kategori_mapping[$kategori_id];
        }
        
        $stmt = $pdo->prepare("UPDATE artikel SET category_enum = ? WHERE id = ?");
        $stmt->execute([$enum_value, $data['id']]);
    }
    
    echo "✅ Data berhasil dimigrasikan ke format ENUM<br>";
    
    // 5. Create new kategori definitions as constants
    echo "<h3>📝 Step 5: Definisi Kategori Baru</h3>";
    
    $kategori_definitions = [
        'teknologi' => ['name' => 'Teknologi', 'color' => '#667eea', 'icon' => '💻'],
        'pendidikan' => ['name' => 'Pendidikan', 'color' => '#2ecc71', 'icon' => '📚'],
        'bisnis' => ['name' => 'Bisnis', 'color' => '#e74c3c', 'icon' => '💼'],
        'kesehatan' => ['name' => 'Kesehatan', 'color' => '#f39c12', 'icon' => '🏥'],
        'sains' => ['name' => 'Sains', 'color' => '#9b59b6', 'icon' => '🔬'],
        'lifestyle' => ['name' => 'Lifestyle', 'color' => '#1abc9c', 'icon' => '🌟'],
        'olahraga' => ['name' => 'Olahraga', 'color' => '#e67e22', 'icon' => '⚽'],
        'hiburan' => ['name' => 'Hiburan', 'color' => '#ff6b9d', 'icon' => '🎬'],
        'umum' => ['name' => 'Umum', 'color' => '#95a5a6', 'icon' => '📄']
    ];
    
    // Save definitions to a PHP file
    $definitions_php = "<?php\n";
    $definitions_php .= "/**\n * KATEGORI DEFINITIONS\n * Auto-generated category definitions\n */\n\n";
    $definitions_php .= "define('KATEGORI_DEFINITIONS', " . var_export($kategori_definitions, true) . ");\n\n";
    $definitions_php .= "function getKategoriInfo(\$category_enum) {\n";
    $definitions_php .= "    \$definitions = KATEGORI_DEFINITIONS;\n";
    $definitions_php .= "    return \$definitions[\$category_enum] ?? \$definitions['umum'];\n";
    $definitions_php .= "}\n\n";
    $definitions_php .= "function getAllKategori() {\n";
    $definitions_php .= "    return KATEGORI_DEFINITIONS;\n";
    $definitions_php .= "}\n";
    
    file_put_contents('kategori_definitions.php', $definitions_php);
    echo "✅ File kategori_definitions.php berhasil dibuat<br>";
    
    // 6. Show statistics
    echo "<h3>📈 Step 6: Statistik Migrasi</h3>";
    
    $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total FROM artikel GROUP BY category_enum ORDER BY total DESC");
    $stats = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Kategori</th><th>Jumlah Artikel</th><th>Persentase</th></tr>";
    
    $total_articles = array_sum(array_column($stats, 'total'));
    
    foreach ($stats as $stat) {
        $percentage = $total_articles > 0 ? round(($stat['total'] / $total_articles) * 100, 1) : 0;
        $kategori_info = $kategori_definitions[$stat['category_enum']];
        
        echo "<tr>";
        echo "<td>" . $kategori_info['icon'] . " " . $kategori_info['name'] . "</td>";
        echo "<td>" . $stat['total'] . "</td>";
        echo "<td>" . $percentage . "%</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 7. Optional: Drop old kategori table (commented for safety)
    echo "<h3>🗑️ Step 7: Cleanup (Optional)</h3>";
    echo "<p><strong>PERHATIAN:</strong> Tabel kategori lama masih dipertahankan untuk safety.</p>";
    echo "<p>Jika migrasi sukses dan tidak ada masalah, Anda bisa menjalankan:</p>";
    echo "<code>-- DROP TABLE kategori;</code><br>";
    echo "<code>-- ALTER TABLE artikel DROP COLUMN kategori_id;</code><br>";
    
    echo "<br><h3>🎉 Migrasi Selesai!</h3>";
    echo "<p>✅ Struktur kategori berhasil diubah ke ENUM</p>";
    echo "<p>✅ Performa query akan lebih cepat</p>";
    echo "<p>✅ Data lebih konsisten</p>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #10367d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>← Kembali ke Beranda</a>";
    echo "<a href='admin_artikel.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Kelola Artikel</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3>❌ Error Migrasi:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p>Migrasi dibatalkan untuk keamanan data.</p>";
}
?> 