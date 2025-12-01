<?php
// user_profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: process/index.html");
    exit;
}
include 'db_config.php';
$uid = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT id, username, email, fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Profile</title>
  <link rel="stylesheet" href="styles/user-dashboard.css">
</head>
<body>
<div class="sidebar">
  <h2>Eventify</h2>
  <a href="user_dashboard.php">Dashboard</a>
  <a href="calendar.php">Calendar</a>
  <a class="active" href="user_profile.php">My Profile</a>
  <a href="logout.php">Logout</a>
</div>
<div class="content">
  <h1>My Profile</h1>
  <?php if (!empty($_SESSION['success'])) { echo '<p style="color:green">'.$_SESSION['success'].'</p>'; unset($_SESSION['success']); } ?>
  <?php if (!empty($_SESSION['error'])) { echo '<p style="color:red">'.$_SESSION['error'].'</p>'; unset($_SESSION['error']); } ?>
  <form action="update_profile.php" method="post" style="max-width:600px;">
    <div style="margin:8px 0;">
      <label>Full Name</label><br>
      <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="margin:8px 0;">
      <label>Email</label><br>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="margin:8px 0;">
      <label>Username</label><br>
      <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="margin:8px 0;">
      <label>New Password (leave blank to keep)</label><br>
      <input type="password" name="password" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="user_dashboard.php" class="action-btn">Back</a>
      <button class="edit-btn" type="submit">Save Profile</button>
    </div>
  </form>
</div>
</body>
</html>
