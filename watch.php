<?php
require __DIR__ . '/includes/config.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$movie_id = (int)$_GET['id'];
$db = Database::getInstance()->getPDO();

// Lấy thông tin phim
$movie = $db->prepare("SELECT m.*, 
                      (SELECT COUNT(*) FROM comments c WHERE c.movie_id = m.id) as comment_count,
                      (SELECT COUNT(*) FROM watch_history wh WHERE wh.movie_id = m.id) as view_count
                      FROM movies m WHERE m.id = ?");
$movie->execute([$movie_id]);
$movie = $movie->fetch();

if (!$movie) {
    redirect('index.php');
}

// Kiểm tra quyền xem phim
if (isset($_SESSION['user_id'])) {
    $user_vip = $_SESSION['vip_level'];
} else {
    $user_vip = 0;
}

if ($movie['required_vip'] > $user_vip) {
    $_SESSION['redirect_url'] = "watch.php?id=$movie_id";
    redirect('vip.php');
}

// Ghi nhận lượt xem
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra xem đã xem chưa
    $stmt = $db->prepare("SELECT 1 FROM watch_history WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$user_id, $movie_id]);
    
    if ($stmt->rowCount() > 0) {
        // Cập nhật thời gian xem
        $db->prepare("UPDATE watch_history SET last_watched = NOW() WHERE user_id = ? AND movie_id = ?")
           ->execute([$user_id, $movie_id]);
    } else {
        // Thêm mới vào lịch sử
        $db->prepare("INSERT INTO watch_history (user_id, movie_id) VALUES (?, ?)")
           ->execute([$user_id, $movie_id]);
    }
    
    // Cập nhật tổng lượt xem
    $db->query("UPDATE movies SET view_count = view_count + 1 WHERE id = $movie_id");
}

// Xử lý bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = sanitize($_POST['comment']);
    
    $db->prepare("INSERT INTO comments (user_id, movie_id, content) VALUES (?, ?, ?)")
       ->execute([$_SESSION['user_id'], $movie_id, $comment]);
}

// Lấy bình luận
$comments = $db->prepare("SELECT c.*, u.username, u.avatar 
                         FROM comments c
                         JOIN users u ON c.user_id = u.id
                         WHERE c.movie_id = ?
                         ORDER BY c.created_at DESC");
$comments->execute([$movie_id]);
$comments = $comments->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="watch-container">
    <div class="video-player">
        <video id="main-video" controls>
            <source src="<?= $movie['file_path'] ?>" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video HTML5
        </video>
    </div>

    <div class="video-info">
        <h1><?= $movie['title'] ?></h1>
        
        <div class="video-meta">
            <span class="view-count"><i class="fas fa-eye"></i> <?= $movie['view_count'] ?> lượt xem</span>
            <span class="comment-count"><i class="fas fa-comment"></i> <?= $movie['comment_count'] ?> bình luận</span>
            <span class="vip-badge vip-<?= $movie['required_vip'] ?>">VIP <?= $movie['required_vip'] ?></span>
        </div>
        
        <div class="video-description">
            <h3>Mô tả phim</h3>
            <p><?= nl2br($movie['description']) ?></p>
        </div>
    </div>

    <div class="comment-section">
        <h2>Bình luận</h2>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" class="comment-form">
            <div class="comment-input">
                <img src="<?= !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'assets/images/default-avatar.jpg' ?>" 
                     alt="Avatar" class="comment-avatar">
                <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi bình luận</button>
        </form>
        <?php else: ?>
        <div class="alert alert-info">
            Vui lòng <a href="login.php">đăng nhập</a> để bình luận
        </div>
        <?php endif; ?>
        
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <div class="comment-avatar">
                    <img src="<?= !empty($comment['avatar']) ? $comment['avatar'] : 'assets/images/default-avatar.jpg' ?>" 
                         alt="<?= $comment['username'] ?>">
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-author"><?= $comment['username'] ?></span>
                        <span class="comment-date"><?= format_date($comment['created_at'], 'd/m/Y H:i') ?></span>
                    </div>
                    <div class="comment-text">
                        <?= nl2br($comment['content']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// Theo dõi thời gian xem
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('main-video');
    let lastUpdate = 0;
    
    video.addEventListener('timeupdate', function() {
        const currentTime = Math.floor(video.currentTime);
        
        // Gửi cập nhật mỗi 30 giây
        if (currentTime > 0 && currentTime % 30 === 0 && currentTime !== lastUpdate) {
            fetch('api/update_watchtime.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    movie_id: <?= $movie_id ?>,
                    current_time: currentTime
                })
            });
            lastUpdate = currentTime;
        }
    });
});
</script>

<?php
require __DIR__ . '/includes/footer.php';