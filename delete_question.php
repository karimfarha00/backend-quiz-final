<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    if (empty($input_data['question_id'])) {
        echo json_encode(['success' => false, 'error' => 'Question ID is required.']);
        exit();
    }

    $question_id = $input_data['question_id'];

    $stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("i", $question_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Question deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Question not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete question: ' . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>