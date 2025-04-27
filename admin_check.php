<?php
function isAdmin($conn, $admin_user_id) {
    if (empty($admin_user_id) || !is_numeric($admin_user_id)) {
        return false;
    }

    $stmt_check_admin = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    if (!$stmt_check_admin) {
        error_log("Admin check prepare failed: " . $conn->error);
        return false;
    }

    $stmt_check_admin->bind_param("i", $admin_user_id);
    $stmt_check_admin->execute();
    $result = $stmt_check_admin->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt_check_admin->close();
        return ($user['role'] === 'admin');
    }

    $stmt_check_admin->close();
    return false;
}
?>