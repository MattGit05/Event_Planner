<?php
session_start();
require "../db_config.php";  // FIXED PATH (adjust if needed)

header('Content-Type: application/json'); // prevent HTML output

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "no-session"]);
    exit;
}

if (!isset($_GET['other_id'])) {
    echo json_encode(["error" => "no-other-id"]);
    exit;
}

$user = (int) $_SESSION['user_id'];
$other = (int) $_GET['other_id'];

$sql = "
    SELECT id, sender_id, receiver_id, message, created_at
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?)
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user, $other, $other, $user);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
