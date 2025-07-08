<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$title = 'Kelola Artikel - Blog Sederhana';

// Ambil semua artikel milik user
$sql = "SELECT a.*, k.nama_kategori, k.color 
        FROM artikels a 
        JOIN kategoris k ON a.kategori_id = k.id 
        WHERE a.user_id = ? 
        ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$articles = $stmt->fetchAll();

// Proses hapus artikel
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $article_id = $_GET['delete'];
    
    // Pastikan artikel milik user yang sedang login
    $sql = "SELECT id FROM artikels WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$article_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $sql = "DELETE FROM artikels WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$article_id]);
        
        header('Location: artikel_manager.php?deleted=1');
        exit();
    }
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>📝 Kelola Artikel</h1>
        <p class="lead">Kelola semua artikel yang Anda tulis</p>
        <hr>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ Artikel berhasil dihapus!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tombol Tambah Artikel -->
<div class="row mb-4">
    <div class="col-md-12">
        <a href="artikel_add.php" class="btn btn-primary">
            ➕ Tulis Artikel Baru
        </a>
        <a href="admin.php" class="btn btn-secondary">
            ← Kembali ke Admin Panel
        </a>
    </div>
</div>

<!-- Daftar Artikel -->
<?php if (empty($articles)): ?>
    <div class="alert alert-info">
        <h5>📝 Belum ada artikel</h5>
        <p>Anda belum membuat artikel apapun. Mulai menulis artikel pertama Anda!</p>
        <a href="artikel_add.php" class="btn btn-primary">Tulis Artikel Baru</a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($article['judul']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo substr(strip_tags($article['konten']), 0, 80) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $article['color']; ?>;">
                                        <?php echo $article['nama_kategori']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($article['status'] == 'published'): ?>
                                        <span class="badge bg-success">✅ Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">📝 Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($article['updated_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="artikel_detail.php?id=<?php echo $article['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Lihat">
                                            👁️
                                        </a>
                                        <a href="artikel_edit.php?id=<?php echo $article['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            ✏️
                                        </a>
                                        <a href="artikel_manager.php?delete=<?php echo $article['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Hapus"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Statistik -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">📊 Statistik Artikel Anda</h5>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary"><?php echo count($articles); ?></h4>
                            <p>Total Artikel</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">
                                <?php echo count(array_filter($articles, function($a) { return $a['status'] == 'published'; })); ?>
                            </h4>
                            <p>Dipublikasikan</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">
                                <?php echo count(array_filter($articles, function($a) { return $a['status'] == 'draft'; })); ?>
                            </h4>
                            <p>Draft</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">
                                <?php echo date('d/m/Y', strtotime(end($articles)['created_at'])); ?>
                            </h4>
                            <p>Artikel Terakhir</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?> 