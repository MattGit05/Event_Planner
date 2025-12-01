<?php
header('Content-Type: application/json');

// Disable error display to avoid corrupting JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start output buffering to prevent any unintended output before JSON
ob_start();

// Include database config
$db_config_path = __DIR__ . '/db_config.php';
if (!file_exists($db_config_path)) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Database configuration file not found"]);
    exit;
}
include $db_config_path;

// Read and decode JSON input safely
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

$id = $data['id'] ?? null;
$title = trim($data['title'] ?? '');
$category = trim($data['category'] ?? '');
$description = trim($data['description'] ?? '');
$date = trim($data['date'] ?? '');
$time = trim($data['time'] ?? '');
$attendees = isset($data['attendees']) ? json_encode($data['attendees']) : json_encode([]);

if (!$id || empty($title) || empty($category) || empty($date) || empty($time)) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, date = ?, time = ?, attendees = ? WHERE id = ?");
if (!$stmt) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssssssi", $title, $category, $description, $date, $time, $attendees, $id);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(["success" => true, "message" => "Event updated successfully"]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Failed to update event: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
