<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

check_login();

$db = Database::getInstance()->getPDO();
$user_id = $_SESSION['user_id'];

// Xử lý thêm/xóa khỏi watchlist
if (isset($_GET['action']) && isset($_GET['movie_id'])) {
    $movie_id = (int)$_GET['movie_id'];
    $action = $_GET['action'];
    
    if ($action === 'add') {
        $db->prepare("INSERT IGNORE INTO watchlist (user_id, movie_id) VALUES (?, ?)")
           ->execute([$user_id, $movie_id]);
    } elseif ($action === 'remove') {
        $db->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?")
           ->execute([$user_id, $movie_id]);
    }
    
    redirect('watchlist.php');
}

// Lấy danh sách phim đã lưu
$watchlist = $db->prepare("SELECT m.* FROM movies m
                          JOIN watchlist w ON m.id = w.movie_id
                          WHERE w.user_id = ?
                          ORDER BY w.added_at DESC");
$watchlist->execute([$user_id]);
$watchlist = $watchlist->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1>Danh sách phim đã lưu</h1>
    
    <?php if (empty($watchlist)): ?>
    <div class="alert alert-info">
        Bạn chưa lưu phim nào. <a href="index.php">Khám phá phim mới</a>
    </div>
    <?php else: ?>
    <div class="movie-grid">
        <?php foreach ($watchlist as $movie): ?>
        <div class="movie-card">
            <a href="watch.php?id=<?= $movie['id'] ?>">
                <div class="movie-thumbnail">
                    <img src="<?= $movie['thumbnail'] ?>" alt="<?= $movie['title'] ?>">
                    <div class="movie-overlay">
                        <span class="view-count"><i class="fas fa-eye"></i> <?= $movie['view_count'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="movie-info">
                    <h3><?= $movie['title'] ?></h3>
                    <span class="vip-badge vip-<?= $movie['required_vip'] ?>">VIP <?= $movie['required_vip'] ?></span>
                </div>
            </a>
            <div class="movie-actions">
                <a href="watchlist.php?action=remove&movie_id=<?= $movie['id'] ?>" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Xóa
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/footer.php';