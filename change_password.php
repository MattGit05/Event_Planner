<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access.";
    exit;
}

include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $admin_id = $_SESSION['user_id'];

    // Empty fields check
    if (empty($current) || empty($new) || empty($confirm)) {
        echo "<script>alert('All password fields are required'); history.back();</script>";
        exit;
    }

    // Check new passwords match
    if ($new !== $confirm) {
        echo "<script>alert('New passwords do not match'); history.back();</script>";
        exit;
    }

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    // Verify old password
    if (!password_verify($current, $row['password'])) {
        echo "<script>alert('Current password is incorrect'); history.back();</script>";
        exit;
    }

    // Hash new password
    $hashed = password_hash($new, PASSWORD_DEFAULT);

    // Update DB
    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $update->bind_param("si", $hashed, $admin_id);

    if ($update->execute()) {
        echo "<script>alert('Password changed successfully!'); window.location='settings.php';</script>";
    } else {
        echo "Error updating password.";
    }
}
?>
