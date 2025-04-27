<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    if (empty($input_data['username']) || empty($input_data['email']) || empty($input_data['password'])) {
        echo json_encode(['success' => false, 'error' => 'Username, email, and password are required.']);
        exit();
    }

    if (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
        exit();
    }

    $username = $input_data['username'];
    $email = $input_data['email'];
    $password = $input_data['password'];

    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    if (!$stmt_check) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed (check exists): ' . $conn->error]);
        exit();
    }
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Username or email already taken.']);
        $stmt_check->close();
        $conn->close();
        exit();
    }
    $stmt_check->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
     if ($password_hash === false) {
         echo json_encode(['success' => false, 'error' => 'Failed to hash password.']);
         $conn->close();
         exit();
    }

    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
     if (!$stmt_insert) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed (insert): ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt_insert->bind_param("sss", $username, $email, $password_hash);

    if ($stmt_insert->execute()) {
        $new_user_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully.',
            'user_id' => $new_user_id
            ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $stmt_insert->error]);
    }

    $stmt_insert->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>