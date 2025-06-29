<?php
// Get categories from ENUM definitions
require_once 'kategori_definitions.php';
if (!isset($kategoris)) {
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
                    <span class="kategori-label"><?php echo $kategori['count'] == 1 ? 'artikel' : 'artikel'; ?></span>
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

<style>
.kategori-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    position: relative;
}

.kategori-header::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(135deg, #a5ce00 0%, #67b26f 100%);
    border-radius: 2px;
}

.kategori-header h3 {
    color: #ffffff;
    font-size: 1.4rem;
    margin-bottom: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.kategori-header p {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
    margin: 0;
    font-style: italic;
}

.kategori-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.kategori-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    text-decoration: none;
    color: #ffffff;
    transition: all 0.4s ease;
    border-left: 4px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(15px);
    position: relative;
    overflow: hidden;
}

.kategori-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.kategori-item:hover::before {
    opacity: 1;
}

.kategori-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-4px) translateX(8px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    border-left-color: var(--kategori-color, #a5ce00);
}

.kategori-item.active {
    background: rgba(255, 255, 255, 0.25);
    border-left-color: #a5ce00;
    box-shadow: 0 6px 25px rgba(165, 206, 0, 0.3);
    transform: translateX(8px);
}

.kategori-item.active .kategori-icon {
    color: #a5ce00 !important;
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.kategori-icon {
    font-size: 1.8rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.kategori-item:hover .kategori-icon {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.kategori-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.kategori-nama {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
}

.kategori-desc {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.3;
}

.kategori-stats {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    min-width: 50px;
}

.kategori-count {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    min-width: 35px;
    text-align: center;
    line-height: 1;
}

.kategori-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kategori-item.active .kategori-count {
    background: #a5ce00;
    color: #ffffff;
    box-shadow: 0 2px 10px rgba(165, 206, 0, 0.3);
}

.kategori-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.1);
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--kategori-color, #a5ce00);
    transition: width 0.6s ease;
    border-radius: 0 2px 2px 0;
}

.kategori-empty {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 2.5rem 1.5rem;
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 2px dashed rgba(255, 255, 255, 0.2);
}

.empty-icon {
    font-size: 3rem;
    opacity: 0.8;
    animation: float 3s ease-in-out infinite;
}

.empty-content h4 {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.empty-content p {
    font-size: 0.85rem;
    margin: 0;
    line-height: 1.4;
}

.kategori-summary {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    margin-top: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.summary-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
}

.summary-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.summary-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .kategori-header h3 {
        font-size: 1.2rem;
    }
    
    .kategori-header p {
        font-size: 0.8rem;
    }
    
    .kategori-item {
        padding: 1rem;
        gap: 0.8rem;
    }
    
    .kategori-icon {
        font-size: 1.4rem;
        width: 35px;
        height: 35px;
    }
    
    .kategori-nama {
        font-size: 0.9rem;
    }
    
    .kategori-desc {
        font-size: 0.75rem;
    }
    
    .kategori-count {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    
    .kategori-label {
        font-size: 0.65rem;
    }
    
    .kategori-summary {
        padding: 1rem;
        gap: 0.5rem;
    }
    
    .summary-number {
        font-size: 1.2rem;
    }
    
    .summary-label {
        font-size: 0.65rem;
    }
    
    .empty-icon {
        font-size: 2.5rem;
    }
    
    .kategori-empty {
        flex-direction: column;
        gap: 1rem;
        padding: 2rem 1rem;
    }
}

@media (max-width: 480px) {
    .kategori-list {
        gap: 0.8rem;
    }
    
    .kategori-item {
        padding: 0.8rem;
    }
    
    .kategori-info {
        gap: 0.2rem;
    }
    
    .kategori-desc {
        display: none; /* Hide description on very small screens */
    }
    
    .summary-item {
        gap: 0.2rem;
    }
}

/* Animation for category loading */
.kategori-item {
    animation: slideInLeft 0.6s ease forwards;
}

.kategori-item:nth-child(1) { animation-delay: 0.1s; }
.kategori-item:nth-child(2) { animation-delay: 0.2s; }
.kategori-item:nth-child(3) { animation-delay: 0.3s; }
.kategori-item:nth-child(4) { animation-delay: 0.4s; }
.kategori-item:nth-child(5) { animation-delay: 0.5s; }
.kategori-item:nth-child(6) { animation-delay: 0.6s; }
.kategori-item:nth-child(7) { animation-delay: 0.7s; }
.kategori-item:nth-child(8) { animation-delay: 0.8s; }
.kategori-item:nth-child(9) { animation-delay: 0.9s; }

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style> 