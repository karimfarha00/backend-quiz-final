<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);

    if ( (empty($input_data['username']) && empty($input_data['email'])) || empty($input_data['password']) ) {
        echo json_encode(['success' => false, 'error' => 'Username/Email and password are required.']);
        exit();
    }

    $login_identifier = !empty($input_data['username']) ? $input_data['username'] : $input_data['email'];
    $password = $input_data['password'];

    $column_to_check = filter_var($login_identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    $sql = "SELECT user_id, username, email, password_hash, role FROM users WHERE {$column_to_check} = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("s", $login_identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful.',
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use POST.']);
}

$conn->close();
?>