<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lộc Phim - Xem phim trực tuyến</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="<?= BASE_URL ?>">Lộc Phim</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
                    <li><a href="<?= BASE_URL ?>movies.php"><i class="fas fa-film"></i> Danh sách phim</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?= BASE_URL ?>profile.php"><i class="fas fa-user"></i> Tài khoản</a></li>
                        <li><a href="<?= BASE_URL ?>logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a></li>
                        <li><a href="<?= BASE_URL ?>register.php"><i class="fas fa-user-plus"></i> Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content">