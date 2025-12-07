<?php
// send_message.php - Send message from user to admin
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include 'db_config.php';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !isset($data['event_id']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$event_id = (int) $data['event_id'];
$message = trim($data['message']);
$user_id = (int) $_SESSION['user_id'];

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

// Get user info
$user_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Get event info
$event_stmt = $conn->prepare("SELECT title FROM events WHERE id = ?");
$event_stmt->bind_param('i', $event_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit;
}

// Insert message into notifications table
$insert_stmt = $conn->prepare("INSERT INTO notifications (user_id, event_id, message, type, created_at) VALUES (?, ?, ?, 'user_message', NOW())");
$insert_stmt->bind_param('iis', $user_id, $event_id, $message);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $conn->error]);
}

$conn->close();
?>
