<?php
session_start();
require "db_config.php";

$user = $_SESSION['user_id'];
$other = $_GET['other_id'];

$sql = "
    SELECT * FROM messages 
    WHERE (sender_id = $user AND receiver_id = $other)
       OR (sender_id = $other AND receiver_id = $user)
    ORDER BY created_at ASC
";
$result = $conn->query($sql);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
