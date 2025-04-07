<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Chuyển hướng nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$error = '';
$db = Database::getInstance()->getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xác thực CSRF
    if (!Auth::verifyCSRF($_POST['csrf_token'])) {
        $error = 'Lỗi bảo mật CSRF token!';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        try {
            $stmt = $db->prepare("SELECT id, username, password, role, vip_level FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Tạo session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['vip_level'] = $user['vip_level'];
                
                // Cập nhật last login
                $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                   ->execute([$user['id']]);
                
                // Chuyển hướng
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                header('Location: ' . BASE_URL . $redirect);
                exit();
            } else {
                $error = 'Thông tin đăng nhập không chính xác';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Lỗi hệ thống, vui lòng thử lại sau';
        }
    }
}

// Tạo CSRF token mới
$csrf_token = Auth::generateCSRF();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập - Lộc Phim</title>
    <?php include __DIR__ . '/includes/header.php'; ?>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2><i class="fas fa-sign-in-alt"></i> Đăng Nhập</h2>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Tên đăng nhập</label>
                    <input type="text" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mật khẩu</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
                
                <div class="auth-links">
                    <a href="forgot-password.php"><i class="fas fa-question-circle"></i> Quên mật khẩu?</a>
                    <a href="register.php"><i class="fas fa-user-plus"></i> Đăng ký tài khoản</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>