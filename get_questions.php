<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (empty($_GET['quiz_id'])) {
        echo json_encode(['success' => false, 'error' => 'Quiz ID is required.']);
        exit();
    }

    $quiz_id = intval($_GET['quiz_id']);

    $stmt = $conn->prepare("SELECT question_id, quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, created_at FROM questions WHERE quiz_id = ? ORDER BY created_at ASC");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $questions = [];
        if ($result->num_rows > 0) {
            $questions = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(['success' => true, 'questions' => $questions]);
        $result->free();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to retrieve questions: ' . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use GET.']);
}

$conn->close();
?>