<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions_enum.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();
$kategoris = getKategori($pdo);

// Get errors and old data from session
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : array();
$old_data = isset($_SESSION['old_data']) ? $_SESSION['old_data'] : array();
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['old_data']);
unset($_SESSION['success_message']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'judul' => sanitize_input($_POST['judul']),
        'konten' => sanitize_input($_POST['konten']),
        'ringkasan' => sanitize_input($_POST['ringkasan']),
        'category_enum' => sanitize_input($_POST['category_enum']),
        'user_id' => $current_user['id'],
        'status' => sanitize_input($_POST['status'])
    ];
    
    // Validasi data
    $validation_errors = validateArtikelData($data);
    
    if (empty($validation_errors)) {
        // Simpan artikel
        if (tambahArtikel($pdo, $data)) {
            $_SESSION['success_message'] = "Artikel berhasil ditambahkan!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Gagal menambahkan artikel. Silakan coba lagi.";
        }
    } else {
        $errors = $validation_errors;
    }
    
    // Store errors and old data in session for redisplay
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Artikel - Literaturku</title>
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <link rel="stylesheet" href="css/artikel_form.css">
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

<main class="form-main">
    
    <div class="form-container">
        <div class="form-header">
            <h2>✏️ Tulis Artikel Baru</h2>
            <p>Bagikan pengetahuan dan pengalaman Anda dengan dunia</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="artikel-form">
            
            <div class="form-group">
                <label for="judul">📝 Judul Artikel *</label>
                <input type="text" id="judul" name="judul" 
                       value="<?php echo isset($old_data['judul']) ? htmlspecialchars($old_data['judul']) : ''; ?>" 
                       placeholder="Masukkan judul artikel yang menarik..." 
                       maxlength="255" required>
                <div class="character-count" id="judul-count">0/255 karakter</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_enum">📁 Kategori *</label>
                    <select id="category_enum" name="category_enum" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategoris as $kategori): ?>
                            <option value="<?php echo $kategori['enum']; ?>" 
                                    <?php echo (isset($old_data['category_enum']) && $old_data['category_enum'] == $kategori['enum']) ? 'selected' : ''; ?>>
                                <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">📊 Status Publikasi</label>
                    <select id="status" name="status">
                        <option value="published" <?php echo (isset($old_data['status']) && $old_data['status'] == 'published') ? 'selected' : ''; ?>>
                            🟢 Publikasikan Langsung
                        </option>
                        <option value="draft" <?php echo (isset($old_data['status']) && $old_data['status'] == 'draft') ? 'selected' : ''; ?>>
                            📝 Simpan sebagai Draft
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="ringkasan">📋 Ringkasan Artikel *</label>
                <textarea id="ringkasan" name="ringkasan" 
                          placeholder="Tulis ringkasan singkat artikel (akan ditampilkan di halaman utama)..." 
                          maxlength="300" required><?php echo isset($old_data['ringkasan']) ? htmlspecialchars($old_data['ringkasan']) : ''; ?></textarea>
                <div class="character-count" id="ringkasan-count">0/300 karakter</div>
            </div>

            <div class="form-group">
                <label for="konten">📄 Konten Artikel *</label>
                <textarea id="konten" name="konten" class="konten-textarea"
                          placeholder="Tulis konten artikel lengkap di sini..." 
                          required><?php echo isset($old_data['konten']) ? htmlspecialchars($old_data['konten']) : ''; ?></textarea>
                <div class="character-count" id="konten-count">0 karakter</div>
            </div>

            <div class="preview-section" id="preview-section" style="display: none;">
                <h4>👀 Preview Artikel</h4>
                <div id="preview-content"></div>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-back">🏠 Kembali ke Beranda</a>
                <button type="button" class="btn btn-secondary" onclick="togglePreview()">👀 Preview</button>
                <button type="submit" class="btn btn-primary">🚀 Publikasikan Artikel</button>
            </div>

        </form>
    </div>

</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>

<script>
// Character counting
function updateCharCount(elementId, maxLength) {
    const element = document.getElementById(elementId);
    const countElement = document.getElementById(elementId + '-count');
    const currentLength = element.value.length;
    
    countElement.textContent = `${currentLength}${maxLength ? '/' + maxLength : ''} karakter`;
    
    // Remove existing classes
    countElement.classList.remove('warning', 'danger');
    
    if (maxLength) {
        if (currentLength > maxLength * 0.95) {
            countElement.classList.add('danger');
        } else if (currentLength > maxLength * 0.8) {
            countElement.classList.add('warning');
        }
    }
}

// Event listeners for character counting
document.getElementById('judul').addEventListener('input', function() {
    updateCharCount('judul', 255);
});

document.getElementById('ringkasan').addEventListener('input', function() {
    updateCharCount('ringkasan', 300);
});

document.getElementById('konten').addEventListener('input', function() {
    updateCharCount('konten');
});

// Preview functionality
function togglePreview() {
    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    
    if (previewSection.style.display === 'none') {
        // Show preview
        const judul = document.getElementById('judul').value;
        const ringkasan = document.getElementById('ringkasan').value;
        const konten = document.getElementById('konten').value;
        const kategoriSelect = document.getElementById('kategori_id');
        const kategoriNama = kategoriSelect.options[kategoriSelect.selectedIndex].text;
        
        previewContent.innerHTML = `
            <div class="preview-content">
                <div class="preview-kategori">
                    ${kategoriNama !== 'Pilih Kategori' ? kategoriNama : 'Kategori Belum Dipilih'}
                </div>
                <h3>${judul || 'Judul Artikel'}</h3>
                <div class="preview-ringkasan">${ringkasan || 'Ringkasan artikel...'}</div>
                <div class="preview-konten">
                    ${konten ? konten.replace(/\n/g, '<br>') : 'Konten artikel...'}
                </div>
                <div class="preview-meta">
                    <span>👤 ${<?php echo json_encode($current_user['username']); ?>}</span>
                    <span>📅 ${new Date().toLocaleDateString('id-ID')}</span>
                </div>
            </div>
        `;
        
        previewSection.style.display = 'block';
        document.querySelector('button[onclick="togglePreview()"]').textContent = '❌ Tutup Preview';
    } else {
        // Hide preview
        previewSection.style.display = 'none';
        document.querySelector('button[onclick="togglePreview()"]').textContent = '👀 Preview';
    }
}

// Form validation
document.getElementById('artikel-form').addEventListener('submit', function(e) {
    const judul = document.getElementById('judul').value.trim();
    const ringkasan = document.getElementById('ringkasan').value.trim();
    const konten = document.getElementById('konten').value.trim();
    const kategori = document.getElementById('kategori_id').value;
    
    if (!judul || !ringkasan || !konten || !kategori) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi (*)');
        return;
    }
    
    if (judul.length < 5) {
        e.preventDefault();
        alert('Judul artikel minimal 5 karakter');
        return;
    }
    
    if (ringkasan.length < 20) {
        e.preventDefault();
        alert('Ringkasan artikel minimal 20 karakter');
        return;
    }
    
    if (konten.length < 50) {
        e.preventDefault();
        alert('Konten artikel minimal 50 karakter');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ Menyimpan...';
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    // Reset if form validation fails
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.innerHTML = originalText;
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    }, 3000);
});

// Initialize character counts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCharCount('judul', 255);
    updateCharCount('ringkasan', 300);
    updateCharCount('konten');
});
</script>

</body>
</html> 