<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    if (empty($input_data['quiz_id']) || empty($input_data['title'])) {
        echo json_encode(['success' => false, 'error' => 'Quiz ID and Title are required.']);
        exit();
    }

    $quiz_id = $input_data['quiz_id'];
    $title = $input_data['title'];
    $description = isset($input_data['description']) ? $input_data['description'] : null;

    $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ? WHERE quiz_id = ?");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssi", $title, $description, $quiz_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Quiz updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Quiz not found or no changes made.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update quiz: ' . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>