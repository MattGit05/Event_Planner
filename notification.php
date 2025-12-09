<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: process/index.html");
    exit;
}

include 'db_config.php';
$admin_id = $_SESSION['user_id'];


// -------------------------------------------------------
// 1️⃣ Get SYSTEM notifications
// -------------------------------------------------------
$stmt = $conn->prepare("
    SELECT n.id, n.title, n.message, n.type, n.is_read, n.created_at, u.name AS sender_name
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.id
    WHERE n.user_id = ? 
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$system_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// -------------------------------------------------------
// 2️⃣ Get USER → ADMIN messages from messages table
// -------------------------------------------------------
$sql = "
    SELECT 
        m.id,
        m.message,
        m.created_at,
        m.sender_id,
        u.name AS sender_name,
        e.title AS event_title, 
        m.is_read,
        'user_message' AS type
    FROM messages m
    LEFT JOIN users u ON m.sender_id = u.id
    LEFT JOIN events e ON m.event_id = e.id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
";

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $admin_id);
$stmt2->execute();
$user_messages = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();



// Merge both lists
$notifications = array_merge($user_messages, $system_notifications);

// Sort newest first
usort($notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - Eventify</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>
/* --- CSS Variables (Consistent with other pages, derived from my-events.php) --- */
:root {
    --primary-color: #007bff; /* Primary Blue */
    --accent-color: #ff6b6b; /* Soft Red Accent for emphasis/danger */
    --success-color: #28a745;
    --danger-color: #dc3545; 
    --accent-purple: #6a00f4; /* Primary action color (Add Event/Save) */
    --background-light: #f6f8fc;
    --surface-light: #ffffff;
    --text-color-light: #1a1a1a;
    --secondary-color: #6c757d;
    --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.08);
    --header-bg-light: #e9ecef;
    --border-color: #e0e0e0;
}

/* --- Dark Theme Variables --- */
body.dark {
    --primary-color: #7d8bff; 
    --accent-color: #ff8a8a;
    --success-color: #26b949;
    --danger-color: #dc3545;
    --accent-purple: #9b59b6; 
    --background-light: #1c1c28; 
    --surface-light: #2c2c3e; 
    --text-color-light: #ffffff;
    --secondary-color: #b5b5c3;
    --shadow-default: 0 6px 16px rgba(0, 0, 0, 0.5);
    --header-bg-light: #3a3a50;
    --border-color: #44445c;
}

/* --------- GLOBAL & SIDEBAR STYLES (Consistent with my-events.php) --------- */
* { 
    margin:0; 
    padding:0; 
    box-sizing:border-box; 
    font-family:"Poppins", sans-serif;
    transition: background 0.3s, color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

body { 
    display:flex; 
    min-height:100vh; 
    background-color:var(--background-light); 
    color:var(--text-color-light); 
}

.sidebar {
    width:250px;
    background:var(--surface-light);
    padding:2rem 1.5rem;
    box-shadow:var(--shadow-default);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    border-right: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.logo { font-size:2rem; font-weight:800; color:var(--primary-color); margin-bottom:0.5rem; }
.subtitle { font-size:0.85rem; color:var(--secondary-color); margin-bottom:2.5rem; }

.nav { display:flex; flex-direction:column; gap:0.25rem; }
.nav a { 
    text-decoration:none; 
    color:var(--text-color-light); 
    font-weight:500; 
    display:flex; 
    align-items:center; 
    gap:1rem; 
    padding:0.9rem 1rem;
    border-radius:12px;
    transition:background 0.2s, color 0.2s, transform 0.1s; 
}
.nav a:hover { 
    background:rgba(0, 123, 255, 0.1);
    color: var(--primary-color);
    transform: translateX(3px);
}
.nav a.active {
    background: var(--primary-color) !important; /* Override inline style */
    color: white !important;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.35);
}
.nav a .material-icons-round { 
    font-size: 20px;
}

/* --------- MAIN STYLES (Notifications Specific) --------- */
.main { 
    flex:1; 
    padding:3rem 3.5rem; 
    overflow-y:auto;
}

.header { 
    margin-bottom:2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}
.header h1 { 
    font-size:2.2rem; 
    font-weight: 700;
    color:var(--text-color-light);
}
.header p {
    color: var(--secondary-color);
    font-size: 1rem;
    margin-top: 0.2rem;
}


.notifications-container {
    padding-top: 1rem;
}

.notification-card { 
    background: var(--surface-light);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-default);
    display: flex;
    justify-content: space-between;
    align-items: flex-start; /* Align content to the top */
    gap: 20px;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
    border: 1px solid transparent;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}

/* UNREAD state */
.notification-card.unread {
    background: var(--header-bg-light); /* Slightly highlighted background */
    border: 1px solid var(--primary-color); /* Strong primary color border */
}
body.dark .notification-card.unread {
    background: #333348;
    border-color: var(--primary-color);
}


/* Notification Content */
.info {
    flex-grow: 1;
}

.info h3 {
    font-size: 1.15rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.4;
}

.notification-type {
    font-size: 0.8rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    text-transform: uppercase;
}

.type-message {
    background: var(--accent-purple);
    color: white;
}
.type-system {
    background: var(--primary-color);
    color: white;
}

.info h3 small {
    font-weight: 500;
    color: var(--secondary-color);
    font-size: 0.9rem;
    margin-left: 5px;
}

.info p {
    margin: 0.5rem 0;
    color: var(--text-color-light);
    line-height: 1.5;
}

.info strong {
    font-weight: 600;
}

.info small {
    display: block;
    margin-top: 0.8rem;
    font-size: 0.8rem;
    color: var(--secondary-color);
}

/* Actions Section */
.actions {
    display: flex;
    align-items: center;
    padding-left: 20px;
    border-left: 1px solid var(--border-color); /* Visual divider */
}
body.dark .actions {
    border-left-color: var(--border-color);
}

.reply-btn { 
    background: var(--primary-color);
    color: white; 
    padding: 0.7rem 1.4rem; 
    border-radius: 10px; 
    border: none; 
    cursor: pointer; 
    font-weight: 600;
    font-size: 0.9rem;
    transition: background 0.2s, transform 0.1s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.reply-btn:hover { 
    background: #0056b3; 
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    body { flex-direction: column; }
    .sidebar { width: 100%; height: auto; padding: 1rem; }
    .main { padding: 1.5rem; }
    .notification-card { flex-direction: column; align-items: stretch; }
    .actions { padding-left: 0; border-left: none; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem; justify-content: flex-end; }
    .info h3 { flex-direction: column; align-items: flex-start; gap: 4px; }
    .info h3 small { margin-left: 0; margin-top: 4px; }
}
</style>
</head>

<body class="dark"> <aside class="sidebar">
    <div>
        <div class="logo">Eventify</div>
        <div class="subtitle">Professional event planner</div>
        <nav class="nav">
            <a href="index.php"><span class="material-icons-round">dashboard</span> Dashboard</a>
            <a href="my-events.php"><span class="material-icons-round">event</span> My Events</a>
            <a href="calendar.php"><span class="material-icons-round">calendar_month</span> Calendar</a>
            <a href="users.php"><span class="material-icons-round">group</span> View Users</a>
            <a href="notifications.php" class="active"><span class="material-icons-round">notifications</span> Notifications</a>
            <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
            <a href="Budgeting/budget.php"><span class="material-icons-round">account_balance_wallet</span> Budget</a>
        </nav>
    </div>
</aside>

<main class="main">
    <div class="header">
        <h1>Notifications</h1>
        <p>All system alerts and user messages that require your attention.</p>
    </div>

    <div class="notifications-container">
        <?php foreach($notifications as $n): ?>
            <div class="notification-card <?= $n['is_read'] ? '' : 'unread' ?>">
                
                <div class="info">
                    <h3>
                        <span class="notification-type <?= ($n['type'] === 'user_message') ? 'type-message' : 'type-system' ?>">
                            <?= ($n['type'] === 'user_message') ? 'Message' : 'System Alert' ?>
                        </span>
                        
                        <?php if(!empty($n['sender_name'])): ?>
                            <small>from <?= htmlspecialchars($n['sender_name']) ?></small>
                        <?php endif; ?>
                    </h3>

                    <?php if($n['type'] === 'user_message' && !empty($n['event_title'])): ?>
                        <p><strong>Event:</strong> <?= htmlspecialchars($n['event_title']) ?></p>
                    <?php endif; ?>

                    <p><?= htmlspecialchars($n['message']) ?></p>
                    <small><?= date("M d, Y • h:i A", strtotime($n['created_at'])) ?></small>
                </div>

                <div class="actions">
                    <?php if($n['type'] === 'user_message'): ?>
                        <button class="reply-btn" onclick="replyChat(<?= $n['sender_id'] ?>, '<?= htmlspecialchars($n['sender_name']) ?>')">
                            <span class="material-icons-round">reply</span> Reply
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
function replyChat(userId, name){
    // This function remains the same, redirecting to the chat interface.
    window.location.href = "message/chat.php?user_id=" + userId;
}

// Example Dark Mode Toggle Script (assuming the element exists elsewhere)
const themeToggle = document.getElementById("themeToggle");
if (themeToggle) {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
        themeToggle.checked = true;
    }
    themeToggle.addEventListener("change", () => {
        document.body.classList.toggle("dark");
        if (document.body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    });
}
</script>

</body>
</html>