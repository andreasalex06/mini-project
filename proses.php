<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Function untuk mengecek dan membuat tabel users jika belum ada
function ensureUsersTable($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            // Tabel users belum ada, buat otomatis
            require_once 'database.php';
            return setupDatabase($pdo);
        }
        return true;
    } catch(PDOException $e) {
        error_log("Error checking users table: " . $e->getMessage());
        return false;
    }
}

// Function untuk mendapatkan semua users (untuk admin)
function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error getting all users: " . $e->getMessage());
        return [];
    }
}

// Function untuk menghapus user (untuk admin)
function deleteUser($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$user_id]);
    } catch(PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}

// Function untuk mendapatkan statistik users
function getUserStats($pdo) {
    try {
        $stats = [];
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = $stmt->fetchColumn();
        
        // Users hari ini
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
        $stats['today_users'] = $stmt->fetchColumn();
        
        // Users minggu ini
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE WEEK(created_at) = WEEK(NOW())");
        $stats['week_users'] = $stmt->fetchColumn();
        
        // Users bulan ini
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(NOW())");
        $stats['month_users'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch(PDOException $e) {
        error_log("Error getting user stats: " . $e->getMessage());
        return [
            'total_users' => 0,
            'today_users' => 0,
            'week_users' => 0,
            'month_users' => 0
        ];
    }
}

// Function untuk mencari users
function searchUsers($search_term, $pdo) {
    try {
        $search_term = "%$search_term%";
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users 
                               WHERE username LIKE ? OR email LIKE ? 
                               ORDER BY created_at DESC");
        $stmt->execute([$search_term, $search_term]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error searching users: " . $e->getMessage());
        return [];
    }
}

// Function untuk integrasi dengan tabel artikel (jika ada)
function getUserArticles($user_id, $pdo) {
    try {
        // Cek apakah tabel artikel ada
        $stmt = $pdo->query("SHOW TABLES LIKE 'artikel'");
        if ($stmt->rowCount() > 0) {
            // Tabel artikel ada, ambil artikel user
            $stmt = $pdo->prepare("SELECT * FROM artikel WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        }
        return [];
    } catch(PDOException $e) {
        error_log("Error getting user articles: " . $e->getMessage());
        return [];
    }
}

// Function untuk mendapatkan semua tabel yang ada
function getDatabaseTables($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        error_log("Error getting database tables: " . $e->getMessage());
        return [];
    }
}

// Processing untuk AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'check_email':
            $email = sanitize_input($_POST['email']);
            $exists = email_exists($email, $pdo);
            echo json_encode(['exists' => $exists]);
            break;
            
        case 'check_username':
            $username = sanitize_input($_POST['username']);
            $exists = username_exists($username, $pdo);
            echo json_encode(['exists' => $exists]);
            break;
            
        case 'get_user_stats':
            if (is_logged_in()) {
                $stats = getUserStats($pdo);
                echo json_encode($stats);
            } else {
                echo json_encode(['error' => 'Unauthorized']);
            }
            break;
            
        case 'search_users':
            if (is_logged_in()) {
                $search_term = sanitize_input($_POST['search_term']);
                $users = searchUsers($search_term, $pdo);
                echo json_encode($users);
            } else {
                echo json_encode(['error' => 'Unauthorized']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit();
}

// Jika diakses langsung tanpa POST, redirect ke index
if ($_SERVER['REQUEST_METHOD'] == 'GET' && basename($_SERVER['PHP_SELF']) == 'proses.php') {
    header("Location: index.php");
    exit();
}
?>
