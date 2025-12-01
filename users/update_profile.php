<?php
// update_profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: process/index.html");
    exit;
}
include 'db_config.php';
$uid = intval($_SESSION['user_id']);
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$fullname = trim($_POST['fullname'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $username === '') {
    $_SESSION['error'] = 'Email and username required.';
    header("Location: user_profile.php");
    exit;
}

// Check uniqueness of email/username (excluding current user)
$chk = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id <> ?");
$chk->bind_param("ssi", $email, $username, $uid);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'Email or username already taken.';
    header("Location: user_profile.php");
    exit;
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET email=?, username=?, fullname=?, password=? WHERE id=?");
    $stmt->bind_param("ssssi", $email, $username, $fullname, $hash, $uid);
} else {
    $stmt = $conn->prepare("UPDATE users SET email=?, username=?, fullname=? WHERE id=?");
    $stmt->bind_param("sssi", $email, $username, $fullname, $uid);
}
$ok = $stmt->execute();

if ($ok) {
    $_SESSION['success'] = 'Profile updated.';
    $_SESSION['username'] = $username;
} else {
    $_SESSION['error'] = 'Error saving profile.';
}
header("Location: user_profile.php");
exit;
