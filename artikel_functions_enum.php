<?php
/**
 * ARTIKEL CRUD FUNCTIONS - ENUM VERSION
 * Updated to use ENUM category system
 */

require_once 'kategori_definitions.php';

// Fungsi untuk mendapatkan semua artikel dengan pagination (ENUM version)
function getArtikel($pdo, $limit = 10, $offset = 0, $category_enum = null, $search = null) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'published'";
        
        $params = [];
        
        if ($category_enum) {
            $sql .= " AND a.category_enum = ?";
            $params[] = $category_enum;
        }
        
        if ($search) {
            $sql .= " AND (a.judul LIKE ? OR a.konten LIKE ? OR a.ringkasan LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
        
        // Add category info to each article
        foreach ($articles as &$article) {
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $articles;
    } catch(PDOException $e) {
        error_log("Error getting artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan total artikel (untuk pagination)
function getTotalArtikel($pdo, $category_enum = null, $search = null) {
    try {
        $sql = "SELECT COUNT(*) FROM artikel WHERE status = 'published'";
        $params = [];
        
        if ($category_enum) {
            $sql .= " AND category_enum = ?";
            $params[] = $category_enum;
        }
        
        if ($search) {
            $sql .= " AND (judul LIKE ? OR konten LIKE ? OR ringkasan LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log("Error getting total artikel: " . $e->getMessage());
        return 0;
    }
}

// Fungsi untuk mendapatkan artikel berdasarkan ID
function getArtikelById($pdo, $id) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if ($article) {
            // Add category info
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $article;
    } catch(PDOException $e) {
        error_log("Error getting artikel by id: " . $e->getMessage());
        return null;
    }
}

// Fungsi untuk menambah artikel baru
function tambahArtikel($pdo, $data) {
    try {
        // Get current timestamp for created_at
        $created_at = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO artikel (judul, konten, ringkasan, category_enum, user_id, gambar, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['judul'],
            $data['konten'],
            $data['ringkasan'],
            $data['category_enum'] ?? 'umum',
            $data['user_id'],
            $data['gambar'] ?? null,
            $data['status'] ?? 'published',
            $created_at
        ]);
    } catch(PDOException $e) {
        error_log("Error adding artikel: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk update artikel
function updateArtikel($pdo, $id, $data) {
    try {
        $sql = "UPDATE artikel SET judul = ?, konten = ?, ringkasan = ?, category_enum = ?, gambar = ?, status = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['judul'],
            $data['konten'],
            $data['ringkasan'],
            $data['category_enum'] ?? 'umum',
            $data['gambar'] ?? null,
            $data['status'] ?? 'published',
            $id
        ]);
    } catch(PDOException $e) {
        error_log("Error updating artikel: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk hapus artikel
function deleteArtikel($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM artikel WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Error deleting artikel: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk increment views artikel
function incrementViews($pdo, $id) {
    try {
        $stmt = $pdo->prepare("UPDATE artikel SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Error incrementing views: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk mendapatkan artikel populer
function getArtikelPopuler($pdo, $limit = 5) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'published' 
                ORDER BY a.views DESC, a.created_at DESC 
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        $articles = $stmt->fetchAll();
        
        // Add category info to each article
        foreach ($articles as &$article) {
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $articles;
    } catch(PDOException $e) {
        error_log("Error getting popular artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan artikel terbaru
function getArtikelTerbaru($pdo, $limit = 5) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'published' 
                ORDER BY a.created_at DESC 
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        $articles = $stmt->fetchAll();
        
        // Add category info to each article
        foreach ($articles as &$article) {
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $articles;
    } catch(PDOException $e) {
        error_log("Error getting latest artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan semua kategori (ENUM version)
function getKategori($pdo = null) {
    $all_kategori = getAllKategori();
    $result = [];
    
    foreach ($all_kategori as $enum_value => $info) {
        $result[] = [
            'id' => $enum_value,          // Use enum value as ID
            'enum' => $enum_value,        // Original enum value
            'nama' => $info['name'],      // Display name
            'warna' => $info['color'],    // Color for UI
            'icon' => $info['icon']       // Icon for UI
        ];
    }
    
    return $result;
}

// Fungsi untuk mendapatkan artikel per kategori (untuk statistik)
function getArtikelPerKategori($pdo) {
    try {
        $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total 
                            FROM artikel 
                            WHERE status = 'published' 
                            GROUP BY category_enum 
                            ORDER BY total DESC");
        $stats = $stmt->fetchAll();
        
        $result = [];
        foreach ($stats as $stat) {
            $kategori_info = getKategoriInfo($stat['category_enum']);
            $result[] = [
                'enum' => $stat['category_enum'],
                'nama' => $kategori_info['name'],
                'total' => $stat['total'],
                'warna' => $kategori_info['color'],
                'icon' => $kategori_info['icon']
            ];
        }
        
        return $result;
    } catch(PDOException $e) {
        error_log("Error getting artikel per kategori: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan statistik artikel
function getArtikelStats($pdo) {
    try {
        $stats = [];
        
        // Total artikel published
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE status = 'published'");
        $stats['total_published'] = $stmt->fetchColumn();
        
        // Total artikel draft
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE status = 'draft'");
        $stats['total_draft'] = $stmt->fetchColumn();
        
        // Total artikel archived
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE status = 'archived'");
        $stats['total_archived'] = $stmt->fetchColumn();
        
        // Total views
        $stmt = $pdo->query("SELECT SUM(views) FROM artikel WHERE status = 'published'");
        $stats['total_views'] = $stmt->fetchColumn() ?: 0;
        
        // Artikel hari ini
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE DATE(created_at) = CURDATE()");
        $stats['today_articles'] = $stmt->fetchColumn();
        
        // Kategori dengan artikel terbanyak
        $stmt = $pdo->query("SELECT category_enum, COUNT(*) as total 
                            FROM artikel 
                            WHERE status = 'published' 
                            GROUP BY category_enum 
                            ORDER BY total DESC 
                            LIMIT 1");
        $top_category = $stmt->fetch();
        if ($top_category) {
            $kategori_info = getKategoriInfo($top_category['category_enum']);
            $stats['top_category'] = $kategori_info['name'];
            $stats['top_category_count'] = $top_category['total'];
        } else {
            $stats['top_category'] = 'Belum ada';
            $stats['top_category_count'] = 0;
        }
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Error getting artikel stats: " . $e->getMessage());
        return [
            'total_published' => 0,
            'total_draft' => 0,
            'total_archived' => 0,
            'total_views' => 0,
            'today_articles' => 0,
            'top_category' => 'Error',
            'top_category_count' => 0
        ];
    }
}

// Fungsi untuk mencari artikel berdasarkan kategori dan user
function getArtikelByUser($pdo, $user_id, $limit = 10, $offset = 0, $category_enum = null, $status = 'all', $search = null) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.user_id = ?";
        
        $params = [$user_id];
        
        if ($status !== 'all') {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        if ($category_enum) {
            $sql .= " AND a.category_enum = ?";
            $params[] = $category_enum;
        }
        
        if ($search) {
            $sql .= " AND (a.judul LIKE ? OR a.konten LIKE ? OR a.ringkasan LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $sql .= " ORDER BY a.updated_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
        
        // Add category info to each article
        foreach ($articles as &$article) {
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $articles;
    } catch(PDOException $e) {
        error_log("Error getting artikel by user: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $bulan[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}

// Fungsi untuk truncate text
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

// Fungsi untuk validasi data artikel (ENUM version)
function validateArtikelData($data) {
    $errors = [];
    
    // Validasi judul
    if (empty($data['judul']) || strlen(trim($data['judul'])) < 5) {
        $errors[] = "Judul artikel minimal 5 karakter";
    }
    
    // Validasi konten
    if (empty($data['konten']) || strlen(trim($data['konten'])) < 50) {
        $errors[] = "Konten artikel minimal 50 karakter";
    }
    
    // Validasi ringkasan
    if (empty($data['ringkasan']) || strlen(trim($data['ringkasan'])) < 20) {
        $errors[] = "Ringkasan artikel minimal 20 karakter";
    }
    
    // Validasi kategori enum
    $valid_categories = array_keys(getAllKategori());
    if (empty($data['category_enum']) || !in_array($data['category_enum'], $valid_categories)) {
        $errors[] = "Kategori tidak valid";
    }
    
    // Validasi status
    $valid_status = ['draft', 'published', 'archived'];
    if (!empty($data['status']) && !in_array($data['status'], $valid_status)) {
        $errors[] = "Status tidak valid";
    }
    
    return $errors;
}

// Fungsi untuk mendapatkan artikel terkait (same category)
function getRelatedArtikel($pdo, $current_id, $category_enum, $limit = 4) {
    try {
        $sql = "SELECT a.*, u.username as penulis 
                FROM artikel a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.category_enum = ? AND a.id != ? AND a.status = 'published' 
                ORDER BY a.views DESC, a.created_at DESC 
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_enum, $current_id, $limit]);
        $articles = $stmt->fetchAll();
        
        // Add category info to each article
        foreach ($articles as &$article) {
            $kategori_info = getKategoriInfo($article['category_enum']);
            $article['kategori_nama'] = $kategori_info['name'];
            $article['kategori_warna'] = $kategori_info['color'];
            $article['kategori_icon'] = $kategori_info['icon'];
        }
        
        return $articles;
    } catch(PDOException $e) {
        error_log("Error getting related articles: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan total artikel by user (untuk pagination)
function getTotalArtikelByUser($pdo, $user_id, $category_enum = null, $status = 'all', $search = null) {
    try {
        $sql = "SELECT COUNT(*) FROM artikel WHERE user_id = ?";
        $params = [$user_id];
        
        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($category_enum) {
            $sql .= " AND category_enum = ?";
            $params[] = $category_enum;
        }
        
        if ($search) {
            $sql .= " AND (judul LIKE ? OR konten LIKE ? OR ringkasan LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log("Error getting total artikel by user: " . $e->getMessage());
        return 0;
    }
}

// Helper function sanitize_input sudah ada di koneksi.php
?> 