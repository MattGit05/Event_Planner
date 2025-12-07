<?php
// update_profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: process/index.html");
    exit;
}
include '../db_config.php';
$uid = intval($_SESSION['user_id']);
$email = trim($_POST['email'] ?? '');
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $name === '') {
    $_SESSION['error'] = 'Email and name required.';
    header("Location: user_settings.php");
    exit;
}

// Check uniqueness of email/name (excluding current user)
$chk = $conn->prepare("SELECT id FROM users WHERE (email = ? OR name = ?) AND id <> ?");
$chk->bind_param("ssi", $email, $name, $uid);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'Email or name already taken.';
    header("Location: user_profile.php");
    exit;
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET email=?, name=?, password=? WHERE id=?");
    $stmt->bind_param("sssi", $email, $name, $hash, $uid);
} else {
    $stmt = $conn->prepare("UPDATE users SET email=?, name=? WHERE id=?");
    $stmt->bind_param("ssi", $email, $name, $uid);
}
$ok = $stmt->execute();

if ($ok) {
    $_SESSION['success'] = 'Profile updated.';
    $_SESSION['name'] = $name;
} else {
    $_SESSION['error'] = 'Error saving profile.';
}
header("Location: user_settings.php");
exit;
