<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$title = 'Kelola Kategori - Blog Sederhana';
$error = '';
$success = '';

// Proses hapus kategori
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $kategori_id = $_GET['delete'];
    
    try {
        // Cek apakah kategori masih digunakan artikel
        $sql_check = "SELECT COUNT(*) as count FROM artikels WHERE kategori_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$kategori_id]);
        $article_count = $stmt_check->fetch()['count'];
        
        if ($article_count > 0) {
            $error = "Kategori tidak bisa dihapus karena masih digunakan oleh $article_count artikel!";
        } else {
            $sql = "DELETE FROM kategoris WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$kategori_id]);
            
            $success = 'Kategori berhasil dihapus!';
        }
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Proses tambah/edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    $color = trim($_POST['color']);
    $icon = trim($_POST['icon']);
    
    // Validasi input
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        try {
            // Buat slug dari nama kategori
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama_kategori));
            $slug = trim($slug, '-');
            
            if ($id > 0) {
                // Update kategori
                $sql_check = "SELECT id FROM kategoris WHERE slug = ? AND id != ?";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$slug, $id]);
                
                if ($stmt_check->fetch()) {
                    $slug = $slug . '-' . time();
                }
                
                $sql = "UPDATE kategoris SET nama_kategori = ?, slug = ?, deskripsi = ?, color = ?, icon = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nama_kategori, $slug, $deskripsi, $color, $icon, $id]);
                
                $success = 'Kategori berhasil diupdate!';
            } else {
                // Tambah kategori baru
                $sql_check = "SELECT id FROM kategoris WHERE slug = ?";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$slug]);
                
                if ($stmt_check->fetch()) {
                    $slug = $slug . '-' . time();
                }
                
                $sql = "INSERT INTO kategoris (nama_kategori, slug, deskripsi, color, icon) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nama_kategori, $slug, $deskripsi, $color, $icon]);
                
                $success = 'Kategori baru berhasil ditambahkan!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Ambil data kategori untuk edit
$edit_kategori = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $sql = "SELECT * FROM kategoris WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$edit_id]);
    $edit_kategori = $stmt->fetch();
}

// Ambil semua kategori
$sql = "SELECT k.*, COUNT(a.id) as artikel_count 
        FROM kategoris k 
        LEFT JOIN artikels a ON k.id = a.kategori_id 
        GROUP BY k.id 
        ORDER BY k.nama_kategori";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

// Icon options
$icon_options = [
    'bi-laptop' => 'Laptop',
    'bi-heart' => 'Heart',
    'bi-book' => 'Book',
    'bi-star' => 'Star',
    'bi-briefcase' => 'Briefcase',
    'bi-map' => 'Map',
    'bi-music-note' => 'Music',
    'bi-camera' => 'Camera',
    'bi-palette' => 'Palette',
    'bi-trophy' => 'Trophy',
    'bi-house' => 'House',
    'bi-car-front' => 'Car',
    'bi-airplane' => 'Airplane',
    'bi-shop' => 'Shop',
    'bi-cup' => 'Cup'
];

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>📂 Kelola Kategori</h1>
        <p class="lead">Tambah, edit, dan hapus kategori artikel</p>
        <hr>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Tambah/Edit Kategori -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><?php echo $edit_kategori ? '✏️ Edit Kategori' : '➕ Tambah Kategori Baru'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($edit_kategori): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_kategori['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                               value="<?php echo $edit_kategori ? htmlspecialchars($edit_kategori['nama_kategori']) : ''; ?>" 
                               required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                  placeholder="Deskripsi kategori..."><?php echo $edit_kategori ? htmlspecialchars($edit_kategori['deskripsi']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Warna</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" 
                               value="<?php echo $edit_kategori ? $edit_kategori['color'] : '#007bff'; ?>">
                        <div class="form-text">Pilih warna untuk badge kategori</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <select class="form-select" id="icon" name="icon">
                            <?php foreach ($icon_options as $icon_class => $icon_name): ?>
                                <option value="<?php echo $icon_class; ?>" 
                                        <?php echo ($edit_kategori && $edit_kategori['icon'] == $icon_class) ? 'selected' : ''; ?>>
                                    <?php echo $icon_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Preview:</label>
                        <div id="preview">
                            <span class="badge" id="badge-preview" style="background-color: <?php echo $edit_kategori ? $edit_kategori['color'] : '#007bff'; ?>;">
                                <i class="<?php echo $edit_kategori ? $edit_kategori['icon'] : 'bi-laptop'; ?>"></i>
                                <span id="preview-text"><?php echo $edit_kategori ? htmlspecialchars($edit_kategori['nama_kategori']) : 'Preview'; ?></span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_kategori ? '💾 Update Kategori' : '➕ Tambah Kategori'; ?>
                        </button>
                        <?php if ($edit_kategori): ?>
                            <a href="kategori_manager.php" class="btn btn-secondary">❌ Batal Edit</a>
                        <?php endif; ?>
                        <a href="admin.php" class="btn btn-outline-secondary">← Kembali ke Admin</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Daftar Kategori -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>📋 Daftar Kategori</h5>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="alert alert-info">
                        <h6>📂 Belum ada kategori</h6>
                        <p>Belum ada kategori yang dibuat. Tambahkan kategori pertama di form sebelah kiri.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Artikel</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $kategori): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $kategori['color']; ?>;">
                                                <i class="<?php echo $kategori['icon']; ?>"></i>
                                                <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">Slug: <?php echo $kategori['slug']; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($kategori['deskripsi']): ?>
                                                <?php echo htmlspecialchars($kategori['deskripsi']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Tidak ada deskripsi</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $kategori['artikel_count']; ?> artikel</span>
                                            <?php if ($kategori['artikel_count'] > 0): ?>
                                                <br>
                                                <a href="kategori.php?id=<?php echo $kategori['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info mt-1">
                                                    👁️ Lihat
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="kategori_manager.php?edit=<?php echo $kategori['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit">
                                                    ✏️
                                                </a>
                                                <?php if ($kategori['artikel_count'] == 0): ?>
                                                    <a href="kategori_manager.php?delete=<?php echo $kategori['id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Hapus"
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                        🗑️
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" title="Tidak bisa dihapus (masih ada artikel)" disabled>
                                                        🔒
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Statistik -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>📊 Statistik Kategori</h6>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h4 class="text-primary"><?php echo count($categories); ?></h4>
                                            <p>Total Kategori</p>
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            $total_articles = array_sum(array_column($categories, 'artikel_count'));
                                            ?>
                                            <h4 class="text-success"><?php echo $total_articles; ?></h4>
                                            <p>Total Artikel</p>
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            $categories_with_articles = count(array_filter($categories, function($cat) {
                                                return $cat['artikel_count'] > 0;
                                            }));
                                            ?>
                                            <h4 class="text-info"><?php echo $categories_with_articles; ?></h4>
                                            <p>Kategori Terpakai</p>
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            $empty_categories = count($categories) - $categories_with_articles;
                                            ?>
                                            <h4 class="text-warning"><?php echo $empty_categories; ?></h4>
                                            <p>Kategori Kosong</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview untuk kategori
document.addEventListener('DOMContentLoaded', function() {
    const namaInput = document.getElementById('nama_kategori');
    const colorInput = document.getElementById('color');
    const iconSelect = document.getElementById('icon');
    const badgePreview = document.getElementById('badge-preview');
    const previewText = document.getElementById('preview-text');
    
    function updatePreview() {
        const nama = namaInput.value || 'Preview';
        const color = colorInput.value;
        const icon = iconSelect.value;
        
        previewText.textContent = nama;
        badgePreview.style.backgroundColor = color;
        badgePreview.querySelector('i').className = icon;
    }
    
    namaInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('change', updatePreview);
    iconSelect.addEventListener('change', updatePreview);
});
</script>

<?php include 'footer.php'; ?> 