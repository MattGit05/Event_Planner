<?php
// component/delete_user.php
session_start();
header('Content-Type: application/json');

// Security check: Only allow admins to perform this action
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

include '../db_config.php'; // Adjust path if necessary

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    // Prevent admin from deleting their own account (important security/UX feature)
    if ($user_id == $_SESSION['user_id']) {
        $response['message'] = 'You cannot delete your own active account.';
        echo json_encode($response);
        exit;
    }

    try {
        // Prepare the SQL statement to delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'User deleted successfully.';
            } else {
                $response['message'] = 'User not found or already deleted.';
            }
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request.';
}

$conn->close();
echo json_encode($response);
?>