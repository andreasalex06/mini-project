<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions_enum.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();

// Parameters untuk filtering
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'all';
$kategori_filter = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get articles based on user role (user can only see their own articles)
try {
    $artikel_list = getArtikelByUser($pdo, $current_user['id'], $limit, $offset, $kategori_filter, $status_filter, $search);
    
    // Get total count for pagination using the same function logic
    $total_artikel = getTotalArtikelByUser($pdo, $current_user['id'], $kategori_filter, $status_filter, $search);
    $total_pages = ceil($total_artikel / $limit);
    
} catch(Exception $e) {
    error_log("Error getting articles: " . $e->getMessage());
    $artikel_list = [];
    $total_artikel = 0;
    $total_pages = 0;
}

// Get categories for filter
$kategoris = getKategori($pdo);

// Get statistics for current user
$stats = [];
try {
    $user_id = $current_user['id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'published'");
    $stmt->execute([$user_id]);
    $stats['published'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'draft'");
    $stmt->execute([$user_id]);
    $stats['draft'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'archived'");
    $stmt->execute([$user_id]);
    $stats['archived'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT SUM(views) FROM artikel WHERE user_id = ? AND status = 'published'");
    $stmt->execute([$user_id]);
    $stats['total_views'] = $stmt->fetchColumn() ?: 0;
    
} catch(PDOException $e) {
    $stats = ['published' => 0, 'draft' => 0, 'archived' => 0, 'total_views' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Literaturku</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/admin_artikel.css">
</head>
<body>

<header>
  <div class="logo">
    <h1>Literaturku</h1>
  </div>
  <nav>
    <?php include 'navigasi.php'; ?>
  </nav>
</header>

<main class="admin-main">
    
    <div class="admin-page-container">
        
        <!-- Header -->
        <section class="admin-header grid-area-header">
            <h2>📊 Kelola Artikel</h2>
            <p>Kelola semua artikel Anda dalam satu tempat dengan mudah dan efisien</p>
        </section>

        <!-- Statistics -->
        <section class="grid-area-stats">
            <div class="stats-grid">
                <div class="stat-card published">
                    <div class="stat-icon">🟢</div>
                    <div class="stat-number"><?php echo $stats['published']; ?></div>
                    <div class="stat-label">Artikel Publikasi</div>
                </div>
                <div class="stat-card draft">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?php echo $stats['draft']; ?></div>
                    <div class="stat-label">Draft</div>
                </div>
                <div class="stat-card archived">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $stats['archived']; ?></div>
                    <div class="stat-label">Arsip</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-number"><?php echo number_format($stats['total_views']); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>
        </section>
        
        <!-- Filters -->
        <section class="filters-section grid-area-filters">
            <div class="filters-header">
                <h3>🔍 Filter & Pencarian</h3>
                <a href="tambah_artikel.php" class="btn-new-article">
                    ✏️ Tulis Artikel Baru
                </a>
            </div>
            
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="search">Cari Artikel</label>
                    <input type="text" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Cari judul, konten, atau ringkasan...">
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Publikasi</option>
                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Arsip</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoris as $kategori): ?>
                            <option value="<?php echo $kategori['enum']; ?>" 
                                    <?php echo $kategori_filter == $kategori['enum'] ? 'selected' : ''; ?>>
                                <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-filter">🔍 Filter</button>
            </form>
        </section>
        
        <!-- Articles List -->
        <section class="articles-section grid-area-content">
            <div class="articles-header">
                <h3>📄 Daftar Artikel</h3>
                <div class="results-info">
                    Menampilkan <?php echo count($artikel_list); ?> dari <?php echo $total_artikel; ?> artikel
                </div>
            </div>
            
            <?php if (empty($artikel_list)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h4>Belum Ada Artikel</h4>
                    <p>
                        <?php if ($search || $status_filter !== 'all' || $kategori_filter): ?>
                            Tidak ditemukan artikel dengan filter yang dipilih.
                        <?php else: ?>
                            Anda belum menulis artikel apapun. Mulai dengan menulis artikel pertama Anda!
                        <?php endif; ?>
                    </p>
                    <a href="tambah_artikel.php" class="btn-new-article">
                        ✏️ Tulis Artikel Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="articles-table">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th class="table-hidden-mobile">Views</th>
                                <th class="table-hidden-mobile">Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php foreach ($artikel_list as $artikel): ?>
                            <tr>
                                <td>
                                    <div class="article-title">
                                        <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>">
                                            <?php echo htmlspecialchars($artikel['judul']); ?>
                                        </a>
                                    </div>
                                    <div class="article-summary">
                                        <?php echo htmlspecialchars(truncateText($artikel['ringkasan'], 100)); ?>
                                    </div>
                                    <div class="article-meta">
                                        <div class="article-meta-item">
                                            <span>📅</span>
                                            <span>Dibuat: <?php echo formatTanggal($artikel['created_at']); ?></span>
                                        </div>
                                        <div class="article-meta-item">
                                            <span>🔄</span>
                                            <span>Diupdate: <?php echo formatTanggal($artikel['updated_at']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="kategori-badge" style="background-color: <?php echo $artikel['kategori_warna']; ?>">
                                        <?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $artikel['status']; ?>">
                                        <?php 
                                        switch($artikel['status']) {
                                            case 'published': echo '🟢 Publikasi'; break;
                                            case 'draft': echo '📝 Draft'; break;
                                            case 'archived': echo '📦 Arsip'; break;
                                            default: echo $artikel['status'];
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="table-hidden-mobile">
                                    <strong><?php echo number_format($artikel['views']); ?></strong>
                                </td>
                                <td class="table-hidden-mobile">
                                    <?php echo date('d/m/Y', strtotime($artikel['updated_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" 
                                           class="btn-action btn-view">👁️ Lihat</a>
                                        <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" 
                                           class="btn-action btn-edit">✏️ Edit</a>
                                        <a href="#" onclick="confirmDelete(<?php echo $artikel['id']; ?>, '<?php echo addslashes($artikel['judul']); ?>')" 
                                           class="btn-action btn-delete">🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php
                            $query_params = [];
                            if ($search) $query_params['search'] = $search;
                            if ($status_filter !== 'all') $query_params['status'] = $status_filter;
                            if ($kategori_filter) $query_params['kategori'] = $kategori_filter;
                            ?>
                            
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page-1])); ?>" 
                                   class="pagination-btn">« Sebelumnya</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>" 
                                   class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page+1])); ?>" 
                                   class="pagination-btn">Selanjutnya »</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        
    </div> <!-- End admin-page-container -->

</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>

<script>
// Konfirmasi hapus artikel dengan double confirmation
function confirmDelete(id, title) {
    const messages = {
        first: `⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus artikel "${title}"?\n\nTindakan ini tidak dapat dibatalkan.`,
        second: 'Konfirmasi sekali lagi untuk menghapus artikel secara permanen.'
    };
    
    if (confirm(messages.first) && confirm(messages.second)) {
        window.location.href = `hapus_artikel.php?id=${id}`;
    }
}

// Auto-submit form saat filter berubah
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const kategoriSelect = document.getElementById('kategori');
    const searchInput = document.getElementById('search');
    
    function hasActiveFilters() {
        return statusSelect.value !== 'all' || kategoriSelect.value || searchInput.value;
    }
    
    function submitForm() {
        if (hasActiveFilters()) {
            statusSelect.form.submit();
        }
    }
    
    statusSelect.addEventListener('change', submitForm);
    kategoriSelect.addEventListener('change', submitForm);
});
</script>

</body>
</html> 