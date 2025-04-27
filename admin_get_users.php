<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

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

    $sql = "SELECT user_id, username, email, role, created_at FROM users ORDER BY user_id ASC";
    $result = $conn->query($sql);

    if ($result) {
        $users = [];
        if ($result->num_rows > 0) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(['success' => true, 'users' => $users]);
        $result->free();
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to retrieve users: ' . $conn->error]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use GET.']);
}

$conn->close();
?>