-- ================================================
-- DATABASE SETUP FOR BLOG APPLICATION (SIMPLIFIED)
-- ================================================

-- Create database
CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_db;

-- ================================================
-- TABLE: users
-- ================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- ================================================
-- TABLE: kategoris
-- ================================================
CREATE TABLE IF NOT EXISTS kategoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    icon VARCHAR(50) DEFAULT 'bi-folder',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ================================================
-- TABLE: artikels
-- ================================================
CREATE TABLE IF NOT EXISTS artikels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    konten LONGTEXT NOT NULL,
    excerpt TEXT DEFAULT NULL,
    featured_image VARCHAR(255) DEFAULT NULL,
    kategori_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    tags VARCHAR(500) DEFAULT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategoris(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_kategori (kategori_id),
    INDEX idx_user (user_id),
    INDEX idx_published (published_at),
    INDEX idx_featured (is_featured)
);

-- ================================================
-- INSERT DEFAULT DATA
-- ================================================

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@blog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('user', 'user@blog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Demo', 'user');

-- Insert default categories (without 'Hiburan')
INSERT INTO kategoris (nama_kategori, slug, deskripsi, color, icon) VALUES
('Teknologi', 'teknologi', 'Artikel tentang teknologi terbaru', '#007bff', 'bi-cpu'),
('Lifestyle', 'lifestyle', 'Tips dan trik gaya hidup', '#28a745', 'bi-heart'),
('Pendidikan', 'pendidikan', 'Artikel edukatif dan pembelajaran', '#ffc107', 'bi-book'),
('Bisnis', 'bisnis', 'Dunia bisnis dan keuangan', '#dc3545', 'bi-briefcase'),
('Kesehatan', 'kesehatan', 'Tips kesehatan dan kebugaran', '#20c997', 'bi-heart-pulse'),
('Travel', 'travel', 'Destinasi wisata dan petualangan', '#fd7e14', 'bi-geo-alt');

-- Insert sample articles
INSERT INTO artikels (judul, slug, konten, excerpt, kategori_id, user_id, status, published_at) VALUES
('Perkembangan AI di Indonesia', 'perkembangan-ai-di-indonesia', 
'<p>Artificial Intelligence (AI) semakin berkembang pesat di Indonesia...</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>', 
'AI berkembang pesat di Indonesia dengan berbagai implementasi di berbagai sektor.', 
1, 1, 'published', NOW()),

('Tips Hidup Sehat di Era Digital', 'tips-hidup-sehat-era-digital',
'<p>Di era digital ini, menjaga kesehatan menjadi tantangan tersendiri...</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>',
'Panduan lengkap untuk menjaga kesehatan di tengah pesatnya perkembangan teknologi.',
5, 2, 'published', NOW()),

('Strategi Bisnis Digital 2024', 'strategi-bisnis-digital-2024',
'<p>Tahun 2024 menjadi momentum penting untuk transformasi digital...</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>',
'Strategi-strategi efektif untuk mengembangkan bisnis digital di tahun 2024.',
4, 1, 'published', NOW());

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_system) VALUES
('site_title', 'Blog Sederhana', 'text', 'Judul website', FALSE),
('site_description', 'Platform blog sederhana untuk berbagi artikel', 'textarea', 'Deskripsi website', FALSE),
('site_logo', '', 'text', 'URL logo website', FALSE),
('posts_per_page', '10', 'number', 'Jumlah artikel per halaman', FALSE),
('allow_comments', '1', 'boolean', 'Izinkan komentar', FALSE),
('comment_moderation', '1', 'boolean', 'Moderasi komentar', FALSE),
('maintenance_mode', '0', 'boolean', 'Mode maintenance', TRUE),
('timezone', 'Asia/Jakarta', 'text', 'Zona waktu', TRUE);

-- ================================================
-- CREATE INDEXES FOR BETTER PERFORMANCE
-- ================================================

-- Additional indexes for better search performance
CREATE INDEX idx_artikels_title ON artikels(judul);
CREATE INDEX idx_artikels_content ON artikels(konten(255));
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- ================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ================================================

-- View for published articles with category info
CREATE VIEW view_published_articles AS
SELECT 
    a.id,
    a.judul,
    a.slug,
    a.excerpt,
    a.featured_image,
    a.views_count,
    a.is_featured,
    a.published_at,
    k.nama_kategori,
    k.slug as kategori_slug,
    k.color as kategori_color,
    k.icon as kategori_icon,
    u.username,
    u.full_name
FROM artikels a
JOIN kategoris k ON a.kategori_id = k.id
JOIN users u ON a.user_id = u.id
WHERE a.status = 'published'
ORDER BY a.published_at DESC;

-- View for article statistics
CREATE VIEW view_article_stats AS
SELECT 
    COUNT(*) as total_articles,
    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
    SUM(views_count) as total_views,
    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_articles
FROM artikels;

-- ================================================
-- TRIGGERS FOR AUTOMATIC SLUG GENERATION
-- ================================================

-- Function to generate slug (MySQL 8.0+)
-- Note: This is a simplified version, you may want to implement slug generation in PHP

-- ================================================
-- PERMISSIONS AND SECURITY
-- ================================================

-- Create a dedicated user for the application (optional)
-- CREATE USER 'blog_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON blog_db.* TO 'blog_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ================================================
-- BACKUP AND MAINTENANCE PROCEDURES
-- ================================================

-- Regular maintenance query to optimize tables
-- OPTIMIZE TABLE users, kategoris, artikels, comments, sessions, settings;

-- Query to clean old sessions (run periodically)
-- DELETE FROM sessions WHERE last_activity < (UNIX_TIMESTAMP() - 2592000); -- 30 days

-- ================================================
-- USEFUL QUERIES FOR DEVELOPMENT
-- ================================================

-- Get popular articles
-- SELECT judul, views_count FROM artikels WHERE status = 'published' ORDER BY views_count DESC LIMIT 10;

-- Get recent comments
-- SELECT c.konten, c.created_at, a.judul FROM comments c JOIN artikels a ON c.artikel_id = a.id WHERE c.is_approved = 1 ORDER BY c.created_at DESC LIMIT 5;

-- Get user statistics
-- SELECT u.username, COUNT(a.id) as article_count FROM users u LEFT JOIN artikels a ON u.id = a.user_id GROUP BY u.id;

-- Get category statistics
-- SELECT k.nama_kategori, COUNT(a.id) as article_count FROM kategoris k LEFT JOIN artikels a ON k.id = a.kategori_id GROUP BY k.id;

-- ================================================
-- END OF DATABASE SETUP
-- ================================================

-- Display success message
SELECT 'Database berhasil dibuat!' as message; 