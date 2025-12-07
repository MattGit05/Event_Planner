<?php
// delete_event.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: process/index.html");
    exit;
}
include 'db_config.php';

$id = intval($_GET['id'] ?? 0);
$uid = intval($_SESSION['user_id']);
if (!$id) {
    header("Location: user_dashboard.php");
    exit;
}

// Ensure event belongs to this user
$check = $conn->prepare("SELECT title FROM events WHERE id = ? AND created_by = ?");
$check->bind_param("ii", $id, $uid);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    $_SESSION['error'] = 'Event not found or unauthorized.';
    header("Location: user_dashboard.php");
    exit;
}
$row = $res->fetch_assoc();
$title = $row['title'];

$stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $id, $uid);
$ok = $stmt->execute();

if ($ok) {
    $msg = "Event deleted: {$title}";
    $n = $conn->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
    $n->bind_param("is", $uid, $msg);
    $n->execute();

    header("Location: user_dashboard.php?deleted=1");
} else {
    $_SESSION['error'] = 'Database error deleting event.';
    header("Location: user_dashboard.php");
}
exit;
