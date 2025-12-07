<?php
// budget.php
require '../db_config.php';
session_start();

// get budgets with event info
$sql = "SELECT e.id as event_id, e.title, b.allocated_budget, b.total_spent
        FROM events e
        LEFT JOIN event_budgets b ON b.event_id = e.id
        ORDER BY e.date ASC";
$res = $conn->query($sql);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Budget Management — Eventify</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<style>
/* --- CSS Variables (Consistent with other pages) --- */
:root {
    --primary-color: #007bff; /* Primary Blue */
    --danger-color: #ff4d6d; /* Logout Red */
    --success-color: #00d26a; /* Chart Green */
    --info-color: #4361ee; /* Event Border Blue */
    --background-light: #f6f8fc;
    --surface-light: #ffffff;
    --text-color-light: #1a1a1a;
    --secondary-color: #6c757d;
    --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.08);
    --header-bg-light: #e9ecef;
    --accent-purple: #6a00f4; /* Primary action color (Add Event/Save) */
}

body.dark {
    --primary-color: #5b6ef8;
    --danger-color: #dc3545;
    --background-light: #1e1e2e;
    --surface-light: #2b2b3d;
    --text-color-light: #f5f5f5;
    --secondary-color: #adb5bd;
    --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.4);
    --header-bg-light: #3a3a50;
    --accent-purple: #7d8bff;
}

/* --- Base Styles --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    display: flex;
    min-height: 100vh;
    background-color: var(--background-light);
    color: var(--text-color-light);
    transition: background 0.3s, color 0.3s;
}

/* --- Sidebar Styles (Consistent) --- */
.sidebar {
    width: 250px;
    background: var(--surface-light);
    padding: 2rem 1.5rem;
    box-shadow: var(--shadow-default);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-right: 1px solid rgba(0,0,0,0.05);
}

.logo {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.subtitle {
    font-size: 0.8rem;
    color: var(--secondary-color);
    margin-bottom: 2rem;
}

.nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav a {
    text-decoration: none;
    color: var(--text-color-light);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    transition: background 0.2s, color 0.2s;
}

.nav a:hover {
    background: rgba(0, 123, 255, 0.1);
    color: var(--primary-color);
}

.nav a.active {
    background: var(--primary-color);
    color: white !important;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

.bottom-section {
    margin-top: 3rem;
    font-size: 0.9rem;
    color: var(--secondary-color);
}

.theme-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding: 0.5rem 0;
}

/* --- Main Dashboard --- */
.main {
    flex: 1;
    padding: 2.5rem 3rem;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--header-bg-light);
    flex-wrap: wrap;
    gap: 1rem;
}

.header h1 {
    font-size: 2rem;
    color: var(--text-color-light);
    font-weight: 600;
}

.header p {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.add-btn {
    background: var(--accent-purple);
    color: white;
    border: none;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
    box-shadow: 0 4px 12px rgba(106, 0, 244, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.add-btn:hover {
    background: #5500c8;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(106, 0, 244, 0.4);
}

.add-btn:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(106, 0, 244, 0.3);
}

.logout-btn {
    background: var(--danger-color);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s, transform 0.2s;
}
.logout-btn:hover { background: #c82333; transform: translateY(-1px); }

/* small helper styles */
.budget-card { background:#fff; padding:18px; border-radius:12px; box-shadow:0 6px 18px rgba(20,30,60,.06); margin-bottom:16px; }
.progress { height:10px; background:#eee; border-radius:6px; overflow:hidden; }
.progress > span { display:block; height:100%; }

/* Table styles */
.table {
    width: 100%;
    border-collapse: collapse;
    background: var(--surface-light);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-default);
}

.table th, .table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--header-bg-light);
}

.table th {
    background: var(--header-bg-light);
    font-weight: 600;
    color: var(--text-color-light);
}

.table td {
    color: var(--text-color-light);
}

.btn {
    background: var(--accent-purple);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}

.btn:hover {
    background: #5500c8;
}

/* Responsiveness */
@media (max-width: 768px) {
    body { flex-direction: column; }
    .sidebar { width: 100%; height: auto; padding: 1rem; }
    .main { padding: 1.5rem; }
    .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .logout-btn { align-self: flex-end; }
}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <div class="header">
    <div>
      <h1>Budget Management</h1>
      <p>Track and manage event budgets</p>
    </div>
    <button class="add-btn" onclick="window.location='add_expense.php'">
      <span class="material-icons-round" style="font-size: 1.4rem; vertical-align: -3px;">add</span> Add Expense
    </button>
  </div>

  <div class="grid">
    <?php if ($res && $res->num_rows): ?>
      <table class="table">
        <thead>
          <tr>
            <th>Event</th>
            <th>Allocated (₱)</th>
            <th>Spent (₱)</th>
            <th>Remaining (₱)</th>
            <th>Progress</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): 
            $allocated = floatval($row['allocated_budget'] ?? 0);
            $spent = floatval($row['total_spent'] ?? 0);
            $remaining = $allocated - $spent;
            $pct = $allocated > 0 ? min(100, round(($spent / $allocated)*100)) : ($spent > 0 ? 100 : 0);
        ?>
          <tr>
            <td><?=htmlspecialchars($row['title'])?></td>
            <td><?=number_format($allocated,2)?></td>
            <td><?=number_format($spent,2)?></td>
            <td><?=number_format($remaining,2)?></td>
            <td style="width: 220px;">
              <div class="progress">
                <span style="width:<?=$pct?>%; background: <?=$pct>=90? '#ff5a5f' : ($pct>=70? '#ffb86b' : '#4caf50')?>;"></span>
              </div>
              <small><?=$pct?>% used</small>
            </td>
            <td>
              <a href="budget_event.php?event_id=<?=$row['event_id']?>" class="btn">View / Add</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No events found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
    // Dark mode toggle
    const toggle = document.getElementById("themeToggle");
    // Initial check for theme
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
        toggle.checked = true;
    }

    toggle.addEventListener("change", () => {
        document.body.classList.toggle("dark");
        if (document.body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    });
</script>
</body>
</html>
