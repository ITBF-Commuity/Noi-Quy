<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Chỉ admin mới được truy cập
check_admin();

// Kiểm tra xem header đã được gửi chưa
if (headers_sent()) {
    die('Header already sent');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Lộc Phim</title>
    
    <!-- CSS chung -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    
    <!-- CSS Admin -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Lộc Phim Admin</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="<?= BASE_URL ?>admin/dashboard.php" class="<?= basename($_SERVER['SCRIPT_NAME']) == 'dashboard.php' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>admin/movies.php" class="<?= basename($_SERVER['SCRIPT_NAME']) == 'movies.php' ? 'active' : '' ?>">
                            <i class="fas fa-film"></i> Quản lý phim
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>admin/users.php" class="<?= basename($_SERVER['SCRIPT_NAME']) == 'users.php' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Quản lý thành viên
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>admin/payments.php" class="<?= basename($_SERVER['SCRIPT_NAME']) == 'payments.php' ? 'active' : '' ?>">
                            <i class="fas fa-money-bill-wave"></i> Quản lý thanh toán
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= BASE_URL ?>index.php" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Về trang chủ
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h3><?= ucfirst(basename($_SERVER['SCRIPT_NAME'], '.php')) ?></h3>
                </div>
                
                <div class="topbar-right">
                    <div class="admin-profile">
                        <img src="<?= !empty($_SESSION['avatar']) ? BASE_URL . $_SESSION['avatar'] : BASE_URL . 'assets/images/default-avatar.jpg' ?>" 
                             alt="Admin Avatar" class="profile-avatar">
                        <span><?= $_SESSION['username'] ?> (Admin)</span>
                    </div>
                </div>
            </header>

            <!-- Content Container -->
            <div class="admin-content-container">