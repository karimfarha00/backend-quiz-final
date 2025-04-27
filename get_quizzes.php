<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "SELECT q.quiz_id, q.title, q.description, q.created_at, u.username as creator_username
            FROM quizzes q
            JOIN users u ON q.user_id = u.user_id
            ORDER BY q.created_at DESC";

    $result = $conn->query($sql);

    if ($result) {
        $quizzes = [];
        if ($result->num_rows > 0) {
            $quizzes = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(['success' => true, 'quizzes' => $quizzes]);
        $result->free();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to retrieve quizzes: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use GET.']);
}

$conn->close();
?>