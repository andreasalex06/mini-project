<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions_enum.php';

// Proteksi halaman - hanya user yang login bisa akses
require_login();

$current_user = get_logged_in_user();

// Parameters untuk filtering
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'all';
$kategori_filter = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get articles based on user role (user can only see their own articles)
try {
    $artikel_list = getArtikelByUser($pdo, $current_user['id'], $limit, $offset, $kategori_filter, $status_filter, $search);
    
    // Get total count for pagination using the same function logic
    $total_artikel = getTotalArtikelByUser($pdo, $current_user['id'], $kategori_filter, $status_filter, $search);
    $total_pages = ceil($total_artikel / $limit);
    
} catch(Exception $e) {
    error_log("Error getting articles: " . $e->getMessage());
    $artikel_list = [];
    $total_artikel = 0;
    $total_pages = 0;
}

// Get categories for filter
$kategoris = getKategori($pdo);

// Get statistics for current user
$stats = [];
try {
    $user_id = $current_user['id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'published'");
    $stmt->execute([$user_id]);
    $stats['published'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'draft'");
    $stmt->execute([$user_id]);
    $stats['draft'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE user_id = ? AND status = 'archived'");
    $stmt->execute([$user_id]);
    $stats['archived'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT SUM(views) FROM artikel WHERE user_id = ? AND status = 'published'");
    $stmt->execute([$user_id]);
    $stats['total_views'] = $stmt->fetchColumn() ?: 0;
    
} catch(PDOException $e) {
    $stats = ['published' => 0, 'draft' => 0, 'archived' => 0, 'total_views' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Literaturku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
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
    <div class="container">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 mb-1">
                            <i class="bi bi-gear text-primary me-2"></i>
                            Kelola Artikel
                        </h2>
                        <p class="text-muted mb-0">Kelola semua artikel Anda dalam satu tempat dengan mudah dan efisien</p>
                    </div>
                    <a href="tambah_artikel.php" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-2"></i>Tulis Artikel Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['published']; ?></h4>
                        <p class="card-text">Artikel Publikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-pencil fs-1 mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['draft']; ?></h4>
                        <p class="card-text">Draft</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-secondary">
                    <div class="card-body text-center">
                        <i class="bi bi-archive fs-1 mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['archived']; ?></h4>
                        <p class="card-text">Arsip</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="bi bi-eye fs-1 mb-2"></i>
                        <h4 class="card-title"><?php echo number_format($stats['total_views']); ?></h4>
                        <p class="card-text">Total Views</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel me-2"></i>
                            Filter & Pencarian
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari Artikel</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                                       placeholder="Cari judul, konten, atau ringkasan...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Publikasi</option>
                                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Arsip</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($kategoris as $kategori): ?>
                                        <option value="<?php echo $kategori['enum']; ?>" 
                                                <?php echo $kategori_filter == $kategori['enum'] ? 'selected' : ''; ?>>
                                            <?php echo $kategori['icon']; ?> <?php echo htmlspecialchars($kategori['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Articles List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-text me-2"></i>
                            Daftar Artikel
                        </h5>
                        <span class="badge bg-primary">
                            Menampilkan <?php echo count($artikel_list); ?> dari <?php echo $total_artikel; ?> artikel
                        </span>
                    </div>
                    
                    <?php if (empty($artikel_list)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted mb-4"></i>
                            <h4 class="text-muted">Belum Ada Artikel</h4>
                            <p class="text-muted mb-4">
                                <?php if ($search || $status_filter !== 'all' || $kategori_filter): ?>
                                    Tidak ditemukan artikel dengan filter yang dipilih.
                                <?php else: ?>
                                    Anda belum menulis artikel apapun. Mulai dengan menulis artikel pertama Anda!
                                <?php endif; ?>
                            </p>
                            <a href="tambah_artikel.php" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Tulis Artikel Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Artikel</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th class="d-none d-md-table-cell">Views</th>
                                        <th class="d-none d-lg-table-cell">Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($artikel_list as $artikel): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" 
                                                           class="text-decoration-none">
                                                            <?php echo htmlspecialchars($artikel['judul']); ?>
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        <?php echo htmlspecialchars(truncateText($artikel['ringkasan'], 100)); ?>
                                                    </p>
                                                    <div class="d-flex gap-3 text-muted small">
                                                        <span>
                                                            <i class="bi bi-calendar me-1"></i>
                                                            Dibuat: <?php echo formatTanggal($artikel['created_at']); ?>
                                                        </span>
                                                        <span>
                                                            <i class="bi bi-arrow-repeat me-1"></i>
                                                            Diupdate: <?php echo formatTanggal($artikel['updated_at']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $artikel['kategori_warna']; ?>">
                                                    <?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                switch($artikel['status']) {
                                                    case 'published': 
                                                        echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Publikasi</span>'; 
                                                        break;
                                                    case 'draft': 
                                                        echo '<span class="badge bg-warning"><i class="bi bi-pencil me-1"></i>Draft</span>'; 
                                                        break;
                                                    case 'archived': 
                                                        echo '<span class="badge bg-secondary"><i class="bi bi-archive me-1"></i>Arsip</span>'; 
                                                        break;
                                                    default: 
                                                        echo '<span class="badge bg-light text-dark">' . $artikel['status'] . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <strong><?php echo number_format($artikel['views']); ?></strong>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <?php echo date('d/m/Y', strtotime($artikel['updated_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="artikel_detail.php?id=<?php echo $artikel['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" 
                                                       class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button onclick="confirmDelete(<?php echo $artikel['id']; ?>, '<?php echo addslashes($artikel['judul']); ?>')" 
                                                            class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bootstrap Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                        $query_params = [];
                                        if ($search) $query_params['search'] = $search;
                                        if ($status_filter !== 'all') $query_params['status'] = $status_filter;
                                        if ($kategori_filter) $query_params['kategori'] = $kategori_filter;
                                        ?>
                                        
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page-1])); ?>">
                                                    <span aria-hidden="true">&laquo;</span>
                                                    <span class="d-none d-md-inline ms-1">Sebelumnya</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page+1])); ?>">
                                                    <span class="d-none d-md-inline me-1">Selanjutnya</span>
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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
// Konfirmasi hapus artikel dengan double confirmation
function confirmDelete(id, title) {
    const messages = {
        first: `⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus artikel "${title}"?\n\nTindakan ini tidak dapat dibatalkan.`,
        second: 'Konfirmasi sekali lagi untuk menghapus artikel secara permanen.'
    };
    
    if (confirm(messages.first) && confirm(messages.second)) {
        window.location.href = `hapus_artikel.php?id=${id}`;
    }
}

// Auto-submit form saat filter berubah
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const kategoriSelect = document.getElementById('kategori');
    const searchInput = document.getElementById('search');
    
    function hasActiveFilters() {
        return statusSelect.value !== 'all' || kategoriSelect.value || searchInput.value;
    }
    
    function submitForm() {
        if (hasActiveFilters()) {
            statusSelect.form.submit();
        }
    }
    
    statusSelect.addEventListener('change', submitForm);
    kategoriSelect.addEventListener('change', submitForm);
});
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 