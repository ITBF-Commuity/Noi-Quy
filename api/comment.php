<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
check_login();

$db = Database::getInstance()->getPDO();
$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Lấy bình luận
        $movie_id = (int)$_GET['movie_id'];
        $stmt = $db->prepare("SELECT c.*, u.username, u.avatar 
                            FROM comments c
                            JOIN users u ON c.user_id = u.id
                            WHERE c.movie_id = ?
                            ORDER BY c.created_at DESC");
        $stmt->execute([$movie_id]);
        $comments = $stmt->fetchAll();

        $response = [
            'success' => true,
            'comments' => array_map(function($comment) {
                return [
                    'id' => $comment['id'],
                    'username' => htmlspecialchars($comment['username']),
                    'avatar' => $comment['avatar'] ? BASE_URL . $comment['avatar'] : BASE_URL . 'assets/images/default-avatar.jpg',
                    'content' => nl2br(htmlspecialchars($comment['content'])),
                    'created_at' => format_date($comment['created_at'], 'H:i d/m/Y')
                ];
            }, $comments)
        ];

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Thêm bình luận
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!verify_csrf_token($data['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }

        $movie_id = (int)$data['movie_id'];
        $content = sanitize($data['content']);

        if (empty($content)) {
            throw new Exception('Nội dung không được trống');
        }

        $stmt = $db->prepare("INSERT INTO comments (user_id, movie_id, content) 
                             VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $movie_id, $content]);

        $response = [
            'success' => true,
            'comment' => [
                'id' => $db->lastInsertId(),
                'username' => $_SESSION['username'],
                'avatar' => $_SESSION['avatar'] ?? BASE_URL . 'assets/images/default-avatar.jpg',
                'content' => nl2br(htmlspecialchars($content)),
                'created_at' => date('H:i d/m/Y')
            ]
        ];
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);