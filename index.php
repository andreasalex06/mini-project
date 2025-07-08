<?php
require_once 'koneksi.php';

$title = 'Beranda - Blog Sederhana';

// Ambil parameter filter kategori dan search
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Build query berdasarkan filter
$where_clause = "WHERE a.status = 'published'";
$params = [];

if ($kategori_filter > 0) {
    $where_clause .= " AND a.kategori_id = ?";
    $params[] = $kategori_filter;
}

if (!empty($search_query)) {
    $where_clause .= " AND (a.judul LIKE ? OR a.konten LIKE ? OR a.ringkasan LIKE ? OR a.tags LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Hitung total artikel untuk pagination
$sql_count = "SELECT COUNT(*) as total 
              FROM artikels a 
              JOIN kategoris k ON a.kategori_id = k.id 
              JOIN users u ON a.user_id = u.id 
              $where_clause";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_articles = $stmt_count->fetch()['total'];
$total_pages = ceil($total_articles / $per_page);

// Ambil artikel berdasarkan filter dan pagination
$sql = "SELECT a.*, k.nama_kategori, k.color, k.icon, u.username, u.nama_lengkap 
        FROM artikels a 
        JOIN kategoris k ON a.kategori_id = k.id 
        JOIN users u ON a.user_id = u.id 
        $where_clause 
        ORDER BY a.created_at DESC 
        LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Ambil kategori untuk filter
$sql = "SELECT k.*, COUNT(a.id) as artikel_count 
        FROM kategoris k 
        LEFT JOIN artikels a ON k.id = a.kategori_id AND a.status = 'published'
        GROUP BY k.id 
        ORDER BY k.nama_kategori";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

// Ambil info kategori yang dipilih
$selected_kategori = null;
if ($kategori_filter > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $kategori_filter) {
            $selected_kategori = $cat;
            $title = 'Kategori: ' . $cat['nama_kategori'] . ' - Blog Sederhana';
            break;
        }
    }
}

// Update title jika ada pencarian
if (!empty($search_query)) {
    $title = 'Pencarian: ' . $search_query . ' - Blog Sederhana';
}

include 'header.php';
?>

<!-- Hero Section -->
<div class="jumbotron bg-primary text-white text-center py-5 mb-4 rounded">
    <h1 class="display-4">📝 Literaturku</h1>
    <p class="lead">Tempat berbagi artikel dan informasi menarik</p>
    <p>Tanpa iklan, tanpa spam, tanpa pemblokir</p>
</div>

<!-- Search Box -->
<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
                 <form method="GET" action="index.php">
            <div class="input-group">
                <input type="text" class="form-control" name="search" 
                       placeholder="Cari artikel berdasarkan judul, konten, atau tags..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <?php if ($kategori_filter > 0): ?>
                    <input type="hidden" name="kategori" value="<?php echo $kategori_filter; ?>">
                <?php endif; ?>
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <?php if (!empty($search_query) || $kategori_filter > 0): ?>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Filter Kategori -->
<div class="row mb-4">
    <div class="col-md-12">
        <h5>📂 Filter berdasarkan Kategori:</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" 
               class="btn btn-sm <?php echo ($kategori_filter == 0) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-house"></i> Semua
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="index.php?kategori=<?php echo $category['id']; ?>" 
                   class="btn btn-sm <?php echo ($kategori_filter == $category['id']) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                   style="<?php echo ($kategori_filter == $category['id']) ? 'background-color: ' . $category['color'] . '; border-color: ' . $category['color'] . ';' : 'border-color: ' . $category['color'] . '; color: ' . $category['color'] . ';'; ?>">
                    <i class="<?php echo $category['icon']; ?>"></i>
                    <?php echo $category['nama_kategori']; ?>
                    <span class="badge bg-light text-dark ms-1"><?php echo $category['artikel_count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Header Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <?php if (!empty($search_query)): ?>
            <div class="card border-info">
                <div class="card-body text-center py-4">
                    <h2 class="text-info">
                        <i class="bi bi-search"></i>
                        Hasil Pencarian: "<?php echo htmlspecialchars($search_query); ?>"
                    </h2>
                    <p class="text-muted">
                        <i class="bi bi-file-text"></i> 
                        Ditemukan <?php echo $total_articles; ?> artikel
                        <?php if ($selected_kategori): ?>
                            di kategori <strong><?php echo htmlspecialchars($selected_kategori['nama_kategori']); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php elseif ($selected_kategori): ?>
            <div class="card" style="background: linear-gradient(135deg, <?php echo $selected_kategori['color']; ?>22, <?php echo $selected_kategori['color']; ?>11);">
                <div class="card-body text-center py-4">
                    <h2 style="color: <?php echo $selected_kategori['color']; ?>;">
                        <i class="<?php echo $selected_kategori['icon']; ?>"></i>
                        <?php echo htmlspecialchars($selected_kategori['nama_kategori']); ?>
                    </h2>
                    <?php if ($selected_kategori['deskripsi']): ?>
                        <p class="lead text-muted"><?php echo htmlspecialchars($selected_kategori['deskripsi']); ?></p>
                    <?php endif; ?>
                    <p class="text-muted">
                        <i class="bi bi-file-text"></i> 
                        <?php echo $selected_kategori['artikel_count']; ?> artikel tersedia
                    </p>
                </div>
            </div>
        <?php else: ?>
            <h2>📰 Semua Artikel</h2>
            <p class="text-muted">
                <i class="bi bi-file-text"></i> 
                Menampilkan <?php echo $total_articles; ?> artikel terbaru
            </p>
        <?php endif; ?>
        <hr>
    </div>
</div>

<?php if (empty($articles)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info text-center">
                <h5>📝 Belum ada artikel</h5>
                <?php if (!empty($search_query)): ?>
                    <p>Tidak ditemukan artikel dengan kata kunci "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php if ($selected_kategori): ?>
                        di kategori <strong><?php echo htmlspecialchars($selected_kategori['nama_kategori']); ?></strong>
                    <?php endif; ?>.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php" class="btn btn-primary">Reset Pencarian</a>
                        <?php if ($selected_kategori): ?>
                            <a href="index.php?kategori=<?php echo $kategori_filter; ?>" class="btn btn-outline-primary">
                                Lihat Semua di <?php echo htmlspecialchars($selected_kategori['nama_kategori']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php elseif ($selected_kategori): ?>
                    <p>Belum ada artikel yang dipublikasikan di kategori <strong><?php echo htmlspecialchars($selected_kategori['nama_kategori']); ?></strong>.</p>
                    <a href="index.php" class="btn btn-primary">Lihat Semua Artikel</a>
                <?php else: ?>
                    <p>Belum ada artikel yang dipublikasikan. Silakan login sebagai admin untuk menambah artikel.</p>
                    <a href="login.php" class="btn btn-primary">Login Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($articles as $article): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($article['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($article['judul']); ?>" 
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge" style="background-color: <?php echo $article['color']; ?>;">
                                <i class="<?php echo $article['icon']; ?>"></i>
                                <?php echo htmlspecialchars($article['nama_kategori']); ?>
                            </span>
                        </div>
                        
                        <h5 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h5>
                        
                        <?php if ($article['ringkasan']): ?>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($article['ringkasan']); ?>
                            </p>
                        <?php else: ?>
                            <p class="card-text text-muted">
                                <?php echo substr(strip_tags($article['konten']), 0, 100) . '...'; ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> 
                                <?php echo htmlspecialchars($article['nama_lengkap']); ?>
                                <br>
                                <i class="bi bi-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                                • 
                                <i class="bi bi-eye"></i> 
                                <?php echo $article['views']; ?> views
                            </small>
                            <div class="mt-2">
                                <a href="artikel_detail.php?id=<?php echo $article['id']; ?>" class="btn btn-primary btn-sm">
                                    Baca Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <nav aria-label="Pagination">
                    <ul class="pagination justify-content-center">
                        <!-- Previous -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?<?php 
                                    $url_params = [];
                                    if ($kategori_filter > 0) $url_params[] = 'kategori=' . $kategori_filter;
                                    if (!empty($search_query)) $url_params[] = 'search=' . urlencode($search_query);
                                    $url_params[] = 'page=' . ($page - 1);
                                    echo implode('&', $url_params);
                                ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?<?php 
                                    $url_params = [];
                                    if ($kategori_filter > 0) $url_params[] = 'kategori=' . $kategori_filter;
                                    if (!empty($search_query)) $url_params[] = 'search=' . urlencode($search_query);
                                    $url_params[] = 'page=' . $i;
                                    echo implode('&', $url_params);
                                ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?<?php 
                                    $url_params = [];
                                    if ($kategori_filter > 0) $url_params[] = 'kategori=' . $kategori_filter;
                                    if (!empty($search_query)) $url_params[] = 'search=' . urlencode($search_query);
                                    $url_params[] = 'page=' . ($page + 1);
                                    echo implode('&', $url_params);
                                ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
</nav>

                <!-- Info pagination -->
                <div class="text-center text-muted mt-2">
                    <small>
                        Menampilkan <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total_articles); ?> 
                        dari <?php echo $total_articles; ?> artikel
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Statistik -->
<div class="row mt-5">
    <div class="col-md-12">
        <h4>📊 Statistik Blog</h4>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">📄 Total Artikel</h5>
                <?php
                $sql = "SELECT COUNT(*) as total FROM artikels WHERE status = 'published'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $total_articles = $stmt->fetch()['total'];
                ?>
                <h3 class="text-primary"><?php echo $total_articles; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">📂 Total Kategori</h5>
                <?php
                $sql = "SELECT COUNT(*) as total FROM kategoris";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $total_categories = $stmt->fetch()['total'];
                ?>
                <h3 class="text-success"><?php echo $total_categories; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">👥 Total User</h5>
                <?php
                $sql = "SELECT COUNT(*) as total FROM users";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $total_users = $stmt->fetch()['total'];
                ?>
                <h3 class="text-info"><?php echo $total_users; ?></h3>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 