<?php
if (!defined('LOCPHIM_DEBUG')) {
    define('LOCPHIM_DEBUG', false);
}

if (LOCPHIM_DEBUG) {
    function debug_includes() {
        echo '<div style="position:fixed;bottom:0;left:0;background:#fff;z-index:9999;padding:10px;border:1px solid red;">';
        echo '<h3>Include Stack</h3>';
        echo '<pre>';
        print_r(get_included_files());
        echo '</pre>';
        echo '</div>';
    }
    register_shutdown_function('debug_includes');
}

// Kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'locphim');
define('BASE_URL', 'http://localhost/');

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tự động load class
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Include các file cần thiết
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Khởi tạo database
$db = Database::getInstance();

// Cấu hình hệ thống
define('MAX_UPLOAD_SIZE', 500 * 1024 * 1024); // 500MB
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'mkv']);
define('THUMBNAIL_WIDTH', 320);
define('THUMBNAIL_HEIGHT', 180);

// Xử lý lỗi
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: [$errno] $errstr in $errfile on line $errline");
    if (defined('DEBUG') && DEBUG) {
        echo "<div class='error'>$errstr</div>";
    }
});

// Kết nối database
try {
    $db = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USER, 
        DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}