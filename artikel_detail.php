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
    <title><?php echo htmlspecialchars($artikel['judul']); ?> - Literaturku</title>
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <style>
        .article-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .article-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .article-kategori {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .article-title {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }
        
        .article-summary {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .article-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .article-content {
            padding: 2.5rem;
        }
        
        .article-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 2rem;
        }
        
        .article-body p {
            margin-bottom: 1.5rem;
        }
        
        .article-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2.5rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-back {
            background: #10367d;
            color: white;
        }
        
        .btn-back:hover {
            background: #0f2a5f;
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        
        .btn-edit:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
        
        .article-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        .related-articles {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .related-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .related-header h3 {
            color: #10367d;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .related-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(16, 54, 125, 0.1);
        }
        
        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .related-kategori {
            background: #10367d;
            color: white;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .related-content {
            padding: 1.2rem;
        }
        
        .related-title {
            color: #10367d;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.8rem;
        }
        
        .related-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .related-title a:hover {
            color: #667eea;
        }
        
        .related-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #888;
        }
        
        .share-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem auto;
            max-width: 800px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .share-header {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .share-twitter {
            background: #1da1f2;
            color: white;
        }
        
        .share-facebook {
            background: #4267b2;
            color: white;
        }
        
        .share-whatsapp {
            background: #25d366;
            color: white;
        }
        
        .share-copy {
            background: #6c757d;
            color: white;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .article-container {
                margin: 1rem;
                border-radius: 12px;
            }
            
            .article-header {
                padding: 1.5rem;
            }
            
            .article-title {
                font-size: 1.8rem;
            }
            
            .article-content {
                padding: 1.5rem;
            }
            
            .article-actions {
                padding: 1rem 1.5rem;
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .article-meta {
                gap: 1rem;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
            
            .share-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .share-btn {
                width: 200px;
                justify-content: center;
            }
        }
    </style>
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

<main style="min-height: calc(100vh - 200px); background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 2rem 1rem;">
    
    <!-- Article Content -->
    <article class="article-container">
        <header class="article-header">
            <div class="article-kategori" style="background-color: <?php echo $artikel['kategori_warna']; ?>">
                <?php echo htmlspecialchars($artikel['kategori_nama'] ?? 'Umum'); ?>
            </div>
            
            <h1 class="article-title">
                <?php echo htmlspecialchars($artikel['judul']); ?>
            </h1>
            
            <p class="article-summary">
                <?php echo htmlspecialchars($artikel['ringkasan']); ?>
            </p>
            
            <div class="article-meta">
                <div class="meta-item">
                    <span>👤</span>
                    <span><?php echo htmlspecialchars($artikel['penulis'] ?? 'Anonymous'); ?></span>
                </div>
                <div class="meta-item">
                    <span>📅</span>
                    <span><?php echo formatTanggal($artikel['created_at']); ?></span>
                </div>
                <div class="meta-item">
                    <span>👁️</span>
                    <span><?php echo number_format($artikel['views']); ?> kali dibaca</span>
                </div>
            </div>
        </header>
        
        <div class="article-content">
            <div class="article-body">
                <?php echo nl2br(htmlspecialchars($artikel['konten'])); ?>
            </div>
        </div>
        
        <footer class="article-actions">
            <div class="action-buttons">
                <a href="index.php" class="btn btn-back">← Kembali ke Beranda</a>
                <?php if ($can_edit): ?>
                    <a href="edit_artikel.php?id=<?php echo $artikel['id']; ?>" class="btn btn-edit">✏️ Edit Artikel</a>
                <?php endif; ?>
            </div>
            
            <div class="article-stats">
                <span>🔄 Terakhir diupdate: <?php echo formatTanggal($artikel['updated_at']); ?></span>
            </div>
        </footer>
    </article>
    
    <!-- Share Section -->
    <div class="share-section">
        <div class="share-header">
            <h4>📤 Bagikan Artikel Ini</h4>
        </div>
        <div class="share-buttons">
            <a href="#" class="share-btn share-twitter" onclick="shareToTwitter()">
                🐦 Twitter
            </a>
            <a href="#" class="share-btn share-facebook" onclick="shareToFacebook()">
                📘 Facebook
            </a>
            <a href="#" class="share-btn share-whatsapp" onclick="shareToWhatsApp()">
                💬 WhatsApp
            </a>
            <a href="#" class="share-btn share-copy" onclick="copyLink()">
                📋 Salin Link
            </a>
        </div>
    </div>
    
    <!-- Related Articles -->
    <?php if (!empty($related_articles)): ?>
    <section class="related-articles">
        <div class="related-header">
            <h3>🔗 Artikel Terkait</h3>
            <p>Artikel lain dalam kategori <?php echo htmlspecialchars($artikel['kategori_nama']); ?></p>
        </div>
        
        <div class="related-grid">
            <?php foreach ($related_articles as $related): ?>
                <article class="related-card">
                    <div class="related-kategori" style="background-color: <?php echo $related['kategori_warna']; ?>">
                        <?php echo htmlspecialchars($related['kategori_nama']); ?>
                    </div>
                    
                    <div class="related-content">
                        <h4 class="related-title">
                            <a href="artikel_detail.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['judul']); ?>
                            </a>
                        </h4>
                        
                        <div class="related-meta">
                            <span>📅 <?php echo formatTanggal($related['created_at']); ?></span>
                            <span>👁️ <?php echo number_format($related['views']); ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<footer>
    <?php include 'footer.php'; ?>
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

</body>
</html> 