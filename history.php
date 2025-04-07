<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

check_login();

$db = Database::getInstance()->getPDO();
$user_id = $_SESSION['user_id'];

// Xử lý xóa lịch sử
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $db->prepare("DELETE FROM watch_history WHERE user_id = ?")->execute([$user_id]);
    redirect('history.php');
}

// Lấy lịch sử xem phim
$history = $db->prepare("SELECT m.*, wh.last_watched 
                        FROM movies m
                        JOIN watch_history wh ON m.id = wh.movie_id
                        WHERE wh.user_id = ?
                        ORDER BY wh.last_watched DESC");
$history->execute([$user_id]);
$history = $history->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="history-header">
        <h1>Lịch sử xem phim</h1>
        <?php if (!empty($history)): ?>
        <a href="history.php?action=clear" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa toàn bộ lịch sử?')">
            <i class="fas fa-trash"></i> Xóa tất cả
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($history)): ?>
    <div class="alert alert-info">
        Bạn chưa xem phim nào. <a href="index.php">Khám phá phim mới</a>
    </div>
    <?php else: ?>
    <div class="history-list">
        <?php foreach ($history as $movie): ?>
        <div class="history-item">
            <a href="watch.php?id=<?= $movie['id'] ?>" class="history-thumbnail">
                <img src="<?= $movie['thumbnail'] ?>" alt="<?= $movie['title'] ?>">
            </a>
            <div class="history-info">
                <h3><a href="watch.php?id=<?= $movie['id'] ?>"><?= $movie['title'] ?></a></h3>
                <div class="history-meta">
                    <span class="vip-badge vip-<?= $movie['required_vip'] ?>">VIP <?= $movie['required_vip'] ?></span>
                    <span class="history-date">
                        <i class="fas fa-clock"></i> <?= format_date($movie['last_watched'], 'd/m/Y H:i') ?>
                    </span>
                </div>
                <div class="history-actions">
                    <a href="watchlist.php?action=add&movie_id=<?= $movie['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-bookmark"></i> Lưu lại
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/footer.php';