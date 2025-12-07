
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: process/index.html");
    exit;
}

require "../db_config.php";

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Settings — Eventify</title>

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

        .settings-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
            margin-bottom: 22px;
        }

        .settings-card h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-bottom: 16px;
            font-size: 16px;
        }

        input:focus {
            border-color: var(--accent);
            outline: none;
        }

        .save-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 600;
        }

        .save-btn:hover {
            opacity: 0.9;
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
    <a class="nav-link" href="user_notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
    <a class="nav-link active" href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

    <div style="margin-top:30px; border-top:1px solid #e5e7eb; padding-top:15px;">
        Logged in as:
        <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></strong><br><br>
        <a href="../logout.php" style="display:inline-block; background:#ff2e7a; color:white; padding:8px 16px; border-radius:10px; text-decoration:none;">Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="page-title">Settings</div>

    <!-- Profile Settings -->
    <form action="update_settings.php" method="POST">
        <div class="settings-card">
            <h2>Profile Information</h2>

            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <!-- Password Settings -->
        <div class="settings-card">
            <h2>Change Password</h2>

            <label>Current Password</label>
            <input type="password" name="current_password">

            <label>New Password</label>
            <input type="password" name="new_password">

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password">
        </div>

        <button class="save-btn" type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
