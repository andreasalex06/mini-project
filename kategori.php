<?php
require_once 'koneksi.php';

// Ambil ID kategori dari URL
$kategori_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kategori_id <= 0) {
    header('Location: index.php');
    exit();
}

// Ambil info kategori
$sql = "SELECT * FROM kategoris WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$kategori_id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    header('Location: index.php');
    exit();
}

$title = 'Kategori: ' . $kategori['nama_kategori'] . ' - Blog Sederhana';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Hitung total artikel di kategori ini
$sql_count = "SELECT COUNT(*) as total FROM artikels WHERE kategori_id = ? AND status = 'published'";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute([$kategori_id]);
$total_articles = $stmt_count->fetch()['total'];
$total_pages = ceil($total_articles / $per_page);

// Ambil artikel dengan pagination
$sql = "SELECT a.*, u.username, u.nama_lengkap 
        FROM artikels a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.kategori_id = ? AND a.status = 'published' 
        ORDER BY a.created_at DESC 
        LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute([$kategori_id]);
$articles = $stmt->fetchAll();

// Ambil semua kategori untuk navigasi
$sql_all_categories = "SELECT * FROM kategoris ORDER BY nama_kategori";
$stmt_all_categories = $pdo->prepare($sql_all_categories);
$stmt_all_categories->execute();
$all_categories = $stmt_all_categories->fetchAll();

include 'header.php';
?>

<!-- Header Kategori -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card" style="background: linear-gradient(135deg, <?php echo $kategori['color']; ?>22, <?php echo $kategori['color']; ?>11);">
            <div class="card-body text-center py-5">
                <h1 class="display-4" style="color: <?php echo $kategori['color']; ?>;">
                    <i class="<?php echo $kategori['icon']; ?>"></i>
                    <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                </h1>
                <?php if ($kategori['deskripsi']): ?>
                    <p class="lead text-muted"><?php echo htmlspecialchars($kategori['deskripsi']); ?></p>
                <?php endif; ?>
                <p class="text-muted">
                    <i class="bi bi-file-text"></i> 
                    <?php echo $total_articles; ?> artikel tersedia
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Navigasi Kategori -->
<div class="row mb-4">
    <div class="col-md-12">
        <h5>📂 Kategori Lainnya:</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house"></i> Semua Artikel
            </a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="kategori.php?id=<?php echo $cat['id']; ?>" 
                   class="btn btn-sm <?php echo ($cat['id'] == $kategori_id) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                   style="<?php echo ($cat['id'] == $kategori_id) ? 'background-color: ' . $cat['color'] . '; border-color: ' . $cat['color'] . ';' : 'border-color: ' . $cat['color'] . '; color: ' . $cat['color'] . ';'; ?>">
                    <i class="<?php echo $cat['icon']; ?>"></i>
                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Daftar Artikel -->
<div class="row">
    <div class="col-md-12">
        <h2>📰 Artikel di Kategori <?php echo htmlspecialchars($kategori['nama_kategori']); ?></h2>
        <hr>
    </div>
</div>

<?php if (empty($articles)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info text-center">
                <h5>📝 Belum ada artikel</h5>
                <p>Belum ada artikel yang dipublikasikan di kategori ini.</p>
                <a href="index.php" class="btn btn-primary">Lihat Artikel Lainnya</a>
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
                            <span class="badge" style="background-color: <?php echo $kategori['color']; ?>;">
                                <i class="<?php echo $kategori['icon']; ?>"></i>
                                <?php echo $kategori['nama_kategori']; ?>
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
                                <a href="artikel_detail.php?id=<?php echo $article['id']; ?>" 
                                   class="btn btn-primary btn-sm">
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
                                <a class="page-link" href="kategori.php?id=<?php echo $kategori_id; ?>&page=<?php echo $page - 1; ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="kategori.php?id=<?php echo $kategori_id; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="kategori.php?id=<?php echo $kategori_id; ?>&page=<?php echo $page + 1; ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Statistik Kategori -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>📊 Statistik Kategori <?php echo htmlspecialchars($kategori['nama_kategori']); ?></h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary"><?php echo $total_articles; ?></h4>
                        <p>Total Artikel</p>
                    </div>
                    
                    <div class="col-md-3">
                        <?php
                        $sql_views = "SELECT SUM(views) as total_views FROM artikels WHERE kategori_id = ? AND status = 'published'";
                        $stmt_views = $pdo->prepare($sql_views);
                        $stmt_views->execute([$kategori_id]);
                        $total_views = $stmt_views->fetch()['total_views'] ?: 0;
                        ?>
                        <h4 class="text-success"><?php echo $total_views; ?></h4>
                        <p>Total Views</p>
                    </div>
                    
                    <div class="col-md-3">
                        <?php
                        $sql_authors = "SELECT COUNT(DISTINCT user_id) as total_authors FROM artikels WHERE kategori_id = ? AND status = 'published'";
                        $stmt_authors = $pdo->prepare($sql_authors);
                        $stmt_authors->execute([$kategori_id]);
                        $total_authors = $stmt_authors->fetch()['total_authors'] ?: 0;
                        ?>
                        <h4 class="text-info"><?php echo $total_authors; ?></h4>
                        <p>Penulis</p>
                    </div>
                    
                    <div class="col-md-3">
                        <?php
                        $sql_latest = "SELECT created_at FROM artikels WHERE kategori_id = ? AND status = 'published' ORDER BY created_at DESC LIMIT 1";
                        $stmt_latest = $pdo->prepare($sql_latest);
                        $stmt_latest->execute([$kategori_id]);
                        $latest = $stmt_latest->fetch();
                        ?>
                        <h4 class="text-warning">
                            <?php echo $latest ? date('d/m/Y', strtotime($latest['created_at'])) : '-'; ?>
                        </h4>
                        <p>Artikel Terakhir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 