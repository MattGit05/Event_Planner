<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "final-project_db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Don't output errors to browser; log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}
// ✅ No closing PHP tag to avoid accidental whitespace
