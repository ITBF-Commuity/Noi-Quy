<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
check_login();

$db = Database::getInstance()->getPDO();
$response = ['success' => false];
$user_id = $_SESSION['user_id'];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $movie_id = (int)$data['movie_id'];
    $action = $data['action'];

    switch ($action) {
        case 'add':
            $stmt = $db->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $movie_id]);
            $response['message'] = 'Đã thêm vào danh sách xem sau';
            break;

        case 'remove':
            $stmt = $db->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$user_id, $movie_id]);
            $response['message'] = 'Đã xóa khỏi danh sách xem sau';
            break;

        default:
            throw new Exception('Hành động không hợp lệ');
    }

    $response['success'] = true;
    $response['new_count'] = $db->query("SELECT COUNT(*) FROM watchlist WHERE user_id = $user_id")->fetchColumn();
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        $response['message'] = 'Phim đã có trong danh sách';
    } else {
        $response['message'] = 'Lỗi database: ' . $e->getMessage();
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);