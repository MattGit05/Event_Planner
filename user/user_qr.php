<?php
session_start();
include '../db_config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must log in to view your QR code.");
}

$userId = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT name, qr_code FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Your QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            text-align: center;
            padding-top: 50px;
        }
        .card {
            background: white;
            width: 350px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        img {
            width: 250px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2><?php echo $user['name']; ?>'s QR Code</h2>

    <?php if (!empty($user['qr_code'])): ?>
        <img src="../<?php echo $user['qr_code']; ?>" alt="QR Code">
    <?php else: ?>
        <p>No QR code found for this account.</p>
    <?php endif; ?>

    <br><br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
