<?php
require_once 'koneksi.php';
require_once 'artikel_functions_enum.php';

// Parameters untuk filtering dan searching
$category_enum = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Ambil data artikel dan kategori
$artikel_list = getArtikel($pdo, $limit, $offset, $category_enum, $search);
$total_artikel = getTotalArtikel($pdo, $category_enum, $search);
$total_pages = ceil($total_artikel / $limit);
$kategoris = getKategori($pdo);
$artikel_populer = getArtikelPopuler($pdo, 3);
?>

<!-- Page Grid Container -->
<div class="page-grid-container">
    
    <!-- Kategori Section -->
    <section class="kategori-section grid-area-kategori">
        <div class="section-container">
            <?php include 'kategori.php'; ?>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero-section grid-area-hero">
        <div class="section-container">
            <div class="hero-content">
                <div class="hero-text">
                    <h2>Selamat Datang di Literaturku</h2>
                    <p>Perbanyak literasimu dan bagikan pengetahuanmu kepada dunia.</p>
                </div>
                
                <!-- Search Form -->
                <div class="search-container">
                    <form method="GET" class="search-form">
                        <div class="search-input-group">
                            <input type="text" name="search" placeholder="Cari artikel..." 
                                   value="<?php echo htmlspecialchars($search ?? ''); ?>" class="search-input">
                            <button type="submit" class="search-btn">🔍</button>
                        </div>
                        
                        <div class="filter-group">
                            <select name="kategori" class="kategori-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoris as $kat): ?>
                                    <option value="<?php echo $kat['enum']; ?>" 
                                            <?php echo $category_enum == $kat['enum'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kat['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php if (is_logged_in()): ?>
                                <a href="tambah_artikel.php" class="btn-tambah">✏️ Tulis Artikel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Stats Info -->
                <div class="stats-info">
                    <div class="stat-item">
                        <span class="stat-icon">📚</span>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $total_artikel; ?></span>
                            <span class="stat-label">Artikel</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">📁</span>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo count($kategoris); ?></span>
                            <span class="stat-label">Kategori</span>
                        </div>
                    </div>
                    <?php if (is_logged_in()): ?>
                        <div class="stat-item">
                            <span class="stat-icon">👤</span>
                            <div class="stat-content">
                                <span class="stat-number">Halo!</span>
                                <span class="stat-label"><?php echo htmlspecialchars(get_logged_in_user()['username'] ?? 'User'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <div class="main-content-grid grid-area-content">
    
        <!-- Artikel Section -->
        <section class="artikel-section grid-area-articles">
            <div class="section-container">
                <div class="section-header">
                    <div class="header-content">
                        <h3>
                            <?php if ($search): ?>
                                <span class="header-icon">🔍</span>
                                Hasil Pencarian: "<span class="search-term"><?php echo htmlspecialchars($search); ?></span>"
                            <?php elseif ($category_enum): ?>
                                <span class="header-icon">📁</span>
                                Kategori: <span class="category-name"><?php 
                                    $selected_kategori = array_filter($kategoris, function($k) use($category_enum) { 
                                        return $k['enum'] == $category_enum; 
                                    });
                                    echo htmlspecialchars(reset($selected_kategori)['nama']);
                                ?></span>
                            <?php else: ?>
                                <span class="header-icon">📝</span>
                                Artikel Terbaru
                            <?php endif; ?>
                        </h3>
                        <div class="header-meta">
                            <span class="artikel-count">Menampilkan <?php echo count($artikel_list); ?> dari <?php echo $total_artikel; ?> artikel</span>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <?php if ($search || $category_enum): ?>
                            <a href="index.php" class="btn-reset">
                                <span class="btn-icon">❌</span>
                                <span class="btn-text">Hapus Filter</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (is_logged_in()): ?>
                            <a href="admin_artikel.php" class="btn-manage">
                                <span class="btn-icon">⚙️</span>
                                <span class="btn-text hidden-mobile">Kelola Artikel</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
        
                <!-- Article Content -->
                <div class="artikel-content">
                    <?php if (empty($artikel_list)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <div class="empty-content">
                                <h4>Belum Ada Artikel</h4>
                                <p>
                                    <?php if ($search): ?>
                                        Tidak ditemukan artikel dengan kata kunci "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                    <?php elseif ($category_enum): ?>
                                        Belum ada artikel dalam kategori ini.
                                    <?php else: ?>
                                        Belum ada artikel yang dipublikasikan.
                                    <?php endif; ?>
                                </p>
                                <?php if (is_logged_in()): ?>
                                    <a href="tambah_artikel.php" class="btn btn-primary">
                                        <span class="btn-icon">✏️</span>
                                        <span class="btn-text">Tulis Artikel Pertama</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="artikel-grid">
                            <?php foreach ($artikel_list as $artikel): ?>
                                <article class="artikel-card" data-kategori="<?php echo $artikel['category_enum'] ?? 'umum'; ?>">
                                    <div class="artikel-kategori" style="--kategori-color: <?php echo $artikel['kategori_warna']; ?>">
                                        <span class="kategori-icon"><?php echo $artikel['kategori_icon'] ?? '📄'; ?></span>
                                        <span class="kategori-nama"><?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?></span>
                                    </div>
                                    
                                    <div class="artikel-body">
                                        <h4 class="artikel-title">
                                            <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>">
                                                <?php echo htmlspecialchars($artikel['judul']); ?>
                                            </a>
                                        </h4>
                                        
                                        <p class="artikel-ringkasan">
                                            <?php echo htmlspecialchars(truncateText($artikel['ringkasan'], 120)); ?>
                                        </p>
                                        
                                        <div class="artikel-meta">
                                            <div class="meta-info">
                                                <span class="meta-item meta-author">
                                                    <span class="meta-icon">👤</span>
                                                    <span class="meta-text"><?php echo htmlspecialchars($artikel['penulis'] ?? 'Anonymous'); ?></span>
                                                </span>
                                                <span class="meta-item meta-date">
                                                    <span class="meta-icon">📅</span>
                                                    <span class="meta-text"><?php echo formatTanggal($artikel['created_at']); ?></span>
                                                </span>
                                                <span class="meta-item meta-views">
                                                    <span class="meta-icon">👁️</span>
                                                    <span class="meta-text"><?php echo number_format($artikel['views']); ?></span>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="artikel-actions">
                                            <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" class="btn btn-outline btn-read">
                                                <span class="btn-text">Baca Selengkapnya</span>
                                                <span class="btn-icon">→</span>
                                            </a>
                                            
                                            <?php if (is_logged_in() && get_logged_in_user()['id'] == $artikel['user_id']): ?>
                                                <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" class="btn btn-sm btn-secondary btn-edit" title="Edit Artikel">
                                                    <span class="btn-icon">✏️</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-container">
                                <nav class="pagination" aria-label="Navigasi halaman artikel">
                                    <?php
                                    $query_params = [];
                                    if ($search) $query_params['search'] = $search;
                                    if ($category_enum) $query_params['kategori'] = $category_enum;
                                    ?>
                                    
                                    <?php if ($page > 1): ?>
                                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page-1])); ?>" 
                                           class="pagination-btn pagination-prev" aria-label="Halaman sebelumnya">
                                            <span class="btn-icon">«</span>
                                            <span class="btn-text hidden-mobile">Sebelumnya</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div class="pagination-numbers">
                                        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>" 
                                               class="pagination-btn pagination-number <?php echo $i == $page ? 'active' : ''; ?>"
                                               aria-label="Halaman <?php echo $i; ?>"
                                               <?php echo $i == $page ? 'aria-current="page"' : ''; ?>>
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page+1])); ?>" 
                                           class="pagination-btn pagination-next" aria-label="Halaman selanjutnya">
                                            <span class="btn-text hidden-mobile">Selanjutnya</span>
                                            <span class="btn-icon">»</span>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                                
                                <div class="pagination-info">
                                    <span class="pagination-text">
                                        Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
                                        (<?php echo $total_artikel; ?> total artikel)
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Sidebar -->
        <?php if (!empty($artikel_populer)): ?>
            <aside class="sidebar-section grid-area-sidebar">
                <div class="section-container">
                    <div class="sidebar-widget widget-populer">
                        <div class="widget-header">
                            <h4>
                                <span class="widget-icon">🔥</span>
                                <span class="widget-title">Artikel Populer</span>
                            </h4>
                            <div class="widget-meta">
                                <span class="widget-count"><?php echo count($artikel_populer); ?> artikel</span>
                            </div>
                        </div>
                        
                        <div class="populer-list">
                            <?php foreach ($artikel_populer as $index => $populer): ?>
                                <div class="populer-item" data-rank="<?php echo $index + 1; ?>">
                                    <div class="populer-rank">
                                        <span class="rank-number"><?php echo $index + 1; ?></span>
                                    </div>
                                    
                                    <div class="populer-content">
                                        <h5 class="populer-title">
                                            <a href="artikel_detail.php?id=<?php echo $populer['id']; ?>">
                                                <?php echo htmlspecialchars(truncateText($populer['judul'], 60)); ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="populer-meta">
                                            <span class="populer-views">
                                                <span class="meta-icon">👁️</span>
                                                <span class="meta-text"><?php echo number_format($populer['views']); ?></span>
                                            </span>
                                            <span class="populer-kategori" style="--kategori-color: <?php echo $populer['kategori_warna']; ?>">
                                                <span class="kategori-dot"></span>
                                                <span class="kategori-text"><?php echo htmlspecialchars($populer['kategori_nama'] ?? 'Umum'); ?></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Additional Sidebar Widget jika diperlukan -->
                    <?php if (is_logged_in()): ?>
                        <div class="sidebar-widget widget-user-actions">
                            <div class="widget-header">
                                <h4>
                                    <span class="widget-icon">⚙️</span>
                                    <span class="widget-title">Aksi Cepat</span>
                                </h4>
                            </div>
                            
                            <div class="quick-actions">
                                <a href="tambah_artikel.php" class="action-btn action-create">
                                    <span class="action-icon">✏️</span>
                                    <span class="action-text">Tulis Artikel Baru</span>
                                </a>
                                <a href="admin_artikel.php" class="action-btn action-manage">
                                    <span class="action-icon">📝</span>
                                    <span class="action-text">Kelola Artikel Saya</span>
                                </a>
                                <a href="profile.php" class="action-btn action-profile">
                                    <span class="action-icon">👤</span>
                                    <span class="action-text">Edit Profil</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        <?php endif; ?>
        
    </div> <!-- End main-content-grid -->
    
</div> <!-- End page-grid-container -->
