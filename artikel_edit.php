<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Ambil ID artikel dari URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header('Location: artikel_manager.php');
    exit();
}

// Ambil artikel
$sql = "SELECT * FROM artikels WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: artikel_manager.php');
    exit();
}

// Cek apakah user bisa edit artikel ini
$can_edit = false;
if ($article['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] == 'admin') {
    $can_edit = true;
}

if (!$can_edit) {
    header('Location: artikel_manager.php');
    exit();
}

$title = 'Edit Artikel - ' . $article['judul'];
$error = '';
$success = '';

// Ambil semua kategori
$sql = "SELECT * FROM kategoris ORDER BY nama_kategori";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    $ringkasan = trim($_POST['ringkasan']);
    $kategori_id = $_POST['kategori_id'];
    $status = $_POST['status'];
    $tags = trim($_POST['tags']);
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    
    // Validasi input
    if (empty($judul) || empty($konten) || empty($kategori_id)) {
        $error = 'Judul, konten, dan kategori harus diisi!';
    } else {
        try {
            // Update slug jika judul berubah
            $slug = $article['slug'];
            if ($judul !== $article['judul']) {
                $new_slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul));
                $new_slug = trim($new_slug, '-');
                
                // Pastikan slug unik
                $sql_check = "SELECT id FROM artikels WHERE slug = ? AND id != ?";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$new_slug, $article_id]);
                
                if ($stmt_check->fetch()) {
                    $new_slug = $new_slug . '-' . time();
                }
                
                $slug = $new_slug;
            }
            
            // Set published_at jika status berubah ke published
            $published_at = $article['published_at'];
            if ($status == 'published' && $article['status'] != 'published') {
                $published_at = date('Y-m-d H:i:s');
            } elseif ($status == 'draft') {
                $published_at = null;
            }
            
            // Update artikel
            $sql = "UPDATE artikels SET 
                    judul = ?, slug = ?, konten = ?, ringkasan = ?, 
                    kategori_id = ?, status = ?, tags = ?, 
                    meta_title = ?, meta_description = ?, published_at = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $judul, $slug, $konten, $ringkasan,
                $kategori_id, $status, $tags,
                $meta_title, $meta_description, $published_at,
                $article_id
            ]);
            
            $success = 'Artikel berhasil diupdate!';
            
            // Update data artikel untuk form
            $article['judul'] = $judul;
            $article['konten'] = $konten;
            $article['ringkasan'] = $ringkasan;
            $article['kategori_id'] = $kategori_id;
            $article['status'] = $status;
            $article['tags'] = $tags;
            $article['meta_title'] = $meta_title;
            $article['meta_description'] = $meta_description;
            
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>✏️ Edit Artikel</h1>
        <p class="lead">Edit artikel: <strong><?php echo htmlspecialchars($article['judul']); ?></strong></p>
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
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>📝 Form Edit Artikel</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Judul -->
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul Artikel *</label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($article['judul']); ?>" 
                                       required maxlength="200">
                                <div class="form-text">Maksimal 200 karakter</div>
                            </div>
                            
                            <!-- Ringkasan -->
                            <div class="mb-3">
                                <label for="ringkasan" class="form-label">Ringkasan</label>
                                <textarea class="form-control" id="ringkasan" name="ringkasan" rows="3" 
                                          placeholder="Ringkasan singkat artikel..."><?php echo htmlspecialchars($article['ringkasan']); ?></textarea>
                                <div class="form-text">Deskripsi singkat artikel (opsional)</div>
                            </div>
                            
                            <!-- Konten -->
                            <div class="mb-3">
                                <label for="konten" class="form-label">Konten Artikel *</label>
                                <textarea class="form-control" id="konten" name="konten" rows="15" 
                                          placeholder="Tulis konten artikel di sini..." required><?php echo htmlspecialchars($article['konten']); ?></textarea>
                                <div class="form-text">Tulis konten artikel lengkap di sini</div>
                            </div>
                            
                            <!-- SEO Section -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6>🔍 SEO (Opsional)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?php echo htmlspecialchars($article['meta_title']); ?>" 
                                               maxlength="200">
                                        <div class="form-text">Judul untuk mesin pencari</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2" 
                                                  placeholder="Deskripsi untuk mesin pencari..."><?php echo htmlspecialchars($article['meta_description']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Info -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6>ℹ️ Info Artikel</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Dibuat:</strong><br><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></p>
                                    <p><strong>Terakhir diupdate:</strong><br><?php echo date('d/m/Y H:i', strtotime($article['updated_at'])); ?></p>
                                    <p><strong>Views:</strong> <?php echo $article['views']; ?></p>
                                    <p><strong>Slug:</strong><br><small class="text-muted"><?php echo $article['slug']; ?></small></p>
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft" <?php echo ($article['status'] == 'draft') ? 'selected' : ''; ?>>
                                        📝 Draft (Belum Dipublikasi)
                                    </option>
                                    <option value="published" <?php echo ($article['status'] == 'published') ? 'selected' : ''; ?>>
                                        ✅ Published (Dipublikasi)
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Kategori -->
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori *</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($article['kategori_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tags -->
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       value="<?php echo htmlspecialchars($article['tags']); ?>" 
                                       placeholder="tag1, tag2, tag3">
                                <div class="form-text">Pisahkan dengan koma (,)</div>
                            </div>
                            
                            <!-- Tombol -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    💾 Update Artikel
                                </button>
                                <a href="artikel_detail.php?id=<?php echo $article['id']; ?>" class="btn btn-info">
                                    👁️ Lihat Artikel
                                </a>
                                <a href="artikel_manager.php" class="btn btn-secondary">
                                    ← Kembali
                                </a>
                            </div>
                            
                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6>⚙️ Aksi Lain</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="artikel_manager.php?delete=<?php echo $article['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                            🗑️ Hapus Artikel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 