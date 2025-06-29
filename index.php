<?php
session_start();
require_once 'auth_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Design System & Components -->
    <link rel="stylesheet" href="/mini-project/css/design-system.css">
    <link rel="stylesheet" href="/mini-project/css/kategori.css">
    <link rel="stylesheet" href="/mini-project/css/style.css">
    <link rel="stylesheet" href="/mini-project/css/beranda.css">
    <title>Literaturku</title>
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

<main>
    <?php include 'beranda.php'; ?>
</main>

<footer>
    <?php include 'footer.php'; ?>
</footer>
    
</body>
</html>