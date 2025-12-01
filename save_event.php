<?php
// Disable error display to avoid corrupting JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start output buffering to prevent any unintended output before JSON
ob_start();

header('Content-Type: application/json');

// ✅ FIX: Make sure the correct path to db_config.php is used
// If db_config.php is in the same folder, this is fine.
// If it’s inside a folder like "backend/", change it accordingly.
$db_config_path = __DIR__ . '/db_config.php';
if (!file_exists($db_config_path)) {
    // Clear buffer before echoing JSON
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Database configuration file not found"]);
    exit;
}
include $db_config_path;

// ✅ Read and decode JSON input safely
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// If JSON is invalid
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

// Start session to get user_id
session_start();
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}
$user_id = $_SESSION['user_id'];

// ✅ Extract and validate fields
$title = trim($data['title'] ?? '');
$category = trim($data['category'] ?? '');
$description = trim($data['description'] ?? '');
$date = trim($data['date'] ?? '');
$time = trim($data['time'] ?? '');

$attendees = isset($data['attendees']) ? json_encode($data['attendees']) : json_encode([]);

if (empty($title) || empty($category) || empty($date) || empty($time)) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// ✅ Prepare and execute SQL insert
$stmt = $conn->prepare("
    INSERT INTO events (title, category, description, date, time, attendees, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssssssi", $title, $category, $description, $date, $time, $attendees, $user_id);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(["success" => true, "message" => "Event saved successfully"]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
