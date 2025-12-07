<?php
session_start();
require "../db_config.php"; // adjust path if needed

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit;
}

if (!isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    echo "error";
    exit;
}

$sender = (int)$_SESSION['user_id'];
$receiver = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);

if ($message === "") {
    echo "error";
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender, $receiver, $message);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
