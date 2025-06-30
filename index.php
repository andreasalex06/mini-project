<?php
session_start();
require_once 'auth_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andreas Alex">
    <meta name="description" content="Literaturku - Platform literasi modern untuk menambah dan membagikan literasi kepada dunia">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS for Literaturku -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .hero-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            padding: 4rem 0;
        }
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .btn-custom {
            border-radius: 25px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .search-box {
            border-radius: 25px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .search-box:focus {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .category-badge {
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
    <title>Literaturku - Platform Literasi Modern</title>
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

<!-- Bootstrap Main Content -->
<main>
    <div class="container-fluid px-0">
        <?php include 'beranda.php'; ?>
    </div>
</main>

<!-- Bootstrap Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <?php include 'footer.php'; ?>
    </div>
</footer>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>