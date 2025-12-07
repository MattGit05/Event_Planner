<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../db_config.php';

$user_id = (int) $_SESSION['user_id'];

// Fetch user info
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result();
$user = $user_res->fetch_assoc();
$user_name_json = json_encode($user['name']);

if (isset($_GET['id'])) {
    $event_id = (int) $_GET['id'];

    // Fetch event details, ensuring user has access
    $stmt = $conn->prepare("SELECT id, title, category, date, time, description, attendees, created_by FROM events WHERE id = ? AND (created_by = ? OR JSON_CONTAINS(attendees, ?))");
    $stmt->bind_param('iis', $event_id, $user_id, $user_name_json);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        // Decode attendees
        $event['attendees'] = json_decode($event['attendees'], true);
        echo json_encode($event);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found or access denied']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID required']);
}
?>
