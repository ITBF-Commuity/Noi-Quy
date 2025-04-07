<?php
include '../includes/config.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $movie_id = (int)$data['movieId'];
    $duration = (float)$data['currentTime'];

    $db->query("UPDATE watch_history SET duration = duration + $duration 
               WHERE user_id = $user_id AND movie_id = $movie_id");
}