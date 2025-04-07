<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
check_login();

$db = Database::getInstance()->getPDO();
$response = ['success' => false];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $movie_id = (int)$data['movie_id'];
    $current_time = (float)$data['current_time'];
    $user_id = $_SESSION['user_id'];

    // Cập nhật lịch sử xem
    $stmt = $db->prepare("INSERT INTO watch_history (user_id, movie_id, duration, last_watched)
                         VALUES (?, ?, ?, NOW())
                         ON DUPLICATE KEY UPDATE 
                         duration = duration + VALUES(duration),
                         last_watched = NOW()");
    $stmt->execute([$user_id, $movie_id, $current_time]);

    // Cập nhật tổng thời lượng xem
    $db->prepare("UPDATE users SET watch_hours = watch_hours + ? WHERE id = ?")
       ->execute([$current_time / 3600, $user_id]);

    $response['success'] = true;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);