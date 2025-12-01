<?php
require 'db_config.php';

// Read and decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request or missing event ID."]);
    exit;
}

$id = intval($data['id']);

// Prepare the SQL statement securely
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Event deleted successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Error deleting event: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
