<?php
/**
 * functions.php - Tập hợp các hàm tiện ích cho hệ thống Lộc Phim
 * Đảm bảo mọi hàm đều được kiểm tra tồn tại trước khi khai báo
 */

// Kiểm tra xem file đã được include chưa
if (!defined('LOCPHIM_FUNCTIONS')) {
    define('LOCPHIM_FUNCTIONS', true);

    /**
     * Hàm kiểm tra đăng nhập
     */
    if (!function_exists('check_login')) {
        function check_login() {
            if (!isset($_SESSION['user_id'])) {
                header("Location: " . BASE_URL . "login.php");
                exit();
            }
        }
    }

    /**
     * Hàm kiểm tra quyền admin
     */
    if (!function_exists('is_admin')) {
        function is_admin() {
            return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        }
    }

    /**
     * Làm sạch dữ liệu đầu vào
     */
    if (!function_exists('sanitize')) {
        function sanitize($data) {
            global $db;
            $data = trim($data);
            if (is_object($db) && ($db instanceof mysqli || $db instanceof PDO)) {
                if ($db instanceof mysqli) {
                    $data = $db->real_escape_string($data);
                } else {
                    // Xử lý cho PDO
                    $data = str_replace(
                        ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                        ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                        $data
                    );
                }
            }
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Chuyển hướng trang
     */
    if (!function_exists('redirect')) {
        function redirect($url, $statusCode = 303) {
            header('Location: ' . BASE_URL . $url, true, $statusCode);
            exit();
        }
    }

    /**
     * Kiểm tra email hợp lệ
     */
    if (!function_exists('is_valid_email')) {
        function is_valid_email($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }
    }

    /**
     * Kiểm tra mật khẩu mạnh
     */
    if (!function_exists('is_strong_password')) {
        function is_strong_password($password) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
        }
    }

    /**
     * Hiển thị thông báo lỗi
     */
    if (!function_exists('display_error')) {
        function display_error($message) {
            return '<div class="alert alert-danger">' . sanitize($message) . '</div>';
        }
    }

    /**
     * Hiển thị thông báo thành công
     */
    if (!function_exists('display_success')) {
        function display_success($message) {
            return '<div class="alert alert-success">' . sanitize($message) . '</div>';
        }
    }

    /**
     * Tạo CSRF token
     */
    if (!function_exists('generate_csrf_token')) {
        function generate_csrf_token() {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
    }

    /**
     * Kiểm tra CSRF token
     */
    if (!function_exists('verify_csrf_token')) {
        function verify_csrf_token($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }

    /**
     * Format thời gian
     */
    if (!function_exists('format_date')) {
        function format_date($date, $format = 'd/m/Y H:i') {
            $datetime = new DateTime($date);
            return $datetime->format($format);
        }
    }

    /**
     * Lấy thông tin user
     */
    if (!function_exists('get_user_data')) {
        function get_user_data($user_id) {
            global $db;
            if ($db instanceof mysqli) {
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                return $stmt->get_result()->fetch_assoc();
            } else {
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                return $stmt->fetch();
            }
        }
    }

    /**
     * Upload file an toàn
     */
    if (!function_exists('safe_upload')) {
        function safe_upload($file, $target_dir, $allowed_types = ['jpg', 'png', 'jpeg', 'gif']) {
            // Kiểm tra lỗi upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Lỗi upload file'];
            }

            // Kiểm tra loại file
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_types)) {
                return ['success' => false, 'message' => 'Loại file không được hỗ trợ'];
            }

            // Tạo tên file ngẫu nhiên
            $filename = uniqid() . '.' . $ext;
            $target_path = rtrim($target_dir, '/') . '/' . $filename;

            // Di chuyển file
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                return ['success' => true, 'path' => $target_path];
            }

            return ['success' => false, 'message' => 'Không thể lưu file'];
        }
    }
}