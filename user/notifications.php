<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: process/index.html");
    exit;
}

require "../db_config.php";

// Fetch user notifications
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifications — Eventify</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --accent: #2f65ff;
            --muted: #6b7280;
            --card-bg: #ffffff;
            --page-bg: #f3f6fb;
        }

        body {
            background: var(--page-bg);
            font-family: Inter, system-ui, sans-serif;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 34px 24px;
            box-shadow: 0 2px 12px rgba(20,20,50,0.04);
        }

        .sidebar h1 {
            color: var(--accent);
            font-size: 30px;
            font-weight: 700;
        }

        .nav-link {
            display: block;
            padding: 12px 15px;
            border-radius: 12px;
            color: #1f2937;
            font-weight: 500;
            margin-bottom: 8px;
            text-decoration: none;
        }

        .nav-link i {
            width: 20px;
            margin-right: 8px;
        }

        .nav-link.active {
            background: rgba(47,101,255,0.12);
            color: var(--accent);
        }

        /* MAIN */
        .main {
            margin-left: 260px;
            padding: 40px 50px;
        }

        .page-title {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .note-card {
            background: white;
            padding: 20px 22px;
            border-radius: 14px;
            margin-bottom: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.05);
            border-left: 6px solid transparent;
            transition: 0.2s;
        }

        .note-card.unread {
            border-left-color: var(--accent);
            background: #f0f4ff;
        }

        .note-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .note-message {
            color: #374151;
            margin-bottom: 6px;
        }

        .note-time {
            font-size: 14px;
            color: var(--muted);
        }

        .mark-read {
            float: right;
            background: var(--accent);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <h1>Eventify</h1>
    <p style="color:var(--muted); margin-bottom:20px;">Professional event planner</p>

    <a class="nav-link" href="user_dashboard.php"><i class="fa-solid fa-table-columns"></i> Dashboard</a>
    <a class="nav-link" href="my_events.php"><i class="fa-regular fa-calendar-days"></i> My Events</a>
    <a class="nav-link" href="calendar.php"><i class="fa-regular fa-calendar"></i> Calendar</a>
    <a class="nav-link active" href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
    <a class="nav-link" href="user_settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

    <div style="margin-top:30px; border-top:1px solid #e5e7eb; padding-top:15px;">
        Logged in as:
        <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></strong><br><br>
        <a href="../logout.php" style="display:inline-block; background:#ff2e7a; color:white; padding:8px 16px; border-radius:10px; text-decoration:none;">Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="page-title">Notifications</div>

    <?php if (count($notifications) === 0): ?>
        <p style="color: var(--muted); font-size:18px;">No notifications yet.</p>
    <?php else: ?>

        <?php foreach ($notifications as $note): ?>
            <div class="note-card <?php echo $note['is_read'] ? '' : 'unread'; ?>">
                <div class="note-title"><?php echo htmlspecialchars($note['title']); ?></div>
                <div class="note-message"><?php echo htmlspecialchars($note['message']); ?></div>
                <div class="note-time">
                    <i class="fa-regular fa-clock"></i>
                    <?php echo date("F j, Y • g:i A", strtotime($note['created_at'])); ?>
                </div>

                <?php if (!$note['is_read']): ?>
                    <a href="read_notification.php?id=<?php echo $note['id']; ?>" class="mark-read">Mark as read</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>
