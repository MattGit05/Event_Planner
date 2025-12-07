<?php
// delete_notif.php - Delete notification
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

include 'db_config.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$conn->close();
?>
