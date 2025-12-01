<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: process/index.html");
    exit;
}

// include DB to load current admin settings
include 'db_config.php';
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id=?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Settings - Eventify</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* Global Styles */
body {
  font-family: 'Inter', sans-serif;
  margin: 0;
  display: flex;
  min-height: 100vh;
  background-color: #f6f8fc;
  color: #1a1a1a;
  transition: background 0.3s, color 0.3s;
}
.dark {
  background-color: #1e1e2f;
  color: #f0f0f0;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: #fff;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-right: 1px solid #e0e0e0;
}
.dark .sidebar {
  background: #2c2c3e;
  border-color: #444;
}
.sidebar .logo {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.2rem;
}
.sidebar .subtitle {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 2rem;
}
.sidebar .nav a {
  display: block;
  padding: 0.6rem 0;
  color: #1a1a1a;
  text-decoration: none;
  border-radius: 6px;
  margin-bottom: 0.3rem;
  transition: 0.2s;
}
.dark .sidebar .nav a {
  color: #ddd;
}
.sidebar .nav a:hover {
  background: #e4e9f7;
}
.dark .sidebar .nav a:hover {
  background: #3a3a50;
}
.sidebar .bottom-section {
  font-size: 0.85rem;
  color: #666;
}
.sidebar .theme-toggle {
  margin-top: 0.3rem;
}

/* Main content */
.main {
  flex: 1;
  padding: 2rem;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.header h1 {
  margin: 0;
  font-size: 1.8rem;
}
.header p {
  margin: 0.2rem 0 0 0;
  color: #666;
}
.logout-btn {
  background: #ff2b6a;
  color: #fff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.2s;
}
.logout-btn:hover {
  opacity: 0.9;
}

/* Card styles */
.card {
  background: #fff;
  border-radius: 12px;
  padding: 1.8rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: 0.3s;
}
.dark .card {
  background: #2c2c3e;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.card h2 {
  margin-top: 0;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}
.card p {
  font-size: 0.9rem;
  color: #555;
}
.form-input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  margin-top: 0.4rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: 0.2s;
}
.dark .form-input {
  background: #3a3a50;
  border: 1px solid #555;
  color: #f0f0f0;
}
.form-input:focus {
  outline: none;
  border-color: #5b6ef8;
  box-shadow: 0 0 0 2px rgba(91,110,248,0.2);
}
.save-btn {
  background: #5b6ef8;
  color: #fff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.2s;
}
.save-btn:hover {
  opacity: 0.9;
}

/* Danger zone */
.card.danger {
  border-left: 6px solid #ff2b6a;
}
.card.danger p {
  color: #aa1f4a;
}
.card.danger .logout-btn {
  background: #ff2b6a;
}

/* Responsive */
@media (max-width: 900px) {
  body {
    flex-direction: column;
  }
  .sidebar {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
  }
  .main {
    padding: 1rem;
  }
} 
</style>
</head>

<body>
<aside class="sidebar">
  <div>
    <div class="logo">Eventify</div>
    <div class="subtitle">Professional event planner</div>
    <nav class="nav">
      <a href="index.php">📊 Dashboard</a>
      <a href="my-events.php">📁 My Events</a>
      <a href="calendar.php">📅 Calendar</a>
      <a href="users.php">👥 View Users</a>
      <a href="notification.php">🔔 Notifications</a>
      <a href="admin_settings.php" style="background:#f0f3fa;">⚙️ Settings</a>
    </nav>
  </div>

  <div class="bottom-section">
    <div>Theme</div>
    <div class="theme-toggle">
      <label>Dark</label>
      <input type="checkbox" id="themeToggle">
    </div>
    <div style="margin-top:1rem; font-size:0.8rem;">v1.0 • Local only</div>
  </div>
</aside>

<main class="main">
  <div class="header">
    <div>
      <h1>Admin Settings</h1>
      <p>Manage your account and system preferences</p>
    </div>
    <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
  </div>

  <!-- Account Information -->
  <div class="card">
    <h2>👤 Account Information</h2>
    <form action="update_settings.php" method="POST">
      <label>Full Name</label>
      <input type="text" name="name" value="<?= $name ?>" class="form-input">

      <label>Email</label>
      <input type="email" name="email" value="<?= $email ?>" class="form-input">

      <label>New Password</label>
      <input type="password" name="new_password" placeholder="Leave blank to keep current" class="form-input">

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" class="form-input">

      <button class="save-btn">Save Changes</button>
    </form>
  </div>

  <!-- System Preferences -->
  <div class="card">
    <h2>⚙️ System Preferences</h2>

    <label>Default Dashboard View</label>
    <select class="form-input" name="default_view">
      <option value="Dashboard">Dashboard</option>
      <option value="My Events">My Events</option>
      <option value="Calendar">Calendar</option>
    </select>

    <label>Notification Sound</label>
    <select class="form-input" name="notif_sound">
      <option value="Default">Default</option>
      <option value="Soft">Soft</option>
      <option value="Pop">Pop</option>
    </select>

    <button class="save-btn">Update Preferences</button>
  </div>

  <!-- Danger Zone -->
  <div class="card danger">
    <h2>⚠️ Danger Zone</h2>
    <p>You can delete your admin account. This action is permanent.</p>
    <button class="logout-btn">Delete Account</button>
  </div>
</main>

<script>
const toggle = document.getElementById("themeToggle");
toggle.addEventListener("change", () => {
  document.body.classList.toggle("dark");
});
</script>

</body>
</html>

</body>
</html>
