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
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="Edit artikel di Literaturku - Platform literasi modern untuk menambah dan membagikan literasi kepada dunia">
    <title>Edit Artikel - <?php echo htmlspecialchars($artikel['judul']); ?> - Literaturku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Prevent horizontal scrollbar */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fa;
        }
        
        /* Ensure all containers are responsive */
        .container {
            max-width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        /* Form elements responsive fixes */
        .form-control, .form-select {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Cards responsive */
        .card {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .card-body {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Flex container fixes */
        .d-flex {
            flex-wrap: wrap;
        }
        
        /* Text content overflow prevention */
        .preview-content {
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }
        
        /* Button group responsive */
        .btn-group, .d-flex.gap-2 {
            flex-wrap: wrap;
        }
        
        /* Toast container fix */
        .toast-container {
            max-width: 100vw;
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .toast {
            max-width: calc(100vw - 20px);
        }
        
        /* Auto-save indicator responsive */
        #autosave-indicator {
            max-width: calc(100vw - 40px);
            word-wrap: break-word;
        }
        
        /* Mobile specific fixes */
        @media (max-width: 768px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .col-lg-10, .col-xl-8 {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .card-body {
                padding: 1rem !important;
            }
            
            .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            
            .btn-lg {
                font-size: 0.875rem;
                padding: 0.5rem 1rem;
            }
            
            /* Stack form actions on mobile */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .d-flex.justify-content-between > .d-flex {
                width: 100%;
                justify-content: center;
            }
            
            /* Responsive text areas */
            textarea.form-control {
                min-height: 120px;
            }
            
            /* Make long text break properly */
            .bg-info.text-white p,
            .bg-info.text-white span {
                word-break: break-word;
                overflow-wrap: break-word;
            }
        }
        
        @media (max-width: 576px) {
            h2.h3 {
                font-size: 1.25rem;
            }
            
            .p-4 {
                padding: 0.75rem !important;
            }
            
            /* Smaller buttons on very small screens */
            .btn-lg {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
            
            /* Make container even smaller on tiny screens */
            .container {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .col-lg-10, .col-xl-8 {
                padding-left: 0;
                padding-right: 0;
            }
            
            /* Ensure preview content doesn't overflow */
            #preview-content {
                padding: 1rem !important;
                font-size: 0.875rem;
            }
        }
        
        /* Utility class to prevent any element from causing horizontal scroll */
        .no-overflow {
            max-width: 100%;
            overflow: hidden;
        }
        
        /* Ensure pre-formatted text doesn't cause overflow */
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            max-width: 100%;
            overflow-x: auto;
        }
        
        /* Fix for any tables that might be too wide */
        table {
            max-width: 100%;
            table-layout: fixed;
        }
        
        /* Image responsive */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Fix specific Bootstrap classes that might cause overflow */
        .rounded-top-3, .rounded-bottom-3, .rounded-3 {
            border-radius: 0.75rem !important;
        }
        
        /* Ensure alert content doesn't overflow */
        .alert {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .alert ul {
            padding-left: 1.25rem;
        }
        
        /* Fix for form labels and text that might be too long */
        .form-label {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Ensure auto-save status doesn't cause overflow */
        #header-autosave-status {
            right: 0.5rem;
            max-width: 150px;
            font-size: 0.75rem;
        }
        
                 @media (max-width: 480px) {
             #header-autosave-status {
                 display: none !important;
             }
         }
         
         /* Additional responsive button utility */
         @media (min-width: 768px) {
             .w-md-auto {
                 width: auto !important;
             }
         }
         
         /* Ensure form elements have proper box-sizing */
         * {
             box-sizing: border-box;
         }
         
         /* Fix for any potential margin causing overflow */
         .row {
             margin-left: 0;
             margin-right: 0;
         }
         
         .col-lg-10, .col-xl-8, .col-md-6 {
             padding-left: 0.75rem;
             padding-right: 0.75rem;
         }
    </style>
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">

    <?php include 'navigasi.php'; ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <!-- Form Header -->
            <div class="bg-primary text-white text-center p-4 rounded-top-3 mb-0 position-relative">
                <h2 class="h3 mb-2"><i class="bi bi-pencil-square me-2"></i>Edit Artikel</h2>
                <p class="mb-0 opacity-75">Perbarui konten artikel Anda</p>
                <!-- Auto-save status indicator -->
                <div id="header-autosave-status" class="position-absolute top-0 end-0 p-2 opacity-75" style="display: none;">
                    <small><i class="bi bi-cloud-check"></i> Auto-save aktif</small>
                </div>
        </div>

            <!-- Main Card -->
            <div class="card shadow-sm border-0 rounded-top-0 rounded-bottom-3">
                <div class="card-body p-4">

        <!-- Article Info -->
                    <div class="bg-info text-white p-4 rounded-3 mb-4">
                        <h5 class="mb-3 fw-bold"><i class="bi bi-file-text me-2"></i>Informasi Artikel</h5>
                        <p class="mb-2"><strong>Judul Saat Ini:</strong> <?php echo htmlspecialchars($artikel['judul']); ?></p>
                        <div class="d-flex flex-wrap gap-3 small">
                            <span><i class="bi bi-calendar-plus me-1"></i><strong>Dibuat:</strong> <?php echo formatTanggal($artikel['created_at']); ?></span>
                            <span><i class="bi bi-calendar-check me-1"></i><strong>Diupdate:</strong> <?php echo formatTanggal($artikel['updated_at']); ?></span>
                            <span><i class="bi bi-eye me-1"></i><strong>Views:</strong> <?php echo number_format($artikel['views']); ?></span>
                        </div>
        </div>

                    <!-- Alerts -->
        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

                    <!-- Form -->
        <form method="POST" id="artikel-form">
            
                        <!-- Judul -->
                        <div class="mb-4">
                            <label for="judul" class="form-label fw-semibold">
                                <i class="bi bi-type me-1"></i>Judul Artikel <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="judul" name="judul" 
                       value="<?php echo htmlspecialchars($form_data['judul']); ?>" 
                       placeholder="Masukkan judul artikel yang menarik..." 
                       maxlength="255" required>
                            <div class="form-text text-muted small" id="judul-count">0/255 karakter</div>
            </div>

                        <!-- Kategori & Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="category_enum" class="form-label fw-semibold">
                                    <i class="bi bi-folder me-1"></i>Kategori <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="category_enum" name="category_enum" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategoris as $kategori): ?>
                            <option value="<?php echo $kategori['enum']; ?>" 
                                    <?php echo $form_data['category_enum'] == $kategori['enum'] ? 'selected' : ''; ?>>
                                <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">
                                    <i class="bi bi-graph-up me-1"></i>Status Publikasi
                                </label>
                                <select class="form-select form-select-lg" id="status" name="status">
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

                        <!-- Ringkasan -->
                        <div class="mb-4">
                            <label for="ringkasan" class="form-label fw-semibold">
                                <i class="bi bi-card-text me-1"></i>Ringkasan Artikel <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="ringkasan" name="ringkasan" rows="3"
                          placeholder="Tulis ringkasan singkat artikel (akan ditampilkan di halaman utama)..." 
                          maxlength="300" required><?php echo htmlspecialchars($form_data['ringkasan']); ?></textarea>
                            <div class="form-text text-muted small" id="ringkasan-count">0/300 karakter</div>
            </div>

                        <!-- Konten -->
                        <div class="mb-4">
                            <label for="konten" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1"></i>Konten Artikel <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="konten" name="konten" rows="12"
                          placeholder="Tulis konten artikel lengkap di sini..." 
                          required><?php echo htmlspecialchars($form_data['konten']); ?></textarea>
                            <div class="form-text text-muted small" id="konten-count">0 karakter</div>
            </div>

                        <!-- Preview Section -->
                        <div class="mb-4 d-none" id="preview-section">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-eye me-2"></i>Preview Artikel</h5>
                            <div class="border rounded-3 p-4 bg-white" id="preview-content">
                                <!-- Preview content will be inserted here -->
                            </div>
            </div>

                        <!-- Form Actions -->
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <a href="artikel_detail.php?id=<?php echo $id; ?>" class="btn btn-secondary btn-lg">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Artikel
                            </a>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-lg" onclick="togglePreview()">
                                    <i class="bi bi-eye me-1"></i>Preview
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save me-1"></i>Simpan Perubahan
                                </button>
                            </div>
            </div>

        </form>
                </div>
            </div>

        <!-- Delete Section -->
            <div class="bg-warning bg-opacity-10 border-start border-danger border-5 p-4 rounded-3 mt-4" style="word-wrap: break-word; overflow-wrap: break-word;">
                <h5 class="text-danger mb-3 fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Zona Bahaya
                </h5>
                <p class="mb-3 text-muted" style="word-wrap: break-word; overflow-wrap: break-word;">
                Menghapus artikel akan menghilangkan semua data secara permanen dan tidak dapat dikembalikan. 
                Pastikan Anda benar-benar yakin sebelum melanjutkan.
            </p>
                <button type="button" class="btn btn-danger btn-lg w-100 w-md-auto" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i>Hapus Artikel
            </button>
            </div>

        </div>
    </div>
</div>

    <?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===== CHARACTER COUNTING =====
function updateCharCount(elementId, maxLength) {
    const element = document.getElementById(elementId);
    const countElement = document.getElementById(elementId + '-count');
    
    if (!element || !countElement) return;
    
    const currentLength = element.value.length;
    const displayText = maxLength ? `${currentLength}/${maxLength} karakter` : `${currentLength} karakter`;
    
    countElement.textContent = displayText;
    
    // Remove existing classes and apply Bootstrap color utilities
    countElement.classList.remove('text-warning', 'text-danger', 'text-muted');
    
    if (maxLength) {
        if (currentLength > maxLength) {
            countElement.classList.add('text-danger');
        } else if (currentLength > maxLength * 0.9) {
            countElement.classList.add('text-warning');
        } else {
            countElement.classList.add('text-muted');
        }
    } else {
        countElement.classList.add('text-muted');
    }
}

// ===== PREVIEW FUNCTIONALITY =====
function togglePreview() {
    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    const previewBtn = document.querySelector('button[onclick="togglePreview()"]');
    
    if (previewSection.classList.contains('d-none')) {
        // Show preview
        const judul = document.getElementById('judul').value.trim();
        const ringkasan = document.getElementById('ringkasan').value.trim();
        const konten = document.getElementById('konten').value.trim();
        const kategoriSelect = document.getElementById('category_enum');
        const kategoriText = kategoriSelect.options[kategoriSelect.selectedIndex].text;
        const username = <?php echo json_encode($current_user['username']); ?>;
        const views = <?php echo $artikel['views']; ?>;
        
        // Calculate reading time (200 words per minute)
        const wordCount = konten.split(' ').filter(word => word.length > 0).length;
        const readingTime = Math.ceil(wordCount / 200);
        
        previewContent.innerHTML = `
            <div class="mb-3">
                <span class="badge bg-secondary rounded-pill">
                    ${kategoriText !== 'Pilih Kategori' ? kategoriText : '📁 Kategori Belum Dipilih'}
                </span>
            </div>
            <h2 class="h3 fw-bold text-dark mb-3" style="word-wrap: break-word; overflow-wrap: break-word;">${judul || 'Judul Artikel'}</h2>
            <div class="bg-light p-3 rounded-3 border-start border-primary border-4 mb-4">
                <em class="text-muted" style="word-wrap: break-word; overflow-wrap: break-word;">${ringkasan || 'Ringkasan artikel...'}</em>
            </div>
            <div class="lh-lg text-dark mb-4" style="white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                ${konten || 'Konten artikel...'}
            </div>
            <div class="d-flex flex-wrap gap-2 small text-muted pt-3 border-top">
                <span><i class="bi bi-person me-1"></i>${username}</span>
                <span><i class="bi bi-calendar me-1"></i>${new Date().toLocaleDateString('id-ID')}</span>
                <span><i class="bi bi-eye me-1"></i>${views.toLocaleString('id-ID')}</span>
                <span><i class="bi bi-clock me-1"></i>${readingTime} menit baca</span>
                <span><i class="bi bi-type me-1"></i>${wordCount} kata</span>
            </div>
        `;
        
        previewSection.classList.remove('d-none');
        previewBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Tutup Preview';
        previewBtn.classList.remove('btn-outline-primary');
        previewBtn.classList.add('btn-outline-danger');
        
        // Scroll to preview with Bootstrap smooth scrolling
        previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Hide preview
        previewSection.classList.add('d-none');
        previewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview';
        previewBtn.classList.remove('btn-outline-danger');
        previewBtn.classList.add('btn-outline-primary');
    }
}

// ===== DELETE CONFIRMATION =====
function confirmDelete() {
    const articleTitle = <?php echo json_encode($artikel['judul']); ?>;
    
    if (confirm(`⚠️ PERINGATAN PENGHAPUSAN!\n\nApakah Anda yakin ingin menghapus artikel "${articleTitle}"?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus artikel secara permanen.`)) {
        if (confirm(`Konfirmasi sekali lagi untuk menghapus artikel:\n\n"${articleTitle}"\n\nKlik OK untuk melanjutkan penghapusan.`)) {
            // Show loading state with Bootstrap classes
            const deleteBtn = document.querySelector('button[onclick="confirmDelete()"]');
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';
        
        // Redirect to delete handler
        window.location.href = `hapus_artikel.php?id=<?php echo $id; ?>`;
        }
    }
}

// ===== FORM VALIDATION =====
function validateForm(e) {
    const judul = document.getElementById('judul').value.trim();
    const ringkasan = document.getElementById('ringkasan').value.trim();
    const konten = document.getElementById('konten').value.trim();
    const kategori = document.getElementById('category_enum').value;
    
    const validations = [
        { condition: !judul, message: 'Judul artikel wajib diisi', field: 'judul' },
        { condition: !ringkasan, message: 'Ringkasan artikel wajib diisi', field: 'ringkasan' },
        { condition: !konten, message: 'Konten artikel wajib diisi', field: 'konten' },
        { condition: !kategori, message: 'Kategori artikel wajib dipilih', field: 'category_enum' },
        { condition: judul.length < 5, message: 'Judul artikel minimal 5 karakter', field: 'judul' },
        { condition: ringkasan.length < 20, message: 'Ringkasan artikel minimal 20 karakter', field: 'ringkasan' },
        { condition: konten.length < 50, message: 'Konten artikel minimal 50 karakter', field: 'konten' },
        { condition: judul.length > 255, message: 'Judul artikel maksimal 255 karakter', field: 'judul' },
        { condition: ringkasan.length > 300, message: 'Ringkasan artikel maksimal 300 karakter', field: 'ringkasan' }
    ];
    
    for (const validation of validations) {
        if (validation.condition) {
            e.preventDefault();
            
            // Show Bootstrap alert and add Bootstrap validation classes
            showAlert('danger', `<i class="bi bi-exclamation-triangle me-1"></i>${validation.message}`);
            
            // Add Bootstrap invalid feedback
            const field = document.getElementById(validation.field);
            field.classList.add('is-invalid');
            
            // Remove invalid class after user starts typing
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            }, { once: true });
            
            field.focus();
            return false;
        }
    }
    
    // Show loading state with Bootstrap spinner
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    submitBtn.disabled = true;
    
    // Reset button after 10 seconds as fallback
    setTimeout(() => {
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
    }, 10000);
    
    return true;
}

// ===== HELPER FUNCTIONS =====
function showAlert(type, message) {
    // Remove existing auto-generated alerts
    const existingAlerts = document.querySelectorAll('.alert.auto-dismiss');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new Bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show auto-dismiss`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the form
    const form = document.getElementById('artikel-form');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }
    }, 5000);
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ===== AUTO-SAVE FUNCTIONALITY =====
let autoSaveTimeout;
let lastNotificationTime = 0;
let lastSavedData = null;
const NOTIFICATION_COOLDOWN = 60000; // 1 minute cooldown between notifications

function autoSave(showNotification = false) {
    const formData = {
        judul: document.getElementById('judul').value,
        ringkasan: document.getElementById('ringkasan').value,
        konten: document.getElementById('konten').value,
        category_enum: document.getElementById('category_enum').value,
        status: document.getElementById('status').value,
        timestamp: Date.now()
    };
    
    // Check if data has actually changed since last save
    const currentDataStr = JSON.stringify({
        judul: formData.judul,
        ringkasan: formData.ringkasan,
        konten: formData.konten,
        category_enum: formData.category_enum,
        status: formData.status
    });
    
    if (lastSavedData === currentDataStr) {
        // No changes, don't save
        return false;
    }
    
    // Only save if there's substantial content
    if (formData.judul.length > 5 && formData.konten.length > 50) {
        localStorage.setItem('edit_artikel_draft_<?php echo $id; ?>', JSON.stringify(formData));
        lastSavedData = currentDataStr;
        
        // Only show notification if explicitly requested AND cooldown has passed
        const now = Date.now();
        if (showNotification && (now - lastNotificationTime) > NOTIFICATION_COOLDOWN) {
            showToast('success', 'Draft tersimpan otomatis');
            lastNotificationTime = now;
        }
        
        // Update subtle indicator without notification
        updateAutoSaveIndicator(true);
        return true;
    }
    
    updateAutoSaveIndicator(false);
    return false;
}

// Debounced auto-save for input events
function debouncedAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSave(false); // Don't show notification for input-triggered saves
    }, 2000); // Wait 2 seconds after user stops typing
}

// Update subtle auto-save indicator
function updateAutoSaveIndicator(saved) {
    let indicator = document.getElementById('autosave-indicator');
    
    if (!indicator) {
        // Create indicator if it doesn't exist
        indicator = document.createElement('div');
        indicator.id = 'autosave-indicator';
        indicator.className = 'position-fixed bottom-0 start-50 translate-middle-x p-2 m-3 rounded-pill text-white small';
        indicator.style.zIndex = '1056';
        indicator.style.transition = 'all 0.3s ease';
        indicator.style.transform = 'translateX(-50%) translateY(100%)';
        indicator.style.backdropFilter = 'blur(10px)';
        indicator.style.maxWidth = 'calc(100vw - 40px)';
        indicator.style.textAlign = 'center';
        document.body.appendChild(indicator);
    }
    
    if (saved) {
        const currentTime = new Date().toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        indicator.className = 'position-fixed bottom-0 start-50 translate-middle-x p-2 px-3 m-3 rounded-pill bg-success text-white small opacity-90';
        
        // Responsive text for mobile
        const isMobile = window.innerWidth <= 576;
        indicator.innerHTML = isMobile 
            ? `<i class="bi bi-cloud-check me-1"></i>Tersimpan`
            : `<i class="bi bi-cloud-check me-1"></i>Tersimpan ${currentTime}`;
            
        indicator.style.transform = 'translateX(-50%) translateY(0)';
        
        // Hide after 4 seconds
        setTimeout(() => {
            indicator.style.transform = 'translateX(-50%) translateY(100%)';
        }, 4000);
    } else {
        indicator.style.transform = 'translateX(-50%) translateY(100%)';
    }
}

function loadDraft() {
    const draftKey = 'edit_artikel_draft_<?php echo $id; ?>';
    const savedDraft = localStorage.getItem(draftKey);
    
    if (savedDraft) {
        try {
            const draftData = JSON.parse(savedDraft);
            const oneHour = 60 * 60 * 1000;
            
            // Only load draft if it's less than 1 hour old
            if (Date.now() - draftData.timestamp < oneHour) {
                const currentJudul = document.getElementById('judul').value;
                const currentKonten = document.getElementById('konten').value;
                
                // Only prompt if current form is mostly empty and draft has content
                if (currentJudul.length < 5 && currentKonten.length < 50 && 
                    draftData.judul.length > 5 && draftData.konten.length > 50) {
                    
                    if (confirm('Ditemukan draft yang belum disimpan. Apakah ingin memuat draft tersebut?')) {
                        document.getElementById('judul').value = draftData.judul;
                        document.getElementById('ringkasan').value = draftData.ringkasan;
                        document.getElementById('konten').value = draftData.konten;
                        document.getElementById('category_enum').value = draftData.category_enum;
                        document.getElementById('status').value = draftData.status;
                        
                        // Update character counts
                        updateCharCount('judul', 255);
                        updateCharCount('ringkasan', 300);
                        updateCharCount('konten', null);
                        
                        showAlert('info', '<i class="bi bi-info-circle me-1"></i>Draft berhasil dimuat');
                    }
                }
            } else {
                // Remove old draft
                localStorage.removeItem(draftKey);
            }
        } catch (e) {
            console.error('Error loading draft:', e);
            localStorage.removeItem(draftKey);
        }
    }
}

function showToast(type, message) {
    // Limit concurrent toasts - remove existing auto-save toasts
    const existingToasts = document.querySelectorAll('.toast[data-toast-type="autosave"]');
    existingToasts.forEach(toast => {
        const bsToast = bootstrap.Toast.getInstance(toast);
        if (bsToast) bsToast.hide();
    });
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast with simplified design
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center border-0" role="alert" data-toast-type="autosave">
            <div class="d-flex">
                <div class="toast-body text-bg-${type} rounded">
                    <i class="bi bi-cloud-check me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast with shorter duration
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { 
        delay: 3000,  // 3 seconds instead of 2
        autohide: true 
    });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
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
            element.addEventListener('input', () => {
                updateCharCount(id, max);
                debouncedAutoSave(); // Use debounced auto-save on input
            });
            // Initialize count
            updateCharCount(id, max);
        }
    });
    
    // Form validation
    const form = document.getElementById('artikel-form');
    if (form) {
        form.addEventListener('submit', validateForm);
    }
    
    // Load draft on page load
    loadDraft();
    
    // Show auto-save status after first interaction
    let hasInteracted = false;
    const showAutoSaveStatus = () => {
        if (!hasInteracted) {
            hasInteracted = true;
            const statusIndicator = document.getElementById('header-autosave-status');
            if (statusIndicator) {
                statusIndicator.style.display = 'block';
                // Hide after 5 seconds
                setTimeout(() => {
                    statusIndicator.style.display = 'none';
                }, 5000);
            }
        }
    };
    
    // Add interaction listeners for auto-save status
    formElements.forEach(element => {
        element.addEventListener('input', showAutoSaveStatus, { once: true });
    });
    
    // Auto-save every 2 minutes with notification (less frequent)
    setInterval(() => {
        const saved = autoSave(true); // Show notification for periodic saves
        if (saved) {
            console.log('Periodic auto-save completed');
        }
    }, 120000); // 2 minutes instead of 30 seconds
    
    // Warn before leaving page if form is dirty
    let formChanged = false;
    const formElements = form.querySelectorAll('input, textarea, select');
    formElements.forEach(element => {
        element.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
        }
    });
    
    // Clear warning on form submit
    form.addEventListener('submit', () => {
        formChanged = false;
    });
});

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S for save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const form = document.getElementById('artikel-form');
        if (form && validateForm(new Event('submit'))) {
            form.submit();
        }
    }
    
    // Ctrl/Cmd + P for preview
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        togglePreview();
    }
    
    // Escape to close preview
    if (e.key === 'Escape') {
        const previewSection = document.getElementById('preview-section');
        if (!previewSection.classList.contains('d-none')) {
            togglePreview();
        }
    }
});
</script>

</body>
</html> 