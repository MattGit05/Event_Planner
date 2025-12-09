<?php
session_start();
require "db_config.php";

if (!isset($_SESSION['user_id'])) {
    echo "Not logged in";
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$event_id = $_POST['event_id'] ?? null;
$message = $_POST['message'] ?? null;

if (!$receiver_id || !$event_id || !$message) {
    echo "Missing fields";
    exit;
}

// Prepared statement
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, event_id, message, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("iiis", $sender_id, $receiver_id, $event_id, $message);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Database error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
