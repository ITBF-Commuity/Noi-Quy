<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

check_admin();

$db = Database::getInstance()->getPDO();
$message = '';

// Xử lý cập nhật role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = sanitize($_POST['new_role']);
    
    $db->prepare("UPDATE users SET role = ? WHERE id = ?")
       ->execute([$new_role, $user_id]);
    
    $message = '<div class="alert alert-success">Cập nhật vai trò thành công!</div>';
}

// Xử lý xóa user
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Không cho xóa admin chính
    if ($user_id !== $_SESSION['user_id']) {
        $db->query("DELETE FROM users WHERE id = $user_id");
        $message = '<div class="alert alert-success">Đã xóa người dùng thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">Không thể xóa admin đang đăng nhập!</div>';
    }
}

// Lấy danh sách users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-container">
    <h1>Quản lý thành viên</h1>
    
    <?= $message ?>
    
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>VIP</th>
                        <th>Ngày đăng ký</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['username'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="new_role" class="form-control" onchange="this.form.submit()">
                                    <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                    <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <noscript><button type="submit" name="update_role" class="btn btn-sm">Save</button></noscript>
                            </form>
                        </td>
                        <td><?= $user['vip_level'] ?></td>
                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';