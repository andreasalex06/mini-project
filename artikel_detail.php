<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
require_once 'artikel_functions_enum.php';

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Get article details
$artikel = getArtikelById($pdo, $id);

if (!$artikel) {
    header("Location: index.php");
    exit();
}

// Increment views
incrementViews($pdo, $id);

// Get related articles (same category, excluding current article)
$related_articles = [];
if ($artikel['category_enum']) {
    $related_articles = getRelatedArtikel($pdo, $id, $artikel['category_enum'], 4);
}

// Check if current user can edit this article
$can_edit = is_logged_in() && (get_logged_in_user()['id'] == $artikel['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="<?php echo htmlspecialchars($artikel['ringkasan']); ?> - Artikel di Literaturku">
    <title><?php echo htmlspecialchars($artikel['judul']); ?> - Literaturku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .article-container { max-width: 800px; margin: 0 auto; }
        .share-btn { transition: all 0.2s ease; }
        .share-btn:hover { transform: translateY(-2px); }
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
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Article Content -->
                <article class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <!-- Article Header -->
                        <header class="mb-4">
                            <div class="mb-3">
                                <span class="badge fs-6 py-2 px-3" style="background-color: <?php echo $artikel['kategori_warna']; ?>">
                                    <?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?>
                                </span>
                            </div>
                            
                            <h1 class="display-5 fw-bold text-dark mb-3">
                                <?php echo htmlspecialchars($artikel['judul']); ?>
                            </h1>
                            
                            <p class="lead text-muted mb-4">
                                <?php echo htmlspecialchars($artikel['ringkasan']); ?>
                            </p>
                            
                            <div class="d-flex flex-wrap gap-3 mb-4 text-muted">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person me-2"></i>
                                    <span><?php echo htmlspecialchars($artikel['penulis'] ?? 'Anonymous'); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar me-2"></i>
                                    <span><?php echo formatTanggal($artikel['created_at']); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-eye me-2"></i>
                                    <span><?php echo number_format($artikel['views']); ?> kali dibaca</span>
                                </div>
                            </div>
                            
                            <hr>
                        </header>
                        
                        <!-- Article Body -->
                        <div class="article-content">
                            <div class="fs-5 lh-base text-dark">
                                <?php echo nl2br(htmlspecialchars($artikel['konten'])); ?>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Article Actions -->
                        <footer>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div class="d-flex gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                                    </a>
                                    <?php if ($can_edit): ?>
                                        <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" class="btn btn-primary">
                                            <i class="bi bi-pencil me-2"></i>Edit Artikel
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Terakhir diupdate: <?php echo formatTanggal($artikel['updated_at']); ?>
                                </small>
                            </div>
                        </footer>
                    </div>
                </article>
                
                <!-- Share Section -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-share me-2 text-primary"></i>
                            Bagikan Artikel Ini
                        </h5>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-info share-btn" onclick="shareToTwitter()">
                                <i class="bi bi-twitter me-2"></i>Twitter
                            </button>
                            <button class="btn btn-outline-primary share-btn" onclick="shareToFacebook()">
                                <i class="bi bi-facebook me-2"></i>Facebook
                            </button>
                            <button class="btn btn-outline-success share-btn" onclick="shareToWhatsApp()">
                                <i class="bi bi-whatsapp me-2"></i>WhatsApp
                            </button>
                            <button class="btn btn-outline-secondary share-btn" onclick="copyLink()">
                                <i class="bi bi-clipboard me-2"></i>Salin Link
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Related Articles -->
                <?php if (!empty($related_articles)): ?>
                <section class="mt-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-link-45deg me-2"></i>
                                Artikel Terkait
                            </h5>
                            <small class="opacity-75">
                                Artikel lain dalam kategori <?php echo htmlspecialchars($artikel['kategori_nama']); ?>
                            </small>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <?php foreach ($related_articles as $index => $related): ?>
                                    <div class="col-md-6">
                                        <div class="p-4 <?php echo $index % 2 == 1 ? 'border-start' : ''; ?> <?php echo $index >= 2 ? 'border-top' : ''; ?>">
                                            <div class="mb-2">
                                                <span class="badge" style="background-color: <?php echo $related['kategori_warna']; ?>">
                                                    <?php echo htmlspecialchars($related['kategori_nama']); ?>
                                                </span>
                                            </div>
                                            
                                            <h6 class="fw-bold mb-2">
                                                <a href="artikel_detail.php?id=<?php echo $related['id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($related['judul']); ?>
                                                </a>
                                            </h6>
                                            
                                            <div class="d-flex gap-3 text-muted small">
                                                <span>
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo formatTanggal($related['created_at']); ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-eye me-1"></i>
                                                    <?php echo number_format($related['views']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>
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
// Share functions
function shareToTwitter() {
    const title = <?php echo json_encode($artikel['judul']); ?>;
    const url = window.location.href;
    const text = encodeURIComponent(`Baca artikel menarik: ${title}`);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(url)}`, '_blank');
}

function shareToFacebook() {
    const url = window.location.href;
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
}

function shareToWhatsApp() {
    const title = <?php echo json_encode($artikel['judul']); ?>;
    const url = window.location.href;
    const text = encodeURIComponent(`Baca artikel menarik: ${title} ${url}`);
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function copyLink() {
    const url = window.location.href;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            // Change button text temporarily
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin!';
            btn.style.background = '#28a745';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '#6c757d';
            }, 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        alert('Link berhasil disalin!');
    }
}

// Smooth scroll for related articles
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Reading progress indicator
window.addEventListener('scroll', function() {
    const article = document.querySelector('.article-content');
    if (!article) return;
    
    const articleTop = article.offsetTop;
    const articleHeight = article.offsetHeight;
    const scrollPosition = window.scrollY;
    const windowHeight = window.innerHeight;
    
    const progress = Math.min(
        Math.max((scrollPosition - articleTop + windowHeight) / articleHeight, 0),
        1
    );
    
    // You can use this progress value to show a reading progress bar
    // For now, we'll just log it
    // console.log('Reading progress:', Math.round(progress * 100) + '%');
});
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 