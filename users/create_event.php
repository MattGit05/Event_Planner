<?php
// create_event.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: process/index.html");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user_dashboard.php");
    exit;
}
include 'db_config.php';

$title = trim($_POST['title'] ?? '');
$date  = $_POST['date'] ?? null;
$time  = $_POST['time'] ?? null;
$category = $_POST['category'] ?? 'Other';
$description = $_POST['description'] ?? '';
$created_by = intval($_SESSION['user_id']);

if ($title === '' || !$date) {
    $_SESSION['error'] = 'Title and Date are required.';
    header("Location: user_dashboard.php");
    exit;
}

$stmt = $conn->prepare("INSERT INTO events (title, category, description, date, time, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssi", $title, $category, $description, $date, $time, $created_by);
$ok = $stmt->execute();

if ($ok) {
    // Add a notification
    $msg = "Event created: {$title}";
    $n = $conn->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
    $n->bind_param("is", $created_by, $msg);
    $n->execute();

    header("Location: user_dashboard.php?created=1");
} else {
    $_SESSION['error'] = 'Database error creating event.';
    header("Location: user_dashboard.php");
}
exit;
