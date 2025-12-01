<?php
include 'db_config.php';

header('Content-Type: application/json');

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo json_encode(['error' => 'Users table does not exist']);
    exit;
}

// Describe users table
$result = $conn->query("DESCRIBE users");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
}

echo json_encode(['columns' => $columns]);
?>
