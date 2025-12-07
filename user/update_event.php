<?php
// update_event.php
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

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$date  = $_POST['date'] ?? null;
$time  = $_POST['time'] ?? null;
$category = $_POST['category'] ?? 'Other';
$description = $_POST['description'] ?? '';
$uid = intval($_SESSION['user_id']);

if (!$id || $title==='' || !$date) {
    $_SESSION['error'] = 'Missing required fields.';
    header("Location: user_dashboard.php");
    exit;
}

// Ensure event belongs to this user
$check = $conn->prepare("SELECT id FROM events WHERE id = ? AND created_by = ?");
$check->bind_param("ii", $id, $uid);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    $_SESSION['error'] = 'Event not found or unauthorized.';
    header("Location: user_dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE events SET title=?, category=?, description=?, date=?, time=? WHERE id=?");
$stmt->bind_param("sssssi", $title, $category, $description, $date, $time, $id);
$ok = $stmt->execute();

if ($ok) {
    $msg = "Event updated: {$title}";
    $n = $conn->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
    $n->bind_param("is", $uid, $msg);
    $n->execute();

    header("Location: user_dashboard.php?updated=1");
} else {
    $_SESSION['error'] = 'Database error updating event.';
    header("Location: user_dashboard.php");
}
exit;
