<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions_enum.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();
$kategoris = getKategori($pdo);

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['errors'] = ['Artikel tidak ditemukan'];
    header("Location: index.php");
    exit();
}

// Get article details
$artikel = getArtikelById($pdo, $id);

if (!$artikel) {
    $_SESSION['errors'] = ['Artikel tidak ditemukan'];
    header("Location: index.php");
    exit();
}

// Check authorization - only article owner can edit
if ($artikel['user_id'] != $current_user['id']) {
    $_SESSION['errors'] = ['Anda tidak memiliki izin untuk mengedit artikel ini'];
    header("Location: artikel_detail.php?id=" . $id);
    exit();
}

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
        'status' => sanitize_input($_POST['status'])
    ];
    
    // Validasi data
    $validation_errors = validateArtikelData($data);
    
    if (empty($validation_errors)) {
        // Update artikel
        if (updateArtikel($pdo, $id, $data)) {
            $_SESSION['success_message'] = "Artikel berhasil diperbarui!";
            header("Location: artikel_detail.php?id=" . $id);
            exit();
        } else {
            $errors[] = "Gagal memperbarui artikel. Silakan coba lagi.";
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

// Use old data or current article data
$form_data = !empty($old_data) ? $old_data : [
    'judul' => $artikel['judul'],
    'konten' => $artikel['konten'],
    'ringkasan' => $artikel['ringkasan'],
    'category_enum' => $artikel['category_enum'],
    'status' => $artikel['status']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - <?php echo htmlspecialchars($artikel['judul']); ?> - Literaturku</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/edit_artikel.css">
</head>
<body>

<header style="width: 100%;">
  <div class="logo">
    <h1>Literaturku</h1>
  </div>
  <nav>
    <?php include 'navigasi.php'; ?>
  </nav>
</header>

<main class="edit-main">
    
    <div class="form-container">
        <div class="form-header">
            <h2>✏️ Edit Artikel</h2>
            <p>Perbarui konten artikel Anda</p>
        </div>

        <!-- Article Info -->
        <div class="article-info">
            <h4>📄 Informasi Artikel</h4>
            <p><strong>Judul Saat Ini:</strong> <?php echo htmlspecialchars($artikel['judul']); ?></p>
            <p><strong>Dibuat:</strong> <?php echo formatTanggal($artikel['created_at']); ?> • 
               <strong>Terakhir Diupdate:</strong> <?php echo formatTanggal($artikel['updated_at']); ?> • 
               <strong>Views:</strong> <?php echo number_format($artikel['views']); ?></p>
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
                       value="<?php echo htmlspecialchars($form_data['judul']); ?>" 
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
                                    <?php echo $form_data['category_enum'] == $kategori['enum'] ? 'selected' : ''; ?>>
                                <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">📊 Status Publikasi</label>
                    <select id="status" name="status">
                        <option value="published" <?php echo $form_data['status'] == 'published' ? 'selected' : ''; ?>>
                            🟢 Publikasikan
                        </option>
                        <option value="draft" <?php echo $form_data['status'] == 'draft' ? 'selected' : ''; ?>>
                            📝 Simpan sebagai Draft
                        </option>
                        <option value="archived" <?php echo $form_data['status'] == 'archived' ? 'selected' : ''; ?>>
                            📦 Arsipkan
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="ringkasan">📋 Ringkasan Artikel *</label>
                <textarea id="ringkasan" name="ringkasan" 
                          placeholder="Tulis ringkasan singkat artikel (akan ditampilkan di halaman utama)..." 
                          maxlength="300" required><?php echo htmlspecialchars($form_data['ringkasan']); ?></textarea>
                <div class="character-count" id="ringkasan-count">0/300 karakter</div>
            </div>

            <div class="form-group">
                <label for="konten">📄 Konten Artikel *</label>
                <textarea id="konten" name="konten" class="konten-textarea"
                          placeholder="Tulis konten artikel lengkap di sini..." 
                          required><?php echo htmlspecialchars($form_data['konten']); ?></textarea>
                <div class="character-count" id="konten-count">0 karakter</div>
            </div>

            <div class="preview-section" id="preview-section" style="display: none;">
                <h4>👀 Preview Artikel</h4>
                <div class="preview-content" id="preview-content"></div>
            </div>

            <div class="form-actions">
                <a href="artikel_detail.php?id=<?php echo $id; ?>" class="btn btn-back">← Kembali ke Artikel</a>
                <button type="button" class="btn btn-secondary" onclick="togglePreview()">👀 Preview</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>

        </form>

        <!-- Delete Section -->
        <div class="delete-section">
            <h4>⚠️ Zona Bahaya</h4>
            <p>
                Menghapus artikel akan menghilangkan semua data secara permanen dan tidak dapat dikembalikan. 
                Pastikan Anda benar-benar yakin sebelum melanjutkan.
            </p>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                🗑️ Hapus Artikel
            </button>
        </div>
    </div>

</main>

<footer style="width: 100%;">
    <?php include 'footer.php'; ?>
</footer>

<script>
// ===== CHARACTER COUNTING =====
function updateCharCount(elementId, maxLength) {
    const element = document.getElementById(elementId);
    const countElement = document.getElementById(elementId + '-count');
    
    if (!element || !countElement) return;
    
    const currentLength = element.value.length;
    const displayText = maxLength ? `${currentLength}/${maxLength} karakter` : `${currentLength} karakter`;
    
    countElement.textContent = displayText;
    
    // Remove existing classes
    countElement.classList.remove('warning', 'error');
    
    if (maxLength) {
        if (currentLength > maxLength) {
            countElement.classList.add('error');
        } else if (currentLength > maxLength * 0.9) {
            countElement.classList.add('warning');
        }
    }
}

// ===== PREVIEW FUNCTIONALITY =====
function togglePreview() {
    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    const previewBtn = document.querySelector('button[onclick="togglePreview()"]');
    
    if (previewSection.style.display === 'none' || !previewSection.style.display) {
        // Show preview
        const judul = document.getElementById('judul').value.trim();
        const ringkasan = document.getElementById('ringkasan').value.trim();
        const konten = document.getElementById('konten').value.trim();
        const kategoriSelect = document.getElementById('category_enum');
        const kategoriText = kategoriSelect.options[kategoriSelect.selectedIndex].text;
        const username = <?php echo json_encode($current_user['username']); ?>;
        const views = <?php echo $artikel['views']; ?>;
        
        previewContent.innerHTML = `
            <div class="preview-kategori">
                ${kategoriText !== 'Pilih Kategori' ? kategoriText : 'Kategori Belum Dipilih'}
            </div>
            <div class="preview-title">${judul || 'Judul Artikel'}</div>
            <div class="preview-summary">${ringkasan || 'Ringkasan artikel...'}</div>
            <div class="preview-divider">
                ${konten ? konten.replace(/\n/g, '<br>') : 'Konten artikel...'}
            </div>
            <div class="preview-meta">
                <span>👤 ${username}</span>
                <span>📅 ${new Date().toLocaleDateString('id-ID')}</span>
                <span>👁️ ${views.toLocaleString('id-ID')}</span>
            </div>
        `;
        
        previewSection.style.display = 'block';
        previewBtn.innerHTML = '❌ Tutup Preview';
        previewBtn.classList.remove('btn-secondary');
        previewBtn.classList.add('btn-danger');
    } else {
        // Hide preview
        previewSection.style.display = 'none';
        previewBtn.innerHTML = '👀 Preview';
        previewBtn.classList.remove('btn-danger');
        previewBtn.classList.add('btn-secondary');
    }
}

// ===== DELETE CONFIRMATION =====
function confirmDelete() {
    const articleTitle = <?php echo json_encode($artikel['judul']); ?>;
    const messages = {
        first: `⚠️ PERINGATAN PENGHAPUSAN!\n\nApakah Anda yakin ingin menghapus artikel "${articleTitle}"?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus artikel secara permanen.`,
        second: `Konfirmasi sekali lagi untuk menghapus artikel:\n\n"${articleTitle}"\n\nKlik OK untuk melanjutkan penghapusan.`
    };
    
    if (confirm(messages.first) && confirm(messages.second)) {
        // Show loading state
        document.body.classList.add('loading');
        
        // Redirect to delete handler
        window.location.href = `hapus_artikel.php?id=<?php echo $id; ?>`;
    }
}

// ===== FORM VALIDATION =====
function validateForm(e) {
    const judul = document.getElementById('judul').value.trim();
    const ringkasan = document.getElementById('ringkasan').value.trim();
    const konten = document.getElementById('konten').value.trim();
    const kategori = document.getElementById('category_enum').value;
    
    const validations = [
        { condition: !judul, message: 'Judul artikel wajib diisi' },
        { condition: !ringkasan, message: 'Ringkasan artikel wajib diisi' },
        { condition: !konten, message: 'Konten artikel wajib diisi' },
        { condition: !kategori, message: 'Kategori artikel wajib dipilih' },
        { condition: judul.length < 5, message: 'Judul artikel minimal 5 karakter' },
        { condition: ringkasan.length < 20, message: 'Ringkasan artikel minimal 20 karakter' },
        { condition: konten.length < 50, message: 'Konten artikel minimal 50 karakter' },
        { condition: judul.length > 255, message: 'Judul artikel maksimal 255 karakter' },
        { condition: ringkasan.length > 300, message: 'Ringkasan artikel maksimal 300 karakter' }
    ];
    
    for (const validation of validations) {
        if (validation.condition) {
            e.preventDefault();
            alert(`❌ ${validation.message}`);
            return false;
        }
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    return true;
}

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    // Character counting event listeners
    const elements = [
        { id: 'judul', max: 255 },
        { id: 'ringkasan', max: 300 },
        { id: 'konten', max: null }
    ];
    
    elements.forEach(({ id, max }) => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', () => updateCharCount(id, max));
            // Initialize count
            updateCharCount(id, max);
        }
    });
    
    // Form validation
    const form = document.getElementById('artikel-form');
    if (form) {
        form.addEventListener('submit', validateForm);
    }
    
    // Auto-save draft every 2 minutes (optional enhancement)
    setInterval(function() {
        const judul = document.getElementById('judul').value.trim();
        const konten = document.getElementById('konten').value.trim();
        
        if (judul && konten && konten.length > 50) {
            // Could implement auto-save functionality here
            console.log('Auto-save: Form data ready for saving');
        }
    }, 120000); // 2 minutes
});

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S for save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const form = document.getElementById('artikel-form');
        if (form) {
            form.dispatchEvent(new Event('submit'));
        }
    }
    
    // Ctrl/Cmd + P for preview
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        togglePreview();
    }
});
</script>

</body>
</html> 