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

<!-- Bootstrap Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">Selamat Datang di Literaturku</h1>
                <p class="lead text-muted mb-5">Perbanyak literasimu dan bagikan pengetahuanmu kepada dunia.</p>
                
                <!-- Search Form Bootstrap -->
                <form method="GET" class="mb-5">
                    <div class="row g-3 justify-content-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control search-box py-3" 
                                       placeholder="Cari artikel..." 
                                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="kategori" class="form-select py-3">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoris as $kat): ?>
                                    <option value="<?php echo $kat['enum']; ?>" 
                                            <?php echo $category_enum == $kat['enum'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kat['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (is_logged_in()): ?>
                        <div class="col-md-3">
                            <a href="tambah_artikel.php" class="btn btn-success w-100 py-3 btn-custom">
                                <i class="bi bi-pencil-square me-2"></i>Tulis Artikel
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Stats Cards Bootstrap -->
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <i class="bi bi-book-fill text-primary fs-1 mb-3"></i>
                            <h3 class="fw-bold text-primary"><?php echo $total_artikel; ?></h3>
                            <p class="text-muted mb-0">Total Artikel</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <i class="bi bi-folder-fill text-success fs-1 mb-3"></i>
                            <h3 class="fw-bold text-success"><?php echo count($kategoris); ?></h3>
                            <p class="text-muted mb-0">Kategori</p>
                        </div>
                    </div>
                    <?php if (is_logged_in()): ?>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <i class="bi bi-person-circle text-info fs-1 mb-3"></i>
                            <h5 class="fw-bold text-info">Halo!</h5>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars(get_logged_in_user()['username'] ?? 'User'); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bootstrap Articles Section -->
<section class="py-5">
    <div class="container">
        <!-- Section Header Bootstrap -->
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2 class="h3 mb-2">
                    <?php if ($search): ?>
                        <i class="bi bi-search text-primary me-2"></i>
                        Hasil Pencarian: "<span class="text-primary"><?php echo htmlspecialchars($search); ?></span>"
                    <?php elseif ($category_enum): ?>
                        <i class="bi bi-folder text-success me-2"></i>
                        Kategori: <span class="text-success"><?php 
                            $selected_kategori = array_filter($kategoris, function($k) use($category_enum) { 
                                return $k['enum'] == $category_enum; 
                            });
                            echo htmlspecialchars(reset($selected_kategori)['nama']);
                        ?></span>
                    <?php else: ?>
                        <i class="bi bi-newspaper text-primary me-2"></i>
                        Artikel Terbaru
                    <?php endif; ?>
                </h2>
                <p class="text-muted small mb-0">
                    <span class="badge bg-light text-dark">
                        Menampilkan <?php echo count($artikel_list); ?> dari <?php echo $total_artikel; ?> artikel
                    </span>
                </p>
            </div>
            
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <?php if ($search || $category_enum): ?>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Hapus Filter
                        </a>
                    <?php endif; ?>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="admin_artikel.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-gear me-1"></i>
                            <span class="d-none d-md-inline">Kelola Artikel</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
                        <!-- Bootstrap Article Cards -->
        <?php if (empty($artikel_list)): ?>
            <!-- Empty State Bootstrap -->
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-4"></i>
                        <h4 class="text-muted">Belum Ada Artikel</h4>
                        <p class="text-muted mb-4">
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
                                <i class="bi bi-pencil-square me-2"></i>
                                Tulis Artikel Pertama
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Bootstrap Article Grid -->
            <div class="row g-4">
                <?php foreach ($artikel_list as $artikel): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <!-- Category Badge -->
                            <div class="card-header border-0 bg-light py-2">
                                <span class="badge category-badge" style="background-color: <?php echo $artikel['kategori_warna'] ?? '#6c757d'; ?>">
                                    <?php echo $artikel['kategori_icon'] ?? '📄'; ?> 
                                    <?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?>
                                </span>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Article Title -->
                                <h5 class="card-title mb-3">
                                    <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($artikel['judul']); ?>
                                    </a>
                                </h5>
                                
                                <!-- Article Summary -->
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(truncateText($artikel['ringkasan'], 120)); ?>
                                </p>
                                
                                <!-- Article Meta -->
                                <div class="mb-3">
                                    <small class="text-muted d-flex flex-wrap gap-3">
                                        <span>
                                            <i class="bi bi-person me-1"></i>
                                            <?php echo htmlspecialchars($artikel['penulis'] ?? 'Anonymous'); ?>
                                        </span>
                                        <span>
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo formatTanggal($artikel['created_at']); ?>
                                        </span>
                                        <span>
                                            <i class="bi bi-eye me-1"></i>
                                            <?php echo number_format($artikel['views']); ?>
                                        </span>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Card Actions -->
                            <div class="card-footer bg-white border-0 pt-0">
                                <div class="d-flex gap-2">
                                    <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" 
                                       class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="bi bi-book me-1"></i>
                                        Baca Selengkapnya
                                    </a>
                                    
                                    <?php if (is_logged_in() && get_logged_in_user()['id'] == $artikel['user_id']): ?>
                                        <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" 
                                           class="btn btn-outline-secondary btn-sm" title="Edit Artikel">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                        
            <!-- Bootstrap Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Navigasi halaman artikel">
                            <ul class="pagination justify-content-center">
                                <?php
                                $query_params = [];
                                if ($search) $query_params['search'] = $search;
                                if ($category_enum) $query_params['kategori'] = $category_enum;
                                ?>
                                
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page-1])); ?>" 
                                           aria-label="Halaman sebelumnya">
                                            <span aria-hidden="true">&laquo;</span>
                                            <span class="d-none d-md-inline ms-1">Sebelumnya</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>"
                                           <?php echo $i == $page ? 'aria-current="page"' : ''; ?>>
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page+1])); ?>" 
                                           aria-label="Halaman selanjutnya">
                                            <span class="d-none d-md-inline me-1">Selanjutnya</span>
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
                                (<?php echo $total_artikel; ?> total artikel)
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Bootstrap Sidebar Section -->
<?php if (!empty($artikel_populer) || is_logged_in()): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <!-- Popular Articles -->
            <?php if (!empty($artikel_populer)): ?>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-fire me-2"></i>
                            Artikel Populer
                            <span class="badge bg-light text-dark ms-2"><?php echo count($artikel_populer); ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($artikel_populer as $index => $populer): ?>
                                <div class="list-group-item list-group-item-action d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <span class="badge bg-danger rounded-pill"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="artikel_detail.php?id=<?php echo $populer['id']; ?>" 
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars(truncateText($populer['judul'], 60)); ?>
                                            </a>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-eye me-1"></i>
                                                <?php echo number_format($populer['views']); ?> views
                                            </small>
                                            <span class="badge" style="background-color: <?php echo $populer['kategori_warna'] ?? '#6c757d'; ?>">
                                                <?php echo htmlspecialchars($populer['kategori_nama'] ?? 'Umum'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quick Actions for Logged In Users -->
            <?php if (is_logged_in()): ?>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="tambah_artikel.php" class="btn btn-success">
                                <i class="bi bi-pencil-square me-2"></i>
                                Tulis Artikel Baru
                            </a>
                            <a href="admin_artikel.php" class="btn btn-outline-primary">
                                <i class="bi bi-newspaper me-2"></i>
                                Kelola Artikel Saya
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="bi bi-person me-2"></i>
                                Edit Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
