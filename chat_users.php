<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: process/index.html");
    exit;
}

include 'db_config.php';

// Fetch all users with role 'user'
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role = 'user'");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select User to Chat - Eventify</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Inter', sans-serif;
  margin: 0;
  display: flex;
  min-height: 100vh;
  background-color: #f6f8fc;
  color: #1a1a1a;
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
.sidebar .logo { font-size: 1.5rem; font-weight: 600; margin-bottom: 0.2rem; }
.sidebar .subtitle { font-size: 0.85rem; color: #666; margin-bottom: 2rem; }
.sidebar .nav a { display: block; padding: 0.6rem 0; color: #1a1a1a; text-decoration: none; border-radius: 6px; margin-bottom: 0.3rem; transition: 0.2s; }
.dark .sidebar .nav a { color: #ddd; }
.sidebar .nav a:hover { background: #e4e9f7; }
.dark .sidebar .nav a:hover { background: #3a3a50; }
.sidebar .bottom-section { font-size: 0.85rem; color: #666; }
.sidebar .theme-toggle { margin-top: 0.3rem; }

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
.header h1 { margin: 0; font-size: 1.8rem; }
.header p { margin: 0.2rem 0 0 0; color: #666; }
.logout-btn { background: #ff2b6a; color: #fff; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.logout-btn:hover { opacity: 0.9; }

/* User list */
.users {
  max-width: 800px;
  margin: auto;
}
.user {
  background: #fff;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: 0.2s;
}
.dark .user { background: #2c2c3e; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.user:hover { transform: translateY(-2px); }
.user .info { max-width: 80%; }
.user .info h3 { margin: 0 0 0.3rem 0; font-size: 1rem; }
.user .info p { margin: 0; font-size: 0.85rem; color: #555; }
.dark .user .info p { color: #ccc; }
.user .actions button {
  background: #5b6ef8;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.2s;
}
.user .actions button:hover { opacity: 0.9; }

/* Responsive */
@media (max-width: 900px) {
  body { flex-direction: column; }
  .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e0e0e0; }
  .main { padding: 1rem; }
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
      <a href="chat_users.php" style="background:#f0f3fa;">💬 Chat with Users</a>
      <a href="admin_settings.php">⚙️ Settings</a>
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
      <h1>Select User to Chat</h1>
      <p>Choose a user to start a conversation</p>
    </div>
    <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
  </div>

  <div class="users">
    <?php if(empty($users)): ?>
      <p>No users found!</p>
    <?php else: ?>
      <?php foreach($users as $user): ?>
        <div class="user">
          <div class="info">
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
          </div>
          <div class="actions">
            <button onclick="chatWithUser(<?= $user['id'] ?>)">Chat</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<script>
const toggle = document.getElementById("themeToggle");
toggle.addEventListener("change", () => {
  document.body.classList.toggle("dark");
});

function chatWithUser(userId){
  window.location = 'message/chat.php?user_id=' + userId;
}
</script>

</body>
</html>
