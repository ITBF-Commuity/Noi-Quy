<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

check_login();

$db = Database::getInstance()->getPDO();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Lọc phim
$genre = isset($_GET['genre']) ? sanitize($_GET['genre']) : '';
$where = $genre ? "WHERE genre LIKE '%$genre%'" : '';

// Lấy danh sách phim
$movies = $db->query("
    SELECT m.*, 
    (SELECT COUNT(*) FROM comments c WHERE c.movie_id = m.id) as comment_count,
    (SELECT COUNT(*) FROM watch_history wh WHERE wh.movie_id = m.id) as view_count
    FROM movies m
    $where
    ORDER BY m.created_at DESC
    LIMIT $per_page OFFSET $offset
")->fetchAll();

// Đếm tổng số phim
$total = $db->query("SELECT COUNT(*) FROM movies $where")->fetchColumn();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1>Danh sách phim</h1>
    
    <!-- Bộ lọc -->
    <div class="filter-section">
        <form method="GET">
            <select name="genre" onchange="this.form.submit()">
                <option value="">Tất cả thể loại</option>
                <?php
                $genres = $db->query("SELECT DISTINCT genre FROM movies")->fetchAll();
                foreach ($genres as $g): 
                    $selected = ($genre === $g['genre']) ? 'selected' : '';
                ?>
                <option value="<?= $g['genre'] ?>" <?= $selected ?>>
                    <?= $g['genre'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Danh sách phim -->
    <div class="movie-grid">
        <?php foreach ($movies as $movie): ?>
        <div class="movie-card">
            <a href="watch.php?id=<?= $movie['id'] ?>">
                <img src="<?= $movie['thumbnail'] ?>" alt="<?= $movie['title'] ?>">
                <div class="movie-info">
                    <h3><?= $movie['title'] ?></h3>
                    <span class="vip-badge vip-<?= $movie['required_vip'] ?>">VIP <?= $movie['required_vip'] ?></span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Phân trang -->
    <div class="pagination">
        <?php
        $total_pages = ceil($total / $per_page);
        for ($i = 1; $i <= $total_pages; $i++):
        ?>
        <a href="?page=<?= $i ?>&genre=<?= $genre ?>" class="<?= $page === $i ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';