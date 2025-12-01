<?php
// FULLY PATCHED get-events.php
// Always return CLEAN JSON — no HTML, no warnings.

ob_start(); // prevent accidental HTML output

error_reporting(E_ALL);
ini_set('display_errors', 0); // don't output warnings to browser

header('Content-Type: application/json');

// Load DB config
include 'db_config.php';

// Prepare query
$sql = "SELECT id, title, category, description, date, time, attendees, created_at 
        FROM events 
        ORDER BY id DESC";

$result = $conn->query($sql);

// Database error
if (!$result) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // decode attendees JSON
    $row['attendees'] = json_decode($row['attendees'], true) ?? [];
    $events[] = $row;
}

ob_end_clean(); // delete any unwanted output (warnings, whitespace, BOM)
echo json_encode([
    "success" => true,
    "events" => $events
], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
