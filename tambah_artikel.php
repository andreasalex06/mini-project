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
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="Tulis artikel baru di Literaturku - Platform literasi modern untuk menambah dan membagikan literasi kepada dunia">
    <title>Tulis Artikel - Literaturku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-container { max-width: 800px; margin: 0 auto; }
        .character-count.warning { color: #ffc107; font-weight: 500; }
        .character-count.danger { color: #dc3545; font-weight: 500; }
        
        /* Preview styling */
        #preview-section {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
        #preview-section:hover {
            border-color: #adb5bd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .preview-content h2 {
            color: #212529;
            line-height: 1.2;
        }
        
        .preview-content .lead {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .preview-content p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        
        /* Responsive preview */
        @media (max-width: 768px) {
            .preview-content h2 {
                font-size: 1.5rem;
            }
            
            .preview-content .lead {
                font-size: 1rem;
            }
        }
        
        /* Keyboard shortcut styling */
        kbd {
            padding: 0.2rem 0.4rem;
            font-size: 87.5%;
            background-color: #212529;
            border-radius: 0.2rem;
        }
        
        /* Loading state */
        .loading {
            position: relative;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Form validation */
        .validation-alert {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-light">

<!-- Bootstrap Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand text-primary d-flex align-items-center" href="index.php">
            <i class="bi bi-book-fill me-2 fs-3"></i>
            <span>Literaturku</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="ms-auto">
                <?php include 'navigasi.php'; ?>
            </div>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container form-container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="card-title mb-1">
                            <i class="bi bi-pencil-square me-2"></i>
                            Tulis Artikel Baru
                        </h2>
                        <p class="card-text mb-0 opacity-75">Bagikan pengetahuan dan pengalaman Anda dengan dunia</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
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
                            <div class="mb-4">
                                <label for="judul" class="form-label">
                                    <i class="bi bi-pencil me-2"></i>Judul Artikel *
                                </label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo isset($old_data['judul']) ? htmlspecialchars($old_data['judul']) : ''; ?>" 
                                       placeholder="Masukkan judul artikel yang menarik..." 
                                       maxlength="255" required>
                                <div class="form-text character-count" id="judul-count">0/255 karakter</div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="category_enum" class="form-label">
                                        <i class="bi bi-folder me-2"></i>Kategori *
                                    </label>
                                    <select class="form-select" id="category_enum" name="category_enum" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($kategoris as $kategori): ?>
                                            <option value="<?php echo $kategori['enum']; ?>" 
                                                    <?php echo (isset($old_data['category_enum']) && $old_data['category_enum'] == $kategori['enum']) ? 'selected' : ''; ?>>
                                                <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">
                                        <i class="bi bi-bar-chart me-2"></i>Status Publikasi
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="published" <?php echo (isset($old_data['status']) && $old_data['status'] == 'published') ? 'selected' : ''; ?>>
                                            <i class="bi bi-check-circle me-1"></i>Publikasikan Langsung
                                        </option>
                                        <option value="draft" <?php echo (isset($old_data['status']) && $old_data['status'] == 'draft') ? 'selected' : ''; ?>>
                                            <i class="bi bi-pencil me-1"></i>Simpan sebagai Draft
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="ringkasan" class="form-label">
                                    <i class="bi bi-card-text me-2"></i>Ringkasan Artikel *
                                </label>
                                <textarea class="form-control" id="ringkasan" name="ringkasan" rows="3"
                                          placeholder="Tulis ringkasan singkat artikel (akan ditampilkan di halaman utama)..." 
                                          maxlength="300" required><?php echo isset($old_data['ringkasan']) ? htmlspecialchars($old_data['ringkasan']) : ''; ?></textarea>
                                <div class="form-text character-count" id="ringkasan-count">0/300 karakter</div>
                            </div>

                            <div class="mb-4">
                                <label for="konten" class="form-label">
                                    <i class="bi bi-file-text me-2"></i>Konten Artikel *
                                </label>
                                <textarea class="form-control" id="konten" name="konten" rows="12"
                                          placeholder="Tulis konten artikel lengkap di sini..." 
                                          required><?php echo isset($old_data['konten']) ? htmlspecialchars($old_data['konten']) : ''; ?></textarea>
                                <div class="form-text character-count" id="konten-count">0 karakter</div>
                                <div class="form-text text-info" id="word-count">0 kata • Estimasi waktu baca: 0 menit</div>
                            </div>

                            <div class="card bg-light mb-4" id="preview-section" style="display: none;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-eye me-2"></i>Preview Artikel
                                    </h5>
                                </div>
                                <div class="card-body" id="preview-content">
                                    <!-- Preview content will be inserted here -->
                                </div>
                            </div>

                            <div class="d-flex justify-content-between flex-wrap gap-3">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-house me-2"></i>Kembali ke Beranda
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" 
                                            class="btn btn-outline-info" 
                                            onclick="togglePreview()"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Ctrl/Cmd + P untuk preview cepat">
                                        <i class="bi bi-eye me-2"></i>Preview
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-success" 
                                            onclick="autoSave(); showValidationAlert('Draft disimpan!', 'success')"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Ctrl/Cmd + S untuk simpan otomatis">
                                        <i class="bi bi-save me-2"></i>Simpan Draft
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Publikasikan Artikel
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Help Text -->
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Tips: Gunakan <kbd>Ctrl/Cmd + S</kbd> untuk menyimpan draft, <kbd>Ctrl/Cmd + P</kbd> untuk preview
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <?php include 'footer.php'; ?>
    </div>
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
    updateWordCount();
});

// Word count and reading time estimation
function updateWordCount() {
    const content = document.getElementById('konten').value;
    const wordCountElement = document.getElementById('word-count');
    
    // Count words (split by whitespace and filter empty strings)
    const words = content.trim().split(/\s+/).filter(word => word.length > 0);
    const wordCount = content.trim() === '' ? 0 : words.length;
    
    // Estimate reading time (average 200 words per minute)
    const readingTime = Math.ceil(wordCount / 200);
    
    // Update display
    if (wordCount === 0) {
        wordCountElement.textContent = '0 kata • Estimasi waktu baca: 0 menit';
        wordCountElement.className = 'form-text text-muted';
    } else if (wordCount < 50) {
        wordCountElement.textContent = `${wordCount} kata • Terlalu pendek untuk artikel`;
        wordCountElement.className = 'form-text text-warning';
    } else if (wordCount < 100) {
        wordCountElement.textContent = `${wordCount} kata • Artikel pendek • ${readingTime} menit baca`;
        wordCountElement.className = 'form-text text-info';
    } else if (wordCount < 500) {
        wordCountElement.textContent = `${wordCount} kata • Artikel sedang • ${readingTime} menit baca`;
        wordCountElement.className = 'form-text text-success';
    } else {
        wordCountElement.textContent = `${wordCount} kata • Artikel panjang • ${readingTime} menit baca`;
        wordCountElement.className = 'form-text text-primary';
    }
}

// Preview functionality
function togglePreview() {
    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    const previewBtn = document.querySelector('button[onclick="togglePreview()"]');
    
    if (previewSection.style.display === 'none' || previewSection.style.display === '') {
        // Show preview
        const judul = document.getElementById('judul').value;
        const ringkasan = document.getElementById('ringkasan').value;
        const konten = document.getElementById('konten').value;
        const kategoriSelect = document.getElementById('category_enum');
        const kategoriNama = kategoriSelect.options[kategoriSelect.selectedIndex].text;
        const statusSelect = document.getElementById('status');
        const statusNama = statusSelect.options[statusSelect.selectedIndex].text;
        
        // Get kategori color if available
        let kategoriColor = '#6c757d'; // default gray
        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
        if (selectedOption.value) {
            // You can add logic here to get color based on category if needed
            kategoriColor = getKategoriColor(selectedOption.value);
        }
        
        previewContent.innerHTML = `
            <div class="preview-content">
                <!-- Article Header -->
                <div class="mb-3">
                    <span class="badge fs-6 py-2 px-3" style="background-color: ${kategoriColor}; color: white;">
                        ${kategoriNama !== 'Pilih Kategori' ? kategoriNama : 'Kategori Belum Dipilih'}
                    </span>
                </div>
                
                <!-- Article Title -->
                <h2 class="display-6 fw-bold text-dark mb-3">
                    ${judul || 'Judul Artikel'}
                </h2>
                
                <!-- Article Summary -->
                <p class="lead text-muted mb-4">
                    ${ringkasan || 'Ringkasan artikel...'}
                </p>
                
                <!-- Article Meta -->
                <div class="d-flex flex-wrap gap-3 mb-4 text-muted border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person me-2"></i>
                        <span>${<?php echo json_encode($current_user['username']); ?>}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar me-2"></i>
                        <span>${new Date().toLocaleDateString('id-ID', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</span>
                    </div>
                                                         <div class="d-flex align-items-center">
                                         <i class="bi bi-eye me-2"></i>
                                         <span>0 kali dibaca</span>
                                     </div>
                                     <div class="d-flex align-items-center">
                                         <i class="bi bi-clock me-2"></i>
                                         <span>${getReadingTime(konten)} menit baca</span>
                                     </div>
                                     <div class="d-flex align-items-center">
                                         <i class="bi bi-bookmark me-2"></i>
                                         <span>${statusNama}</span>
                                     </div>
                </div>
                
                <!-- Article Content -->
                <div class="fs-5 lh-base text-dark">
                    ${konten ? formatPreviewContent(konten) : '<em class="text-muted">Konten artikel...</em>'}
                </div>
            </div>
        `;
        
        previewSection.style.display = 'block';
        previewBtn.innerHTML = '<i class="bi bi-eye-slash me-2"></i>Tutup Preview';
        previewBtn.classList.remove('btn-outline-info');
        previewBtn.classList.add('btn-outline-danger');
        
        // Scroll to preview
        previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Hide preview
        previewSection.style.display = 'none';
        previewBtn.innerHTML = '<i class="bi bi-eye me-2"></i>Preview';
        previewBtn.classList.remove('btn-outline-danger');
        previewBtn.classList.add('btn-outline-info');
    }
}

// Helper function to get kategori color
function getKategoriColor(categoryEnum) {
    const colors = {
        'teknologi': '#007bff',
        'sains': '#28a745', 
        'sejarah': '#ffc107',
        'budaya': '#fd7e14',
        'politik': '#dc3545',
        'ekonomi': '#6f42c1',
        'sosial': '#20c997',
        'pendidikan': '#6c757d',
        'kesehatan': '#e83e8c',
        'olahraga': '#17a2b8'
    };
    return colors[categoryEnum] || '#6c757d';
}

// Helper function to format preview content
function formatPreviewContent(content) {
    return content
        .replace(/\n\n/g, '</p><p class="mb-3">')  // Double line breaks = new paragraph
        .replace(/\n/g, '<br>')  // Single line breaks = <br>
        .replace(/^/, '<p class="mb-3">')  // Start with paragraph
        .replace(/$/, '</p>');  // End with paragraph
}

// Helper function to get reading time for preview
function getReadingTime(content) {
    const words = content.trim().split(/\s+/).filter(word => word.length > 0);
    const wordCount = content.trim() === '' ? 0 : words.length;
    return Math.max(1, Math.ceil(wordCount / 200));
}

// Form validation
document.getElementById('artikel-form').addEventListener('submit', function(e) {
    const judul = document.getElementById('judul').value.trim();
    const ringkasan = document.getElementById('ringkasan').value.trim();
    const konten = document.getElementById('konten').value.trim();
    const kategori = document.getElementById('category_enum').value;
    
    // Check required fields
    if (!judul || !ringkasan || !konten || !kategori) {
        e.preventDefault();
        showValidationAlert('Mohon lengkapi semua field yang wajib diisi (*)', 'warning');
        return;
    }
    
    // Validate field lengths
    if (judul.length < 5) {
        e.preventDefault();
        showValidationAlert('Judul artikel minimal 5 karakter', 'warning');
        document.getElementById('judul').focus();
        return;
    }
    
    if (ringkasan.length < 20) {
        e.preventDefault();
        showValidationAlert('Ringkasan artikel minimal 20 karakter', 'warning');
        document.getElementById('ringkasan').focus();
        return;
    }
    
    if (konten.length < 50) {
        e.preventDefault();
        showValidationAlert('Konten artikel minimal 50 karakter', 'warning');
        document.getElementById('konten').focus();
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    // Reset if form validation fails
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.innerHTML = originalText;
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    }, 5000);
});

// Helper function to show validation alerts
function showValidationAlert(message, type = 'danger') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.validation-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create new alert
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show validation-alert" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the form
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.validation-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
    
    // Scroll to alert
    document.querySelector('.validation-alert').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Real-time preview update
function updatePreviewIfVisible() {
    const previewSection = document.getElementById('preview-section');
    if (previewSection.style.display === 'block') {
        togglePreview(); // Hide first
        togglePreview(); // Then show with updated content
    }
}

// Auto-save to localStorage
function autoSave() {
    const formData = {
        judul: document.getElementById('judul').value,
        ringkasan: document.getElementById('ringkasan').value,
        konten: document.getElementById('konten').value,
        category_enum: document.getElementById('category_enum').value,
        status: document.getElementById('status').value,
        timestamp: Date.now()
    };
    localStorage.setItem('artikel_draft', JSON.stringify(formData));
}

// Load from localStorage
function loadDraft() {
    const saved = localStorage.getItem('artikel_draft');
    if (saved) {
        const data = JSON.parse(saved);
        // Only load if saved within last hour
        if (Date.now() - data.timestamp < 3600000) {
            if (confirm('Ditemukan draft artikel yang belum disimpan. Apakah Anda ingin melanjutkan menulis?')) {
                document.getElementById('judul').value = data.judul || '';
                document.getElementById('ringkasan').value = data.ringkasan || '';
                document.getElementById('konten').value = data.konten || '';
                document.getElementById('category_enum').value = data.category_enum || '';
                document.getElementById('status').value = data.status || 'published';
                
                // Update character counts
                updateCharCount('judul', 255);
                updateCharCount('ringkasan', 300);
                updateCharCount('konten');
                updateWordCount();
                
                // Show success message
                showValidationAlert('Draft berhasil dimuat!', 'success');
            }
        }
    }
}

// Clear draft after successful submit
function clearDraft() {
    localStorage.removeItem('artikel_draft');
}

// Enhanced form submit handler
const originalSubmitHandler = document.getElementById('artikel-form').onsubmit;

// Keyboard shortcuts
function handleKeyboardShortcuts(e) {
    // Ctrl/Cmd + S = Auto save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        autoSave();
        showValidationAlert('Draft disimpan secara otomatis!', 'info');
    }
    
    // Ctrl/Cmd + P = Preview
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        togglePreview();
    }
}

// Initialize character counts and setup auto-save on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCharCount('judul', 255);
    updateCharCount('ringkasan', 300);
    updateCharCount('konten');
    updateWordCount();
    
    // Load draft if available
    loadDraft();
    
    // Setup auto-save on input change
    const inputs = ['judul', 'ringkasan', 'konten', 'category_enum', 'status'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        element.addEventListener('input', () => {
            autoSave();
            // Update preview if visible
            if (id !== 'status') {
                updatePreviewIfVisible();
            }
            // Update word count for konten
            if (id === 'konten') {
                updateWordCount();
            }
        });
        element.addEventListener('change', autoSave);
    });
    
         // Setup keyboard shortcuts
    document.addEventListener('keydown', handleKeyboardShortcuts);
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Clear draft on successful form submission
    document.getElementById('artikel-form').addEventListener('submit', function(e) {
        // If form passes validation, clear the draft
        const judul = document.getElementById('judul').value.trim();
        const ringkasan = document.getElementById('ringkasan').value.trim();
        const konten = document.getElementById('konten').value.trim();
        const kategori = document.getElementById('category_enum').value;
        
        if (judul && ringkasan && konten && kategori && 
            judul.length >= 5 && ringkasan.length >= 20 && konten.length >= 50) {
            clearDraft();
        }
    });
    
    // Warn before leaving if unsaved changes
    window.addEventListener('beforeunload', function(e) {
        const saved = localStorage.getItem('artikel_draft');
        if (saved) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 