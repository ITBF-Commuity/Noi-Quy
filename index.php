<?php
require __DIR__ . '/includes/config.php';

$db = Database::getInstance()->getPDO();

// Lấy danh sách phim
$query = "SELECT m.*, 
          (SELECT COUNT(*) FROM comments c WHERE c.movie_id = m.id) as comment_count,
          (SELECT COUNT(*) FROM watch_history wh WHERE wh.movie_id = m.id) as view_count
          FROM movies m
          ORDER BY m.created_at DESC LIMIT 12";

$movies = $db->query($query)->fetchAll();

// Lấy phim xem nhiều nhất
$popular_movies = $db->query("SELECT m.* FROM movies m 
                             JOIN (SELECT movie_id, COUNT(*) as views 
                                   FROM watch_history 
                                   GROUP BY movie_id 
                                   ORDER BY views DESC LIMIT 4) as popular
                             ON m.id = popular.movie_id")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Xem phim chất lượng cao</h1>
            <p>Hơn 10,000 bộ phim đủ thể loại</p>
            <a href="movies.php" class="btn btn-primary">Xem tất cả phim</a>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Phim mới cập nhật</h2>
        <div class="movie-grid">
            <?php foreach ($movies as $movie): ?>
            <div class="movie-card">
                <a href="watch.php?id=<?= $movie['id'] ?>">
                    <div class="movie-thumbnail">
                        <img src="<?= $movie['thumbnail'] ?>" alt="<?= $movie['title'] ?>">
                        <div class="movie-overlay">
                            <span class="view-count"><i class="fas fa-eye"></i> <?= $movie['view_count'] ?></span>
                            <span class="comment-count"><i class="fas fa-comment"></i> <?= $movie['comment_count'] ?></span>
                        </div>
                    </div>
                    <div class="movie-info">
                        <h3><?= $movie['title'] ?></h3>
                        <span class="vip-badge vip-<?= $movie['required_vip'] ?>">VIP <?= $movie['required_vip'] ?></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Phim xem nhiều nhất</h2>
        <div class="popular-movies">
            <?php foreach ($popular_movies as $movie): ?>
            <div class="popular-movie">
                <a href="watch.php?id=<?= $movie['id'] ?>">
                    <img src="<?= $movie['thumbnail'] ?>" alt="<?= $movie['title'] ?>">
                    <div class="popular-movie-info">
                        <h3><?= $movie['title'] ?></h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
require __DIR__ . '/includes/footer.php';