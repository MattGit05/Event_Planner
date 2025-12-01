<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access");
}

$admin_id = $_SESSION['admin_id'];
$current_password = $_POST['current_password'];

// Get admin password from DB
$stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($db_password);
$stmt->fetch();
$stmt->close();

// Verify password correctly
if (!password_verify($current_password, $db_password)) {
    echo "Incorrect password";
    exit();
}

// Update settings
$default_view = $_POST['default_view'];
$timezone = $_POST['timezone'];

$update = $conn->prepare("
    UPDATE app_settings 
    SET default_view = ?, timezone = ? 
    WHERE id = 1
");
$update->bind_param("ss", $default_view, $timezone);

if ($update->execute()) {
    echo "Settings saved successfully!";
} else {
    echo "Error updating settings.";
}

$update->close();
$conn->close();
?>
