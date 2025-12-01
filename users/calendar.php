<?php
// calendar.php
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
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
  <style>
    body { font-family: "Poppins", sans-serif; margin:0; }
    .sidebar { width:250px; position:fixed; height:100vh; padding:24px; background:linear-gradient(180deg,#2563eb,#1e40af); color:white; box-shadow:2px 0 10px rgba(0,0,0,.12); }
    .content { margin-left:270px; padding:20px; }
    #calendar { background:#fff; border-radius:10px; padding:12px; box-shadow:0 6px 20px rgba(0,0,0,.08); }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Eventify</h2>
    <a href="user_dashboard.php" style="color:#dbeafe;display:block;margin:8px 0;text-decoration:none;">Dashboard</a>
    <a href="calendar.php" style="color:#fff;font-weight:700;display:block;margin:8px 0;text-decoration:none;">📅 Calendar</a>
    <a href="user_profile.php" style="color:#dbeafe;display:block;margin:8px 0;text-decoration:none;">Profile</a>
    <a href="logout.php" style="color:#dbeafe;display:block;margin:8px 0;text-decoration:none;">Logout</a>
  </div>
  <div class="content">
    <h1>Calendar</h1>
    <div id='calendar'></div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: 'events_feed.php',
    eventClick: function(info) {
      // open edit modal preloaded
      var id = info.event.id;
      if (confirm('Open event "' + info.event.title + '" to edit?')) {
        // open edit modal (reuse dashboard's modal or redirect to dashboard with ?edit=)
        // We'll open a small window to edit
        window.location = 'user_dashboard.php?open_edit=' + id;
      }
    },
    select: function(selectionInfo) {
      // quick create using date selected
      var dateStr = selectionInfo.startStr;
      if (confirm('Create event on ' + dateStr + '?')) {
        // open dashboard and prefill date via query param
        window.location = 'user_dashboard.php?create_on=' + encodeURIComponent(dateStr);
      }
    },
    eventDidMount: function(info) {
      // add tooltip or small style based on category
      // info.event.extendedProps.category is available
    }
  });

  calendar.render();
});
</script>
</body>
</html>
