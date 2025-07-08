<?php
require_once 'koneksi.php';

// Ambil ID artikel dari URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header('Location: index.php');
    exit();
}

// Ambil artikel dengan JOIN ke tabel kategoris dan users
$sql = "SELECT a.*, k.nama_kategori, k.color, k.icon, u.username, u.nama_lengkap 
        FROM artikels a 
        JOIN kategoris k ON a.kategori_id = k.id 
        JOIN users u ON a.user_id = u.id 
        WHERE a.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit();
}

// Cek apakah artikel published atau milik user yang login
$can_view = false;
if ($article['status'] == 'published') {
    $can_view = true;
} elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['user_id']) {
    $can_view = true;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $can_view = true;
}

if (!$can_view) {
    header('Location: index.php');
    exit();
}

// Update view count hanya jika artikel published
if ($article['status'] == 'published') {
    $sql_update = "UPDATE artikels SET views = views + 1 WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$article_id]);
}

// Set title dan meta
$title = $article['meta_title'] ?: $article['judul'];
$meta_description = $article['meta_description'] ?: substr(strip_tags($article['konten']), 0, 160);

// Ambil artikel terkait dari kategori yang sama
$sql_related = "SELECT id, judul, slug, ringkasan, created_at 
                FROM artikels 
                WHERE kategori_id = ? AND id != ? AND status = 'published' 
                ORDER BY created_at DESC 
                LIMIT 3";
$stmt_related = $pdo->prepare($sql_related);
$stmt_related->execute([$article['kategori_id'], $article_id]);
$related_articles = $stmt_related->fetchAll();

include 'header.php';
?>

<div class="row">
    <div class="col-md-8">
        <!-- Artikel -->
        <article class="card">
            <div class="card-body">
                <!-- Status Draft -->
                <?php if ($article['status'] == 'draft'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Draft</strong> - Artikel ini belum dipublikasikan
                    </div>
                <?php endif; ?>
                
                <!-- Kategori -->
                <div class="mb-3">
                    <span class="badge" style="background-color: <?php echo $article['color']; ?>;">
                        <i class="<?php echo $article['icon']; ?>"></i>
                        <?php echo $article['nama_kategori']; ?>
                    </span>
                </div>
                
                <!-- Judul -->
                <h1 class="mb-3"><?php echo htmlspecialchars($article['judul']); ?></h1>
                
                <!-- Meta Info -->
                <div class="text-muted mb-4">
                    <small>
                        <i class="bi bi-person"></i> 
                        <?php echo htmlspecialchars($article['nama_lengkap']); ?>
                        • 
                        <i class="bi bi-calendar"></i> 
                        <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?>
                        <?php if ($article['status'] == 'published'): ?>
                            • 
                            <i class="bi bi-eye"></i> 
                            <?php echo $article['views']; ?> views
                        <?php endif; ?>
                    </small>
                </div>
                
                <!-- Featured Image -->
                <?php if ($article['featured_image']): ?>
                    <div class="mb-4">
                        <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                             class="img-fluid rounded" 
                             alt="<?php echo htmlspecialchars($article['judul']); ?>">
                    </div>
                <?php endif; ?>
                
                <!-- Ringkasan -->
                <?php if ($article['ringkasan']): ?>
                    <div class="alert alert-info">
                        <strong>Ringkasan:</strong> <?php echo htmlspecialchars($article['ringkasan']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Konten -->
                <div class="content">
                    <?php echo nl2br(htmlspecialchars($article['konten'])); ?>
                </div>
                
                <!-- Tags -->
                <?php if ($article['tags']): ?>
                    <div class="mt-4">
                        <h6>🏷️ Tags:</h6>
                        <?php 
                        $tags = explode(',', $article['tags']);
                        foreach ($tags as $tag): 
                            $tag = trim($tag);
                            if ($tag):
                        ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($tag); ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                            </a>
                            <a href="kategori.php?id=<?php echo $article['kategori_id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-folder"></i> Artikel Lain di <?php echo $article['nama_kategori']; ?>
                            </a>
                        </div>
                        
                        <!-- Edit button jika artikel milik user atau admin -->
                        <?php if (isset($_SESSION['user_id']) && 
                                 ($_SESSION['user_id'] == $article['user_id'] || $_SESSION['role'] == 'admin')): ?>
                            <div>
                                <a href="artikel_edit.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Artikel
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
    </div>
    
    <div class="col-md-4">
        <!-- Info Author -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="bi bi-person-circle"></i> Tentang Penulis</h6>
            </div>
            <div class="card-body">
                <h6><?php echo htmlspecialchars($article['nama_lengkap']); ?></h6>
                <p class="text-muted">@<?php echo htmlspecialchars($article['username']); ?></p>
                
                <!-- Statistik penulis -->
                <?php
                $sql_stats = "SELECT COUNT(*) as total_articles FROM artikels WHERE user_id = ? AND status = 'published'";
                $stmt_stats = $pdo->prepare($sql_stats);
                $stmt_stats->execute([$article['user_id']]);
                $stats = $stmt_stats->fetch();
                ?>
                <small class="text-muted">
                    <i class="bi bi-file-text"></i> 
                    <?php echo $stats['total_articles']; ?> artikel dipublikasikan
                </small>
            </div>
        </div>
        
        <!-- Artikel Terkait -->
        <?php if (!empty($related_articles)): ?>
            <div class="card">
                <div class="card-header">
                    <h6><i class="bi bi-collection"></i> Artikel Terkait</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($related_articles as $related): ?>
                        <div class="mb-3">
                            <h6 class="mb-1">
                                <a href="artikel_detail.php?id=<?php echo $related['id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($related['judul']); ?>
                                </a>
                            </h6>
                            <?php if ($related['ringkasan']): ?>
                                <p class="text-muted small mb-1">
                                    <?php echo substr(htmlspecialchars($related['ringkasan']), 0, 80) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($related['created_at'])); ?>
                            </small>
                        </div>
                        <?php if ($related !== end($related_articles)): ?>
                            <hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Statistik Artikel -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="bi bi-bar-chart"></i> Statistik</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Views:</span>
                    <strong><?php echo $article['views']; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Dibuat:</span>
                    <small><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></small>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Terakhir diupdate:</span>
                    <small><?php echo date('d/m/Y', strtotime($article['updated_at'])); ?></small>
                </div>
                <?php if ($article['published_at']): ?>
                    <div class="d-flex justify-content-between">
                        <span>Dipublikasi:</span>
                        <small><?php echo date('d/m/Y', strtotime($article['published_at'])); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 