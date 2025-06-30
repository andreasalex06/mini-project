<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Get categories from ENUM definitions
require_once 'kategori_definitions.php';
if (!isset($kategoris)) {
    require_once 'artikel_functions_enum.php';
    $kategoris = getKategori($pdo);
}

// Get current category filter
$current_kategori = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : null;

// Get article counts per category
$kategori_stats = [];
if (isset($pdo)) {
    try {
        // Total published articles
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE status = 'published'");
        $total_published = $stmt->fetchColumn();
        
        // Articles per category
        $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total 
                            FROM artikel 
                            WHERE status = 'published' 
                            GROUP BY category_enum");
        $category_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($kategoris as &$kategori) {
            $kategori['count'] = $category_counts[$kategori['enum']] ?? 0;
        }
        
        $kategori_stats['total'] = $total_published;
    } catch(PDOException $e) {
        // Fallback if database error
        foreach ($kategoris as &$kategori) {
            $kategori['count'] = 0;
        }
        $kategori_stats['total'] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="Kategori artikel di Literaturku - Platform literasi modern untuk menambah dan membagikan literasi kepada dunia">
    <title>Kategori Artikel - Literaturku</title>
    
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
        
        /* Cards responsive */
        .card {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .card-body {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Text content overflow prevention */
        .card-title, .card-text {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Badge responsive */
        .badge {
            font-weight: 500;
            max-width: 100%;
            word-wrap: break-word;
        }
        
        /* Button responsive */
        .btn {
            max-width: 100%;
            word-wrap: break-word;
        }
        
        .hover-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
        }
        
        /* Responsive fixes for Bootstrap grid */
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        .col-lg-4, .col-lg-6, .col-md-6, .col-md-12, .col-12 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        /* Flex container fixes */
        .d-flex {
            flex-wrap: wrap;
        }
        
        /* Icon and text responsive */
        .fs-1, .fs-4, .fs-5, .fs-6 {
            word-wrap: break-word;
        }
        
        /* Mobile specific fixes */
        @media (max-width: 768px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .col-lg-4, .col-lg-6, .col-md-6, .col-md-12 {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .card-body {
                padding: 1rem !important;
            }
            
            .d-flex.gap-2 {
                gap: 0.5rem !important;
            }
            
            /* Make buttons stack properly */
            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
                align-items: stretch;
            }
            
            .d-flex.flex-wrap.gap-2 .btn {
                margin-bottom: 0.5rem;
                width: 100%;
            }
            
            /* Responsive badges in quick filter */
            .d-flex.flex-wrap.justify-content-center.gap-2 {
                justify-content: flex-start !important;
            }
            
            .btn-sm {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .display-6 {
                font-size: 1.5rem;
            }
            
            .hover-card:hover {
                transform: none;
            }
            
            .card-body {
                padding: 0.75rem !important;
            }
            
            .container {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .col-lg-4, .col-lg-6, .col-md-6 {
                padding-left: 2px;
                padding-right: 2px;
            }
            
            /* Smaller text and spacing for very small screens */
            .fs-5 {
                font-size: 1rem !important;
            }
            
            .mb-5 {
                margin-bottom: 2rem !important;
            }
            
            .mb-4 {
                margin-bottom: 1rem !important;
            }
            
            .p-4 {
                padding: 0.75rem !important;
            }
            
            .p-3 {
                padding: 0.5rem !important;
            }
            
            /* Hide non-essential text on very small screens */
            .d-none.d-sm-inline {
                display: none !important;
            }
            
            /* Stack category icons and text vertically on tiny screens */
            .d-flex.align-items-center {
                flex-direction: column;
                text-align: center;
            }
            
            .d-flex.align-items-center .me-3 {
                margin-right: 0 !important;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            /* Even more compact for very tiny screens */
            .display-6 {
                font-size: 1.25rem;
            }
            
            .card-title {
                font-size: 1rem;
            }
            
            .card-text.small {
                font-size: 0.75rem;
            }
            
            /* Single column layout for stats cards */
            .col-lg-4.col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        /* Utility class to prevent any element from causing horizontal scroll */
        .no-overflow {
            max-width: 100%;
            overflow: hidden;
        }
        
        /* Ensure images are responsive */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Fix for any tables that might be too wide */
        table {
            max-width: 100%;
            table-layout: fixed;
        }
        
        /* Ensure form elements don't overflow */
        input, textarea, select {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Fix for long category names */
        .card-title {
            line-height: 1.2;
            word-break: break-word;
        }
        
        /* Progress bar responsive */
        .progress {
            min-width: 0;
            flex: 1;
        }
        
        /* Badge text wrapping */
        .badge.rounded-pill {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }
        
        /* Quick filter responsive improvements */
        .d-flex.flex-wrap.justify-content-center.gap-2 .btn {
            margin: 0.125rem;
            white-space: nowrap;
        }
        
        /* Prevent icon overflow */
        .bi {
            max-width: 100%;
        }
        
        /* Ensure all content fits */
        * {
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <?php include 'navigasi.php'; ?>

    <!-- Main Content -->
    <main>
        <!-- Category Header -->
        <div class="container my-5">
            <div class="row">
                <div class="col-12">
                    <div class="text-center mb-5">
                        <h1 class="display-6 fw-bold text-dark mb-3">
                            <i class="bi bi-folder2-open text-primary me-2"></i>
                            Kategori Artikel
                        </h1>
                        <p class="text-muted fs-5">Jelajahi artikel berdasarkan kategori favorit Anda</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Summary Cards -->
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card bg-primary text-white h-100 shadow-sm" style="max-width: 100%; overflow: hidden;">
                        <div class="card-body text-center" style="word-wrap: break-word; overflow-wrap: break-word;">
                            <i class="bi bi-collection fs-1 mb-3"></i>
                            <h3 class="card-title"><?php echo count($kategoris); ?></h3>
                            <p class="card-text mb-0">Kategori Tersedia</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card bg-success text-white h-100 shadow-sm" style="max-width: 100%; overflow: hidden;">
                        <div class="card-body text-center" style="word-wrap: break-word; overflow-wrap: break-word;">
                            <i class="bi bi-file-earmark-text fs-1 mb-3"></i>
                            <h3 class="card-title"><?php echo $kategori_stats['total'] ?? 0; ?></h3>
                            <p class="card-text mb-0">Total Artikel</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 mb-3">
                    <?php if (!empty($kategoris) && $kategori_stats['total'] > 0): ?>
                        <?php 
                        // Find most popular category
                        $popular_kategori = array_reduce($kategoris, function($max, $cat) {
                            return (!$max || $cat['count'] > $max['count']) ? $cat : $max;
                        });
                        ?>
                        <div class="card bg-warning text-dark h-100 shadow-sm" style="max-width: 100%; overflow: hidden;">
                            <div class="card-body text-center" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <div class="fs-1 mb-3"><?php echo $popular_kategori['icon']; ?></div>
                                <h5 class="card-title"><?php echo htmlspecialchars($popular_kategori['nama']); ?></h5>
                                <p class="card-text mb-0">Kategori Terpopuler</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card bg-info text-white h-100 shadow-sm" style="max-width: 100%; overflow: hidden;">
                            <div class="card-body text-center" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <i class="bi bi-graph-up fs-1 mb-3"></i>
                                <h5 class="card-title">Mulai Menulis</h5>
                                <p class="card-text mb-0">Jadilah penulis pertama!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category Grid -->
            <div class="row">
                <!-- All Articles Card -->
                <div class="col-lg-6 col-md-6 mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <div class="card h-100 shadow-sm border-0 <?php echo !$current_kategori ? 'border border-primary border-3' : ''; ?> hover-card" style="max-width: 100%; overflow: hidden;">
                            <div class="card-body p-4" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle p-3 me-3">
                                        <i class="bi bi-collection fs-4 text-secondary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1 fw-bold">Semua Artikel</h5>
                                        <p class="card-text text-muted small mb-0">Lihat semua artikel yang tersedia</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary rounded-pill fs-6 px-3 py-2">
                                        <?php echo $kategori_stats['total'] ?? 0; ?> artikel
                                    </span>
                                    <?php if (!$current_kategori): ?>
                                        <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                                    <?php else: ?>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Progress bar for all articles -->
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar bg-secondary" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <?php if (!empty($kategoris)): ?>
                    <?php foreach ($kategoris as $kategori): ?>
                        <div class="col-lg-6 col-md-6 mb-4">
                            <a href="index.php?kategori=<?php echo urlencode($kategori['enum']); ?>" class="text-decoration-none">
                                <div class="card h-100 shadow-sm border-0 <?php echo $current_kategori == $kategori['enum'] ? 'border border-primary border-3' : ''; ?> hover-card" style="max-width: 100%; overflow: hidden;">
                                    <div class="card-body p-4" style="word-wrap: break-word; overflow-wrap: break-word;">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle p-3 me-3" style="background-color: <?php echo $kategori['warna']; ?>15;">
                                                <div class="fs-4" style="color: <?php echo $kategori['warna']; ?>">
                                                    <?php echo $kategori['icon']; ?>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1 fw-bold"><?php echo htmlspecialchars($kategori['nama']); ?></h5>
                                                <p class="card-text text-muted small mb-0" style="word-wrap: break-word; overflow-wrap: break-word;">
                                                    <?php 
                                                    // Generate description based on category
                                                    $descriptions = [
                                                        'teknologi' => 'Inovasi & perkembangan teknologi',
                                                        'pendidikan' => 'Pembelajaran & edukasi',
                                                        'bisnis' => 'Dunia bisnis & ekonomi',
                                                        'kesehatan' => 'Tips kesehatan & wellness',
                                                        'sains' => 'Pengetahuan & penelitian',
                                                        'lifestyle' => 'Gaya hidup & trend',
                                                        'olahraga' => 'Sports & aktivitas fisik',
                                                        'hiburan' => 'Entertainment & media',
                                                        'umum' => 'Artikel umum & beragam'
                                                    ];
                                                    echo $descriptions[$kategori['enum']] ?? 'Artikel ' . $kategori['nama'];
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge rounded-pill fs-6 px-3 py-2" 
                                                  style="background-color: <?php echo $kategori['warna']; ?>; color: white;">
                                                <?php echo $kategori['count']; ?> artikel
                                            </span>
                                            <?php if ($current_kategori == $kategori['enum']): ?>
                                                <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                                            <?php else: ?>
                                                <i class="bi bi-arrow-right text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Progress bar showing relative popularity -->
                                        <?php if ($kategori_stats['total'] > 0): ?>
                                            <div class="progress mt-3" style="height: 4px;">
                                                <div class="progress-bar" 
                                                     style="width: <?php echo ($kategori['count'] / $kategori_stats['total']) * 100; ?>%; background-color: <?php echo $kategori['warna']; ?>">
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="progress mt-3" style="height: 4px;">
                                                <div class="progress-bar bg-light" style="width: 100%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Empty State (if no categories) -->
                <?php if (empty($kategoris)): ?>
                    <div class="col-12">
                        <div class="card border-0 bg-light" style="max-width: 100%; overflow: hidden;">
                            <div class="card-body text-center py-5" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-4 mb-3 text-muted">Belum Ada Kategori</h4>
                                <p class="text-muted mb-4">Kategori akan muncul setelah artikel pertama dipublikasikan</p>
                                <a href="tambah_artikel.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Tulis Artikel Pertama
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Category Filter (Optional Enhancement) -->
            <?php if (!empty($kategoris) && count($kategoris) > 3): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light border-0" style="max-width: 100%; overflow: hidden;">
                            <div class="card-body" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <h5 class="card-title text-center mb-4">
                                    <i class="bi bi-funnel me-2"></i>
                                    Filter Cepat
                                </h5>
                                <div class="d-flex flex-wrap justify-content-center gap-2" style="max-width: 100%; overflow-x: auto;">
                                    <a href="index.php" class="btn <?php echo !$current_kategori ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm" style="white-space: nowrap;">
                                        <i class="bi bi-collection me-1"></i>
                                        <span class="d-none d-md-inline">Semua</span>
                                        <span class="d-inline d-md-none">All</span>
                                    </a>
                                    <?php foreach ($kategoris as $kategori): ?>
                                        <?php if ($kategori['count'] > 0): ?>
                                            <a href="index.php?kategori=<?php echo urlencode($kategori['enum']); ?>" 
                                               class="btn btn-sm <?php echo $current_kategori == $kategori['enum'] ? 'btn-primary' : 'btn-outline-secondary'; ?>" 
                                               style="white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo $kategori['icon']; ?>
                                                <span class="d-none d-sm-inline ms-1"><?php echo htmlspecialchars($kategori['nama']); ?></span>
                                                <span class="badge bg-light text-dark ms-1" style="font-size: 0.7rem;"><?php echo $kategori['count']; ?></span>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Add loading state to category links
            const categoryLinks = document.querySelectorAll('a[href*="kategori="], a[href="index.php"]');
            categoryLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Show loading state
                    const card = this.querySelector('.card');
                    if (card) {
                        card.style.opacity = '0.7';
                        const spinner = document.createElement('div');
                        spinner.className = 'position-absolute top-50 start-50 translate-middle';
                        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                        card.style.position = 'relative';
                        card.appendChild(spinner);
                    }
                });
            });

            // Add animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    }
                });
            }, observerOptions);

            // Observe all cards
            document.querySelectorAll('.card').forEach(card => {
                observer.observe(card);
            });
        });

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

