<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: process/index.html");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Calendar — Eventify</title>

  <!-- FullCalendar v5 -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root{
      --accent: #2f65ff;
      --muted: #6b7280;
      --card-bg: #ffffff;
      --page-bg: #f3f6fb;
    }

    body {
      background: var(--page-bg);
      font-family: Inter, system-ui, sans-serif;
      margin: 0;
      padding: 0;
    }

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
      margin-bottom: 6px;
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
      box-shadow: 0 6px 20px rgba(47,101,255,0.08);
    }

    .main {
      margin-left: 260px;
      padding: 40px 50px;
    }

    .top-title {
      font-size: 34px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    #calendar {
      background: white;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.05);
    }

    .calendar-wrapper {
      margin-top: 20px;
      min-height: 650px;
    }
  </style>
</head>

<body>

  <!-- SIDEBAR -->
  <aside class="sidebar">
      <h1>Eventify</h1>
      <p class="lead" style="color:var(--muted); margin-bottom:20px;">Professional event planner</p>

      <a class="nav-link" href="user_dashboard.php"><i class="fa-solid fa-table-columns"></i> Dashboard</a>
      <a class="nav-link" href="my_events.php"><i class="fa-regular fa-calendar-days"></i> My Events</a>
      <a class="nav-link active" href="calendar.php"><i class="fa-regular fa-calendar"></i> Calendar</a>
      <a class="nav-link" href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
      <a class="nav-link" href="user_settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

      <div style="margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 15px;">
          Logged in as:  
          <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></strong><br><br>
          <a href="../logout.php" style="display:inline-block; background:#ff2e7a; color:white; padding:8px 16px; border-radius:10px; text-decoration:none;">Logout</a>
      </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main">
      <div class="top-title">Calendar</div>
      <div class="calendar-wrapper">
        <div id="calendar"></div>
      </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {

  var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    height: "auto",

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },

    selectable: true,

    events: 'events_feed.php',

    eventClick: function(info) {
      window.location = 'user_dashboard.php?open_edit=' + info.event.id;
    },

    select: function(selectionInfo) {
      window.location = 'user_dashboard.php?create_on=' + selectionInfo.startStr;
    }
  });

  calendar.render();
});
</script>

</body>
</html>
