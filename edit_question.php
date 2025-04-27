<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

     $required_fields = ['question_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
     foreach ($required_fields as $field) {
        if (empty($input_data[$field]) && $input_data[$field] !== '0') {
             echo json_encode(['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required.']);
             exit();
        }
     }

    if (!in_array(strtoupper($input_data['correct_answer']), ['A', 'B', 'C', 'D'])) {
         echo json_encode(['success' => false, 'error' => 'Correct answer must be A, B, C, or D.']);
         exit();
    }

    $question_id = $input_data['question_id'];
    $question_text = $input_data['question_text'];
    $option_a = $input_data['option_a'];
    $option_b = $input_data['option_b'];
    $option_c = $input_data['option_c'];
    $option_d = $input_data['option_d'];
    $correct_answer = strtoupper($input_data['correct_answer']);

    $stmt = $conn->prepare("UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ? WHERE question_id = ?");
     if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssssssi", $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Question updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Question not found or no changes made.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update question: ' . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>