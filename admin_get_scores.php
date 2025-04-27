<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-Withe');

include 'db_connect.php';
include 'admin_check.php';

$requesting_user_id = isset($_GET['admin_user_id']) ? intval($_GET['admin_user_id']) : null;

if (!isAdmin($conn, $requesting_user_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Admin access required.']);
    $conn->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "SELECT
                s.score_id,
                s.score,
                s.total_questions,
                s.taken_at,
                u.user_id AS taker_user_id,
                u.username AS taker_username,
                q.quiz_id,
                q.title AS quiz_title
            FROM scores s
            JOIN users u ON s.user_id = u.user_id
            JOIN quizzes q ON s.quiz_id = q.quiz_id
            ORDER BY s.taken_at DESC";

    $result = $conn->query($sql);

    if ($result) {
        $scores = [];
        if ($result->num_rows > 0) {
            $scores = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(['success' => true, 'scores' => $scores]);
        $result->free();
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to retrieve scores: ' . $conn->error]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use GET.']);
}

$conn->close();
?>