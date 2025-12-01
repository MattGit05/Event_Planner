<?php
// notifications.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: process/index.html");
    exit;
}
include 'db_config.php';
$uid = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $u = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $u->bind_param("i", $uid);
    $u->execute();
    header("Location: user_dashboard.php");
    exit;
}

// show a simple list (or you can integrate this into AJAX UI)
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("i", $uid);
$stmt->execute();
$notis = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="styles/user-dashboard.css">
</head>
<body>
<div class="sidebar">
  <h2>Eventify</h2>
  <a href="user_dashboard.php">Dashboard</a>
  <a href="calendar.php">Calendar</a>
  <a href="user_profile.php">Profile</a>
  <a href="logout.php">Logout</a>
</div>
<div class="content">
  <h1>Notifications</h1>
  <form method="post">
    <button type="submit" name="mark_all_read" class="action-btn">Mark all read</button>
  </form>
  <ul style="margin-top:12px;">
    <?php while ($n = $notis->fetch_assoc()): ?>
      <li style="padding:8px 0;border-bottom:1px solid #eee;">
        <?php echo htmlspecialchars($n['message']); ?> <br>
        <small><?php echo $n['created_at']; ?> <?php if (!$n['is_read']) echo '<strong style="color:#2563eb"> • new</strong>'; ?></small>
      </li>
    <?php endwhile; ?>
  </ul>
</div>
</body>
</html>
