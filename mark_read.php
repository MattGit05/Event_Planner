<?php
// mark_read.php - Mark notification as read
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

include 'db_config.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$conn->close();
?>
