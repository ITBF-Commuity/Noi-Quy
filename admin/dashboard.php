<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Kiểm tra quyền admin nghiêm ngặt
if (!Auth::isAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập trang này</p>');
}

$db = Database::getInstance()->getPDO();

// Thống kê tổng quan (dùng Prepared Statement)
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM movies) as total_movies,
        (SELECT COUNT(*) FROM comments) as total_comments,
        (SELECT SUM(amount) FROM payments) as total_revenue,
        (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_users
")->fetch();

// Phim mới nhất
$recent_movies = $db->query("SELECT id, title, created_at FROM movies ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Người dùng mới nhất
$recent_users = $db->query("SELECT id, username, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Include header admin
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-container">
    <h1><i class="fas fa-tachometer-alt"></i> Bảng Điều Khiển</h1>
    
    <div class="stats-grid">
        <!-- [Phần hiển thị thống kê giữ nguyên] -->
    </div>
    
    <div class="admin-row">
        <div class="admin-col">
            <div class="admin-card">
                <h3><i class="fas fa-film"></i> Phim Mới Nhất</h3>
                <!-- [Phần hiển thị phim] -->
            </div>
        </div>
        
        <div class="admin-col">
            <div class="admin-card">
                <h3><i class="fas fa-users"></i> Thành Viên Mới</h3>
                <!-- [Phần hiển thị người dùng] -->
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';