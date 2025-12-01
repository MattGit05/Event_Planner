<?php
// events_feed.php
session_start();
header('Content-Type: application/json');
include '../db_config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['error' => 'unauth']);
    exit;
}
$uid = intval($_SESSION['user_id']);

// single event
if (isset($_GET['single'])) {
    $id = intval($_GET['single']);
    $s = $conn->prepare("SELECT id, title, description, date, time, category FROM events WHERE id = ? AND created_by = ?");
    $s->bind_param("ii", $id, $uid);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    if (!$r) { echo json_encode(['error'=>'not found']); exit; }
    echo json_encode($r);
    exit;
}

// FullCalendar expects an array of events
$stmt = $conn->prepare("SELECT id, title, description, date, time, category FROM events WHERE created_by = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$events = [];
while ($row = $res->fetch_assoc()) {
    $start = $row['date'];
    if (!empty($row['time'])) $start .= 'T'.$row['time'];
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $start,
        'extendedProps' => [
            'description' => $row['description'],
            'category' => $row['category']
        ]
    ];
}
echo json_encode($events);
