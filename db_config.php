<?php
$host = "localhost";
$user = "root"; // change if needed
$pass = "";     // change if needed
$dbname = "final-project_db"; // your DB name

$conn = new mysqli($host, $user, $pass, $dbname);

// ✅ Use standard connection error handling without breaking JSON responses
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}
?>
