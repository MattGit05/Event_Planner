<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include 'db_config.php';

// Get admin user_id
$sql_admin = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
$result_admin = $conn->query($sql_admin);
if ($result_admin->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "No admin found"]);
    exit;
}
$admin_row = $result_admin->fetch_assoc();
$admin_id = $admin_row['id'];

// Fetch events added by admin
$sql = "SELECT id, title, category, description, date, time, attendees, created_at FROM events WHERE created_by = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["success" => false, "message" => "Query failed: " . $conn->error]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    $row['attendees'] = json_decode($row['attendees'], true) ?? [];
    $events[] = $row;
}

echo json_encode(["success" => true, "events" => $events], JSON_PRETTY_PRINT);
$conn->close();
?>
