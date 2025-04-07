<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

check_admin();

$db = Database::getInstance()->getPDO();
$message = '';

// Xử lý thêm phim mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    try {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $required_vip = (int)$_POST['required_vip'];
        
        // Upload video
        $video_path = '';
        if (isset($_FILES['video_file']) {
            $upload = safe_upload($_FILES['video_file'], __DIR__ . '/../videos/', ['mp4', 'mkv', 'webm']);
            if (!$upload['success']) {
                throw new Exception($upload['message']);
            }
            $video_path = str_replace(__DIR__ . '/../', '', $upload['path']);
        }
        
        // Upload thumbnail
        $thumbnail_path = '';
        if (isset($_FILES['thumbnail'])) {
            $upload = safe_upload($_FILES['thumbnail'], __DIR__ . '/../assets/images/thumbnails/', ['jpg', 'jpeg', 'png']);
            if (!$upload['success']) {
                throw new Exception($upload['message']);
            }
            $thumbnail_path = str_replace(__DIR__ . '/../', '', $upload['path']);
        }
        
        $db->prepare("INSERT INTO movies (title, description, file_path, thumbnail, required_vip) 
                     VALUES (?, ?, ?, ?, ?)")
           ->execute([$title, $description, $video_path, $thumbnail_path, $required_vip]);
        
        $message = '<div class="alert alert-success">Thêm phim thành công!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Lỗi: ' . $e->getMessage() . '</div>';
    }
}

// Xử lý xóa phim
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $movie = $db->query("SELECT file_path, thumbnail FROM movies WHERE id = $id")->fetch();
    
    if ($movie) {
        // Xóa file video
        if (!empty($movie['file_path']) && file_exists(__DIR__ . '/../' . $movie['file_path'])) {
            unlink(__DIR__ . '/../' . $movie['file_path']);
        }
        
        // Xóa thumbnail
        if (!empty($movie['thumbnail']) && file_exists(__DIR__ . '/../' . $movie['thumbnail'])) {
            unlink(__DIR__ . '/../' . $movie['thumbnail']);
        }
        
        $db->query("DELETE FROM movies WHERE id = $id");
        $message = '<div class="alert alert-success">Đã xóa phim thành công!</div>';
    }
}

// Lấy danh sách phim
$movies = $db->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-container">
    <h1>Quản lý phim</h1>
    
    <?= $message ?>
    
    <div class="admin-row">
        <div class="admin-col-md-6">
            <div class="admin-card">
                <h3>Thêm phim mới</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Tên phim:</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô tả:</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Yêu cầu VIP:</label>
                        <select name="required_vip" required>
                            <option value="0">Free</option>
                            <option value="1">Premium</option>
                            <option value="2">Super Premium</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>File phim (MP4, MKV):</label>
                        <input type="file" name="video_file" accept="video/*" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ảnh thumbnail:</label>
                        <input type="file" name="thumbnail" accept="image/*" required>
                    </div>
                    
                    <button type="submit" name="add_movie" class="btn btn-primary">Thêm phim</button>
                </form>
            </div>
        </div>
        
        <div class="admin-col-md-6">
            <div class="admin-card">
                <h3>Danh sách phim</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên phim</th>
                                <th>VIP</th>
                                <th>Ngày đăng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td><?= $movie['id'] ?></td>
                                <td><?= $movie['title'] ?></td>
                                <td>VIP <?= $movie['required_vip'] ?></td>
                                <td><?= date('d/m/Y', strtotime($movie['created_at'])) ?></td>
                                <td>
                                    <a href="../watch.php?id=<?= $movie['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?= $movie['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';