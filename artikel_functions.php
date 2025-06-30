<?php
// Artikel CRUD Functions - LEGACY VERSION
// ⚠️ DEPRECATED: File ini menggunakan sistem kategori lama (kategori_id)
// ✅ GUNAKAN: artikel_functions_enum.php untuk sistem ENUM yang baru
// 
// File ini tetap ada untuk kompatibilitas backward, tapi untuk 
// development baru gunakan artikel_functions_enum.php yang sudah
// disesuaikan dengan database FreeSQLDatabase.com dan struktur ENUM

// Fungsi untuk mendapatkan semua artikel dengan pagination
function getArtikel($pdo, $limit = 10, $offset = 0, $kategori_id = null, $search = null) {
    try {
        $sql = "SELECT a.*, k.nama as kategori_nama, k.warna as kategori_warna, u.username as penulis 
                FROM artikel a 
                LEFT JOIN kategori k ON a.kategori_id = k.id 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'published'";
        
        $params = [];
        
        if ($kategori_id) {
            $sql .= " AND a.kategori_id = ?";
            $params[] = $kategori_id;
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
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error getting artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan total artikel (untuk pagination)
function getTotalArtikel($pdo, $kategori_id = null, $search = null) {
    try {
        $sql = "SELECT COUNT(*) FROM artikel WHERE status = 'published'";
        $params = [];
        
        if ($kategori_id) {
            $sql .= " AND kategori_id = ?";
            $params[] = $kategori_id;
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
        $sql = "SELECT a.*, k.nama as kategori_nama, k.warna as kategori_warna, u.username as penulis 
                FROM artikel a 
                LEFT JOIN kategori k ON a.kategori_id = k.id 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Error getting artikel by id: " . $e->getMessage());
        return null;
    }
}

// Fungsi untuk menambah artikel baru
function tambahArtikel($pdo, $data) {
    try {
        $sql = "INSERT INTO artikel (judul, konten, ringkasan, kategori_id, user_id, gambar, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['judul'],
            $data['konten'],
            $data['ringkasan'],
            $data['kategori_id'],
            $data['user_id'],
            $data['gambar'] ?? null,
            $data['status'] ?? 'published'
        ]);
    } catch(PDOException $e) {
        error_log("Error adding artikel: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk update artikel
function updateArtikel($pdo, $id, $data) {
    try {
        $sql = "UPDATE artikel SET judul = ?, konten = ?, ringkasan = ?, kategori_id = ?, gambar = ?, status = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['judul'],
            $data['konten'],
            $data['ringkasan'],
            $data['kategori_id'],
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
        $sql = "SELECT a.*, k.nama as kategori_nama, k.warna as kategori_warna 
                FROM artikel a 
                LEFT JOIN kategori k ON a.kategori_id = k.id 
                WHERE a.status = 'published' 
                ORDER BY a.views DESC, a.created_at DESC 
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error getting popular artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan artikel terbaru
function getArtikelTerbaru($pdo, $limit = 5) {
    try {
        $sql = "SELECT a.*, k.nama as kategori_nama, k.warna as kategori_warna 
                FROM artikel a 
                LEFT JOIN kategori k ON a.kategori_id = k.id 
                WHERE a.status = 'published' 
                ORDER BY a.created_at DESC 
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error getting latest artikel: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan semua kategori
function getKategori($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error getting kategori: " . $e->getMessage());
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
        
        // Total views
        $stmt = $pdo->query("SELECT SUM(views) FROM artikel WHERE status = 'published'");
        $stats['total_views'] = $stmt->fetchColumn() ?: 0;
        
        // Artikel hari ini
        $stmt = $pdo->query("SELECT COUNT(*) FROM artikel WHERE DATE(created_at) = CURDATE()");
        $stats['today_articles'] = $stmt->fetchColumn();
        
        // Kategori dengan artikel terbanyak
        $stmt = $pdo->query("SELECT k.nama, COUNT(a.id) as total 
                            FROM kategori k 
                            LEFT JOIN artikel a ON k.id = a.kategori_id AND a.status = 'published'
                            GROUP BY k.id, k.nama 
                            ORDER BY total DESC 
                            LIMIT 1");
        $top_category = $stmt->fetch();
        $stats['top_category'] = $top_category ? $top_category['nama'] : 'Belum ada';
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Error getting artikel stats: " . $e->getMessage());
        return [
            'total_published' => 0,
            'total_draft' => 0,
            'total_views' => 0,
            'today_articles' => 0,
            'top_category' => 'Error'
        ];
    }
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    
    return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}

// Fungsi untuk memotong teks
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Fungsi untuk validasi data artikel
function validateArtikelData($data) {
    $errors = [];
    
    if (empty($data['judul'])) {
        $errors[] = "Judul artikel tidak boleh kosong";
    } elseif (strlen($data['judul']) < 5) {
        $errors[] = "Judul artikel minimal 5 karakter";
    } elseif (strlen($data['judul']) > 255) {
        $errors[] = "Judul artikel maksimal 255 karakter";
    }
    
    if (empty($data['konten'])) {
        $errors[] = "Konten artikel tidak boleh kosong";
    } elseif (strlen($data['konten']) < 50) {
        $errors[] = "Konten artikel minimal 50 karakter";
    }
    
    if (empty($data['ringkasan'])) {
        $errors[] = "Ringkasan artikel tidak boleh kosong";
    } elseif (strlen($data['ringkasan']) < 20) {
        $errors[] = "Ringkasan artikel minimal 20 karakter";
    }
    
    if (empty($data['kategori_id'])) {
        $errors[] = "Kategori artikel harus dipilih";
    }
    
    return $errors;
}
?> 