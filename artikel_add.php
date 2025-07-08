<?php
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$title = 'Tulis Artikel Baru - Blog Sederhana';
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
            // Buat slug dari judul
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul));
            $slug = trim($slug, '-');
            
            // Pastikan slug unik
            $sql_check = "SELECT id FROM artikels WHERE slug = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$slug]);
            
            if ($stmt_check->fetch()) {
                $slug = $slug . '-' . time(); // Tambahkan timestamp jika slug sudah ada
            }
            
            // Insert artikel
            $sql = "INSERT INTO artikels 
                    (judul, slug, konten, ringkasan, user_id, kategori_id, status, tags, meta_title, meta_description, published_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $published_at = ($status == 'published') ? date('Y-m-d H:i:s') : null;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $judul,
                $slug,
                $konten,
                $ringkasan,
                $_SESSION['user_id'],
                $kategori_id,
                $status,
                $tags,
                $meta_title,
                $meta_description,
                $published_at
            ]);
            
            $success = 'Artikel berhasil disimpan!';
            
            // Redirect ke artikel manager setelah 2 detik
            header("refresh:2;url=artikel_manager.php");
            
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>✍️ Tulis Artikel Baru</h1>
        <p class="lead">Buat artikel baru untuk blog Anda</p>
        <hr>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo $success; ?>
        <br><small>Anda akan diarahkan ke halaman kelola artikel...</small>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>📝 Form Artikel Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Judul -->
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul Artikel *</label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>" 
                                       required maxlength="200">
                                <div class="form-text">Maksimal 200 karakter</div>
                            </div>
                            
                            <!-- Ringkasan -->
                            <div class="mb-3">
                                <label for="ringkasan" class="form-label">Ringkasan</label>
                                <textarea class="form-control" id="ringkasan" name="ringkasan" rows="3" 
                                          placeholder="Ringkasan singkat artikel..."><?php echo isset($_POST['ringkasan']) ? htmlspecialchars($_POST['ringkasan']) : ''; ?></textarea>
                                <div class="form-text">Deskripsi singkat artikel (opsional)</div>
                            </div>
                            
                            <!-- Konten -->
                            <div class="mb-3">
                                <label for="konten" class="form-label">Konten Artikel *</label>
                                <textarea class="form-control" id="konten" name="konten" rows="15" 
                                          placeholder="Tulis konten artikel di sini..." required><?php echo isset($_POST['konten']) ? htmlspecialchars($_POST['konten']) : ''; ?></textarea>
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
                                               value="<?php echo isset($_POST['meta_title']) ? htmlspecialchars($_POST['meta_title']) : ''; ?>" 
                                               maxlength="200">
                                        <div class="form-text">Judul untuk mesin pencari (jika kosong, akan menggunakan judul artikel)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2" 
                                                  placeholder="Deskripsi untuk mesin pencari..."><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>
                                        📝 Draft (Belum Dipublikasi)
                                    </option>
                                    <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>
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
                                                <?php echo (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tags -->
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>" 
                                       placeholder="tag1, tag2, tag3">
                                <div class="form-text">Pisahkan dengan koma (,)</div>
                            </div>
                            
                            <!-- Tombol -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    💾 Simpan Artikel
                                </button>
                                <a href="artikel_manager.php" class="btn btn-secondary">
                                    ← Kembali
                                </a>
                            </div>
                            
                            <!-- Info -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6>💡 Tips Menulis</h6>
                                    <ul class="small">
                                        <li>Buat judul yang menarik dan deskriptif</li>
                                        <li>Gunakan ringkasan untuk preview artikel</li>
                                        <li>Pilih kategori yang sesuai</li>
                                        <li>Gunakan tags untuk memudahkan pencarian</li>
                                        <li>Draft: artikel belum terlihat publik</li>
                                        <li>Published: artikel bisa dilihat publik</li>
                                    </ul>
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