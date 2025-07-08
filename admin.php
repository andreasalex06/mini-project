<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$title = 'Admin Panel - Blog Sederhana';

// Ambil statistik
$sql = "SELECT COUNT(*) as total FROM artikels WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$total_my_articles = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM artikels WHERE status = 'published' AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$total_published = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM artikels WHERE status = 'draft' AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$total_drafts = $stmt->fetch()['total'];

// Ambil artikel terbaru milik user
$sql = "SELECT a.*, k.nama_kategori, k.color 
        FROM artikels a 
        JOIN kategoris k ON a.kategori_id = k.id 
        WHERE a.user_id = ? 
        ORDER BY a.created_at DESC 
        LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$recent_articles = $stmt->fetchAll();

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>⚙️ Pusat Kontrol</h1>
        <p class="lead">Selamat datang, <strong><?php echo $_SESSION['username']; ?></strong>!</p>
        <hr>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title">📄 Total Artikel</h5>
                <h3><?php echo $total_my_articles; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title">✅ Dipublikasikan</h5>
                <h3><?php echo $total_published; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h5 class="card-title">📝 Draft</h5>
                <h3><?php echo $total_drafts; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5 class="card-title">👤 Role</h5>
                <h3><?php echo ucfirst($_SESSION['role']); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Menu Utama -->
<div class="row mb-4">
    <div class="col-md-12">
        <h3>🚀 Menu Utama</h3>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">📝 Kelola Artikel</h5>
                <p class="card-text">Buat, edit, dan hapus artikel</p>
                <a href="artikel_manager.php" class="btn btn-primary">Kelola Artikel</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">📂 Kelola Kategori</h5>
                <p class="card-text">Tambah dan edit kategori</p>
                <a href="kategori_manager.php" class="btn btn-success">Kelola Kategori</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">👤 Profil</h5>
                <p class="card-text">Edit profil dan password</p>
                <a href="profile.php" class="btn btn-info">Edit Profil</a>
            </div>
        </div>
    </div>
</div>

<!-- Artikel Terbaru -->
<div class="row mt-5">
    <div class="col-md-12">
        <h3>📰 Artikel Terbaru Saya</h3>
        <hr>
    </div>
</div>

<?php if (empty($recent_articles)): ?>
    <div class="alert alert-info">
        <h5>📝 Belum ada artikel</h5>
        <p>Anda belum membuat artikel apapun. Mulai menulis artikel pertama Anda!</p>
        <a href="artikel_add.php" class="btn btn-primary">Tulis Artikel Baru</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_articles as $article): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($article['judul']); ?></td>
                        <td>
                            <span class="badge" style="background-color: <?php echo $article['color']; ?>;">
                                <?php echo $article['nama_kategori']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($article['status'] == 'published'): ?>
                                <span class="badge bg-success">Published</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                        <td>
                            <a href="artikel_edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="artikel_detail.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info">Lihat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="text-center mt-3">
        <a href="artikel_manager.php" class="btn btn-primary">Lihat Semua Artikel</a>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?> 