<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    if (empty($input_data['title']) || empty($input_data['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Quiz title and user ID are required.']);
        exit();
    }

    $title = $input_data['title'];
    $description = isset($input_data['description']) ? $input_data['description'] : null;
    $user_id = $input_data['user_id'];

    $stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, description) VALUES (?, ?, ?)");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("iss", $user_id, $title, $description);

    if ($stmt->execute()) {
        $new_quiz_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Quiz created successfully.',
            'quiz_id' => $new_quiz_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create quiz: ' . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>