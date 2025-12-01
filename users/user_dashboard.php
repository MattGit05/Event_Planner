<?php
session_start();

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: process/index.html");
    exit;
}


include '../db_config.php';

// Fetch user's events
$stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | Eventify</title>
<link rel="stylesheet" href="user_dashboard.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Eventify</h2>
    <a href="user_dashboard.php" class="active">My Dashboard</a>
    <a href="user_events.php">My Events</a>
    <a href="user_profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <h1>Welcome back, <?php echo $_SESSION['name']; ?> 👋</h1>

    <div class="stats-container">
        <div class="card">
            <h2>Total Events</h2>
            <p>
                <?php echo $result->num_rows; ?>
            </p>
        </div>

        <div class="card">
            <h2>Upcoming</h2>
            <p>
                <?php
                $up = $conn->query("SELECT COUNT(*) AS total FROM events WHERE created_by=".$_SESSION['user_id']." AND date >= CURDATE()")->fetch_assoc();
                echo $up['total'];
                ?>
            </p>
        </div>

        <div class="card">
            <h2>Completed</h2>
            <p>
                <?php
                $done = $conn->query("SELECT COUNT(*) AS total FROM events WHERE created_by=".$_SESSION['user_id']." AND date < CURDATE()")->fetch_assoc();
                echo $done['total'];
                ?>
            </p>
        </div>
    </div>

    <h2>Your Events</h2>
    <table class="events-table">
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Category</th>
            <th>Status</th>
        </tr>
        
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td>
                    <?php echo ($row['date'] >= date('Y-m-d')) ? "Upcoming" : "Completed"; ?>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>


<script>
(function(){
  const params = new URLSearchParams(window.location.search);
  if (params.get('open_edit')) {
    const id = params.get('open_edit');
    document.getElementById('editEventModal').style.display='block';
    loadEventToEdit(id);
    history.replaceState(null, '', 'user_dashboard.php');
  }
  if (params.get('create_on')) {
    const d = params.get('create_on');
    document.getElementById('addEventModal').style.display='block';
    const dateInput = document.querySelector('#addEventForm input[name="date"]');
    if (dateInput) dateInput.value = d;
    history.replaceState(null, '', 'user_dashboard.php');
  }
})();
</script>

</body>
</html>
