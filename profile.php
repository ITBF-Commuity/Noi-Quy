<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

check_login();

$db = Database::getInstance()->getPDO();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Lấy thông tin user
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

if (!$user) {
    redirect('logout.php');
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    try {
        $db->beginTransaction();
        
        // Kiểm tra mật khẩu hiện tại nếu có thay đổi
        if (!empty($current_password)) {
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Mật khẩu hiện tại không đúng");
            }
            
            if (!empty($new_password)) {
                if (strlen($new_password) < 8) {
                    throw new Exception("Mật khẩu mới phải có ít nhất 8 ký tự");
                }
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            } else {
                $password_hash = $user['password'];
            }
        } else {
            $password_hash = $user['password'];
        }
        
        // Cập nhật thông tin
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $password_hash, $user_id]);
        
        $db->commit();
        
        // Cập nhật session
        $_SESSION['username'] = $username;
        $success = 'Cập nhật thông tin thành công!';
        
        // Lấy lại thông tin user
        $user = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$user_id])->fetch();
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Xử lý upload avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $result = safe_upload($_FILES['avatar'], __DIR__ . '/uploads/avatars/', ['jpg', 'jpeg', 'png']);
        
        if ($result['success']) {
            // Xóa avatar cũ nếu có
            if (!empty($user['avatar']) && file_exists(__DIR__ . '/' . $user['avatar'])) {
                unlink(__DIR__ . '/' . $user['avatar']);
            }
            
            // Cập nhật database
            $db->prepare("UPDATE users SET avatar = ? WHERE id = ?")
               ->execute([$result['path'], $user_id]);
            
            $success = 'Cập nhật ảnh đại diện thành công!';
            $user['avatar'] = $result['path'];
            $_SESSION['avatar'] = $result['path'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Vui lòng chọn file ảnh hợp lệ';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="avatar-upload">
            <div class="avatar-preview">
                <img src="<?= !empty($user['avatar']) ? BASE_URL . $user['avatar'] : BASE_URL . 'assets/images/default-avatar.jpg' ?>" 
                     alt="Avatar" id="avatar-preview">
            </div>
            <form method="POST" enctype="multipart/form-data" class="avatar-form">
                <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;">
                <button type="button" class="btn-upload" onclick="document.getElementById('avatar-input').click()">
                    Chọn ảnh
                </button>
                <button type="submit" name="upload_avatar" class="btn-save">Lưu ảnh</button>
            </form>
        </div>
        
        <div class="user-stats">
            <h3>Thống kê</h3>
            <ul>
                <li><i class="fas fa-film"></i> Đã xem: <?= $user['watch_count'] ?? 0 ?> phim</li>
                <li><i class="fas fa-clock"></i> Thành viên từ: <?= format_date($user['created_at'], 'd/m/Y') ?></li>
                <li><i class="fas fa-star"></i> Hạng: <?= ucfirst($user['role']) ?></li>
                <li><i class="fas fa-crown"></i> VIP: <?= $user['vip_level'] ?></li>
            </ul>
        </div>
    </div>
    
    <div class="profile-content">
        <h1>Thông tin cá nhân</h1>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="profile-form">
            <div class="form-group">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Mật khẩu hiện tại (để thay đổi thông tin):</label>
                <input type="password" name="current_password">
                <small class="form-text">Nhập mật khẩu hiện tại để xác nhận thay đổi</small>
            </div>
            
            <div class="form-group">
                <label>Mật khẩu mới (nếu muốn đổi):</label>
                <input type="password" name="new_password">
                <small class="form-text">Để trống nếu không muốn thay đổi</small>
            </div>
            
            <button type="submit" name="update_profile" class="btn btn-primary">Cập nhật thông tin</button>
        </form>
        
        <div class="profile-actions">
            <h3>Hành động khác</h3>
            <ul>
                <li><a href="watch-history.php"><i class="fas fa-history"></i> Lịch sử xem phim</a></li>
                <li><a href="watchlist.php"><i class="fas fa-bookmark"></i> Danh sách phim đã lưu</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="admin/"><i class="fas fa-cog"></i> Trang quản trị</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
// Xem trước avatar khi chọn
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('avatar-preview').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php
require __DIR__ . '/includes/footer.php';