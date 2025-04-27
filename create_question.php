<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    $required_fields = ['quiz_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
    foreach ($required_fields as $field) {
        if (empty($input_data[$field])) {
            echo json_encode(['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required.']);
            exit();
        }
    }

    if (!in_array(strtoupper($input_data['correct_answer']), ['A', 'B', 'C', 'D'])) {
         echo json_encode(['success' => false, 'error' => 'Correct answer must be A, B, C, or D.']);
         exit();
    }

    $quiz_id = $input_data['quiz_id'];
    $question_text = $input_data['question_text'];
    $option_a = $input_data['option_a'];
    $option_b = $input_data['option_b'];
    $option_c = $input_data['option_c'];
    $option_d = $input_data['option_d'];
    $correct_answer = strtoupper($input_data['correct_answer']);

    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("issssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);

    if ($stmt->execute()) {
        $new_question_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Question added successfully.',
            'question_id' => $new_question_id
        ]);
    } else {
        if ($conn->errno == 1452) {
             echo json_encode(['success' => false, 'error' => 'Failed to add question: Invalid Quiz ID provided.']);
        } else {
             echo json_encode(['success' => false, 'error' => 'Failed to add question: ' . $stmt->error]);
        }
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>