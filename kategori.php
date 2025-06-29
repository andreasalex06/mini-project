<?php
// Get categories from ENUM definitions
require_once 'kategori_definitions.php';
if (!isset($kategoris)) {
    require_once 'koneksi.php';
    require_once 'artikel_functions_enum.php';
    $kategoris = getKategori($pdo);
}

// Get current category filter
$current_kategori = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : null;

// Get article counts per category
$kategori_stats = [];
if (isset($pdo)) {
    try {
        // Total published articles
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE status = 'published'");
        $total_published = $stmt->fetchColumn();
        
        // Articles per category
        $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total 
                            FROM artikel 
                            WHERE status = 'published' 
                            GROUP BY category_enum");
        $category_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($kategoris as &$kategori) {
            $kategori['count'] = $category_counts[$kategori['enum']] ?? 0;
        }
        
        $kategori_stats['total'] = $total_published;
    } catch(PDOException $e) {
        // Fallback if database error
        foreach ($kategoris as &$kategori) {
            $kategori['count'] = 0;
        }
        $kategori_stats['total'] = 0;
    }
}
?>

<div class="kategori-header">
    <h3>📁 Kategori Artikel</h3>
    <p>Jelajahi artikel berdasarkan kategori</p>
</div>

<div class="kategori-list">
    <!-- Semua Kategori -->
    <a href="index.php" class="kategori-item <?php echo !$current_kategori ? 'active' : ''; ?>" data-category="all">
        <span class="kategori-icon">📚</span>
        <div class="kategori-info">
            <span class="kategori-nama">Semua Artikel</span>
            <span class="kategori-desc">Lihat semua artikel</span>
        </div>
        <div class="kategori-stats">
            <span class="kategori-count"><?php echo $kategori_stats['total'] ?? 0; ?></span>
            <span class="kategori-label">artikel</span>
        </div>
    </a>
    
    <?php if (!empty($kategoris)): ?>
        <?php foreach ($kategoris as $kategori): ?>
            <a href="index.php?kategori=<?php echo urlencode($kategori['enum']); ?>" 
               class="kategori-item <?php echo $current_kategori == $kategori['enum'] ? 'active' : ''; ?>"
               style="--kategori-color: <?php echo $kategori['warna']; ?>"
               data-category="<?php echo htmlspecialchars($kategori['enum']); ?>">
                
                <span class="kategori-icon" style="color: <?php echo $kategori['warna']; ?>">
                    <?php echo $kategori['icon']; ?>
                </span>
                
                <div class="kategori-info">
                    <span class="kategori-nama"><?php echo htmlspecialchars($kategori['nama']); ?></span>
                    <span class="kategori-desc">
                        <?php 
                        // Generate description based on category
                        $descriptions = [
                            'teknologi' => 'Inovasi & perkembangan teknologi',
                            'pendidikan' => 'Pembelajaran & edukasi',
                            'bisnis' => 'Dunia bisnis & ekonomi',
                            'kesehatan' => 'Tips kesehatan & wellness',
                            'sains' => 'Pengetahuan & penelitian',
                            'lifestyle' => 'Gaya hidup & trend',
                            'olahraga' => 'Sports & aktivitas fisik',
                            'hiburan' => 'Entertainment & media',
                            'umum' => 'Artikel umum & beragam'
                        ];
                        echo $descriptions[$kategori['enum']] ?? 'Artikel ' . $kategori['nama'];
                        ?>
                    </span>
                </div>
                
                <div class="kategori-stats">
                    <span class="kategori-count"><?php echo $kategori['count']; ?></span>
                    <span class="kategori-label">artikel</span>
                </div>
                
                <!-- Progress bar showing relative popularity -->
                <?php if ($kategori_stats['total'] > 0): ?>
                    <div class="kategori-progress">
                        <div class="progress-bar" style="width: <?php echo ($kategori['count'] / $kategori_stats['total']) * 100; ?>%; background-color: <?php echo $kategori['warna']; ?>"></div>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="kategori-empty">
            <span class="empty-icon">📭</span>
            <div class="empty-content">
                <h4>Belum ada kategori</h4>
                <p>Kategori akan muncul setelah artikel dipublikasikan</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Category Statistics Summary -->
<div class="kategori-summary">
    <div class="summary-item">
        <span class="summary-number"><?php echo count($kategoris); ?></span>
        <span class="summary-label">Kategori</span>
    </div>
    <div class="summary-item">
        <span class="summary-number"><?php echo $kategori_stats['total'] ?? 0; ?></span>
        <span class="summary-label">Total Artikel</span>
    </div>
    <?php if (!empty($kategoris) && $kategori_stats['total'] > 0): ?>
        <?php 
        // Find most popular category
        $popular_kategori = array_reduce($kategoris, function($max, $cat) {
            return (!$max || $cat['count'] > $max['count']) ? $cat : $max;
        });
        ?>
        <div class="summary-item">
            <span class="summary-number" style="color: <?php echo $popular_kategori['warna']; ?>">
                <?php echo $popular_kategori['icon']; ?>
            </span>
            <span class="summary-label">Terpopuler</span>
        </div>
    <?php endif; ?>
</div>

