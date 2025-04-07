<?php
require __DIR__ . '/includes/config.php';

// Nếu đã đăng nhập thì chuyển hướng
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự';
    } else {
        try {
            $db = Database::getInstance()->getPDO();
            
            // Kiểm tra username/email đã tồn tại
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Tên đăng nhập hoặc email đã được sử dụng';
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                
                // Tạo user mới
                $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash]);
                
                // Lấy ID user vừa tạo
                $user_id = $db->lastInsertId();
                
                // Tự động đăng nhập
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'member';
                $_SESSION['vip_level'] = 0;
                
                redirect('index.php');
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Đã xảy ra lỗi hệ thống, vui lòng thử lại sau';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Đăng ký tài khoản</h2>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
                <small class="form-text">Ít nhất 8 ký tự</small>
            </div>

            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
            </div>

            <div class="auth-links">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </div>
        </form>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';