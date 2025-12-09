<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../process/index.html');
    exit;
}

include '../db_config.php'; // adjust path if needed

$user_id = (int) $_SESSION['user_id'];

// Fetch user info
$user_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result();
$user = $user_res->fetch_assoc();
$user_name_json = json_encode($user['name']); // For JSON_CONTAINS

// Handle search and category filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build the WHERE clause
$where_conditions = ["(created_by = ? OR JSON_CONTAINS(attendees, ?))"];
$params = [$user_id, $user_name_json];
$types = 'is';

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Fetch user events with filters
$query = "SELECT id, title, category, date, time, attendees, description, created_by FROM events WHERE $where_clause ORDER BY date DESC, time DESC";
$events_stmt = $conn->prepare($query);
$events_stmt->bind_param($types, ...$params);
$events_stmt->execute();
$events_table = $events_stmt->get_result();

// Fetch unique categories for filter dropdown
$cat_stmt = $conn->prepare("SELECT DISTINCT category FROM events WHERE created_by = ? OR JSON_CONTAINS(attendees, ?) ORDER BY category");
$cat_stmt->bind_param('is', $user_id, $user_name_json);
$cat_stmt->execute();
$categories_result = $cat_stmt->get_result();
$categories = [];
while ($cat_row = $categories_result->fetch_assoc()) {
    $categories[] = $cat_row['category'];
}

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Eventify — My Events</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons (optional) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root{
      --accent: #2f65ff;
      --muted: #6b7280;
      --card-bg: #ffffff;
      --page-bg: #f3f6fb;
    }
    body{ background: var(--page-bg); font-family: Inter, system-ui, Arial, sans-serif; }
    .sidebar{ width: 260px; background: #fff; height: 100vh; box-shadow: 0 2px 12px rgba(20,20,50,0.04); position: fixed; left: 0; top: 0; padding: 34px 24px; }
    .sidebar h1{ color: var(--accent); font-weight: 700; margin-bottom: 4px; }
    .sidebar p.lead{ color: var(--muted); margin-bottom: 22px; }
    .nav-link{ color: #111827; font-weight: 500; padding: 12px 14px; border-radius: 12px; }
    .nav-link .fa-fw{ width: 22px; }
    .nav-link.active{ background: linear-gradient(90deg, rgba(47,101,255,0.12), rgba(47,101,255,0.08)); box-shadow: 0 6px 20px rgba(47,101,255,0.08); color: var(--accent); }

    .main{ margin-left: 260px; padding: 36px 48px; }
    .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom: 18px; }
    .card-ghost{ background: var(--card-bg); border-radius: 14px; padding: 22px; box-shadow: 0 6px 20px rgba(15,20,30,0.04); }
    .section-title{ font-size: 34px; font-weight:700; margin-bottom:6px; }
    .section-sub{ color: var(--muted); margin-bottom: 18px; }

    /* Responsive */
    @media (max-width: 900px){
      .sidebar{ position: static; width: 100%; height: auto; }
      .main{ margin-left: 0; padding: 18px; }
    }
  </style>
</head>
<body>

  <aside class="sidebar">
    <h1>Eventify</h1>
    <p class="lead">Professional event planner</p>

    <nav class="nav flex-column">
      <a class="nav-link mb-2" href="user_dashboard.php"><i class="fa-regular fa-grid-horizontal fa-fw"></i> Dashboard</a>
      <a class="nav-link active mb-2" href="my_events.php"><i class="fa-regular fa-calendar-days fa-fw"></i> My Events</a>
      <a class="nav-link mb-2" href="calendar.php"><i class="fa-regular fa-calendar fa-fw"></i> Calendar</a>
      <a class="nav-link mb-2" href="notifications.php"><i class="fa-regular fa-bell fa-fw"></i> Notifications</a>
      <a class="nav-link mb-2" href="chat.php"><i class="fa-regular fa-comments fa-fw"></i> Chat</a>
      <a class="nav-link mb-3" href="user_settings.php"><i class="fa-regular fa-gear fa-fw"></i> Settings</a>

      <div style="height:18px"></div>
      <div class="border-top pt-3">
        <small class="text-muted">Logged in as</small>
        <div class="mt-1"><strong><?= htmlspecialchars($user['name']) ?></strong></div>
        <div class="mt-3"><a href="../logout.php" class="btn btn-pink btn-sm" style="background:#ff2e7a; color:#fff; border-radius:10px; padding:8px 14px; text-decoration:none;">Logout</a></div>
      </div>
    </nav>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <div class="section-title">My Events</div>
        <div class="section-sub">Manage your events and those you're attending</div>
      </div>
      <div>
        <!-- right side quick button -->
        <a href="create_event.php" class="btn" style="background:var(--accent); color:#fff; border-radius:12px; padding:10px 18px; font-weight:600;">New Event</a>
      </div>
    </div>

    <div class="card-ghost">
      <h5 style="font-weight:700; margin-bottom:12px;">All My Events</h5>

      <!-- Search and Filter Controls -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search events..." value="<?= htmlspecialchars($search) ?>" style="border-radius:8px;">
            <button type="submit" class="btn btn-outline-primary" style="border-radius:8px;"><i class="fa-solid fa-search"></i></button>
            <?php if (!empty($search) || !empty($category_filter)): ?>
              <a href="my_events.php" class="btn btn-outline-secondary" style="border-radius:8px;">Clear</a>
            <?php endif; ?>
          </form>
        </div>
        <div class="col-md-6">
          <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select name="category" class="form-select" onchange="this.form.submit()" style="border-radius:8px;">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>

      <div class="row g-3">
        <?php if ($events_table->num_rows === 0): ?>
          <div class="col-12 text-muted">No events found.</div>
        <?php else: ?>
          <?php while ($row = $events_table->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card-ghost p-3" style="border-radius:12px; background:linear-gradient(180deg, rgba(47,101,255,0.04), rgba(47,101,255,0.02));">
                <div style="font-size:13px; color:#7c3aed; font-weight:700; display:inline-block; padding:6px 10px; border-radius:999px; background:#f3f4f6;"><?= htmlspecialchars($row['category']) ?></div>
                <h6 style="margin-top:10px; margin-bottom:6px; font-weight:600;"><?= htmlspecialchars($row['title']) ?></h6>
                <div style="color:var(--muted); font-size:14px; margin-bottom:8px;"><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($row['date']) ?> · <?= htmlspecialchars($row['time']) ?></div>
                <?php if (!empty($row['description'])): ?>
                  <p style="color:var(--muted); font-size:14px; margin-bottom:10px;"><?= htmlspecialchars(substr($row['description'], 0, 80)) ?><?php if (strlen($row['description']) > 80) echo '...'; ?></p>
                <?php endif; ?>
                <div style="font-size:12px; color:var(--muted); margin-bottom:10px;">
                  <?php
                  $attendees = json_decode($row['attendees'], true);
                  if (is_array($attendees) && count($attendees) > 0) {
                    echo '<i class="fa-solid fa-users"></i> ' . count($attendees) . ' attendee(s)';
                  } else {
                    echo 'No attendees';
                  }
                  ?>
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <button onclick="viewEvent(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-primary">View</button>
                   <button onclick="openMessageModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>', <?= $row['created_by'] ?>)" 
                    class="btn btn-sm btn-outline-info">Message</button>
                  <?php if ($row['created_by'] == $user_id): ?>
                    <span class="badge bg-primary">Creator</span>
                    <a href="edit_event.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                    <a href="delete_event.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this event?');">Delete</a>
                  <?php else: ?>
                    <span class="badge bg-secondary">Attendee</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

    </div>

  </main>

<?php include 'modals/view_event_modal.php'; ?>
<?php include 'modals/message_modal.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
