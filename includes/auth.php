<?php
if (!class_exists('Auth')) {
    class Auth {
        /**
         * Kiểm tra đăng nhập
         */
        public static function check() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                header('Location: ' . BASE_URL . 'login.php');
                exit();
            }
        }
        
        /**
         * Kiểm tra quyền admin
         */
        public static function isAdmin() {
            self::check();
            return ($_SESSION['role'] === 'admin');
        }
        
        /**
         * Kiểm tra VIP
         */
        public static function checkVIP($required_level) {
            self::check();
            return ($_SESSION['vip_level'] >= $required_level);
        }
        
        /**
         * Tạo CSRF token
         */
        public static function generateCSRF() {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
        
        /**
         * Xác thực CSRF token
         */
        public static function verifyCSRF($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }
}