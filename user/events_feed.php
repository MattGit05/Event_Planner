<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode([]);
    exit;
}

require 'db_config.php';

// Logged-in user ID
$userID = $_SESSION['user_id'];

$sql = "SELECT id, title, date, time, category 
        FROM events 
        WHERE created_by = ?
        ORDER BY date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {

    // Combine date + time into ISO format
    $start = $row['date'];
    if (!empty($row['time'])) {
        $start .= "T" . $row['time'];
    }

    $events[] = [
        'id'    => $row['id'],
        'title' => $row['title'],
        'start' => $start,
        'className' => strtolower($row['category'])
    ];
}

echo json_encode($events);
