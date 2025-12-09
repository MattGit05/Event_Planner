<?php
session_start();
require "../db_config.php";

$user = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) AS total FROM messages WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo $result['total'];