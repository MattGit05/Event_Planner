<?php
// budget_event.php
require '../db_config.php';
session_start();

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if (!$event_id) { header("Location: budget.php"); exit; }

// fetch event and budget
$stmt = $conn->prepare("
  SELECT e.*, b.allocated_budget, b.total_spent
  FROM events e
  LEFT JOIN event_budgets b ON b.event_id = e.id
  WHERE e.id = ?
");
$stmt->bind_param("i",$event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) { header("Location: budget.php"); exit; }

// fetch expenses
$stmt2 = $conn->prepare("SELECT * FROM event_expenses WHERE event_id = ? ORDER BY created_at DESC");
$stmt2->bind_param("i",$event_id);
$stmt2->execute();
$expenses = $stmt2->get_result();
$stmt2->close();

$allocated = floatval($event['allocated_budget'] ?? 0);
$spent = floatval($event['total_spent'] ?? 0);
$remaining = $allocated - $spent;
$pct = $allocated > 0 ? min(100, round(($spent/$allocated)*100)) : ($spent>0 ? 100:0);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Budget for <?=htmlspecialchars($event['title'])?></title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

/* Card styles */
.card {
    background: var(--surface-light);
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: var(--shadow-default);
    margin-bottom: 1.5rem;
}

.card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color-light);
    margin-bottom: 1rem;
}

/* Progress bar */
.progress {
    height: 12px;
    background: #eee;
    border-radius: 6px;
    overflow: hidden;
    margin-top: 8px;
}

.progress > span {
    display: block;
    height: 100%;
    background: var(--success-color);
    transition: width 0.3s ease;
}

.progress.over-budget > span {
    background: var(--danger-color);
}

.progress.warning > span {
    background: #ffb86b;
}

/* Form styles */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-color-light);
}

.form-control {
    width: 100%;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    border: 1px solid #ccc;
    outline: none;
    background: var(--background-light);
    color: var(--text-color-light);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
}

body.dark .form-control {
    border: 1px solid #555;
    background: #1e1e2e;
}

/* Button styles */
.btn {
    background: var(--accent-purple);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
    display: inline-block;
}

.btn:hover {
    background: #5500c8;
}

.btn-secondary {
    background: var(--secondary-color);
}

.btn-secondary:hover {
    background: #5a6268;
}

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

/* Budget summary */
.budget-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.budget-summary div {
    flex: 1;
}

.budget-summary .progress-container {
    flex: 1;
    max-width: 300px;
}

/* Layout for add expense and expenses */
.expense-layout {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .expense-layout {
        grid-template-columns: 1fr;
    }
}

/* Responsiveness */
@media (max-width: 768px) {
    body { flex-direction: column; }
    .sidebar { width: 100%; height: auto; padding: 1rem; }
    .main { padding: 1.5rem; }
    .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .logout-btn { align-self: flex-end; }
    .budget-summary { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <div class="header">
    <div>
      <h1>Budget — <?=htmlspecialchars($event['title'])?></h1>
      <p>Manage expenses and track spending</p>
    </div>
    <a href="budget.php" class="btn btn-secondary">← Back to Budgets</a>
  </div>

  <div class="card">
    <h3>Budget Overview</h3>
    <div class="budget-summary">
      <div>
        <p><strong>Allocated:</strong> ₱<?=number_format($allocated,2)?></p>
        <p><strong>Spent:</strong> ₱<span id="spent_val"><?=number_format($spent,2)?></span></p>
        <p><strong>Remaining:</strong> ₱<span id="rem_val"><?=number_format($remaining,2)?></span></p>
      </div>
      <div class="progress-container">
        <div class="progress <?=$pct >= 90 ? 'over-budget' : ($pct >= 70 ? 'warning' : '')?>">
          <span id="progress_bar" style="width:<?=$pct?>%;"></span>
        </div>
        <small id="pct_text"><?=$pct?>% used</small>
      </div>
    </div>
  </div>

  <div class="expense-layout">
    <div>
      <div class="card">
        <h3>Add New Expense</h3>
        <form id="expenseForm">
          <input type="hidden" name="event_id" value="<?=$event_id?>">
          <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="item_name" class="form-control" placeholder="e.g., Catering, Venue Rental" required>
          </div>
          <div class="form-group">
            <label>Amount (₱)</label>
            <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="0.00" required>
          </div>
          <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control">
              <option value="">Select category</option>
              <option>Venue</option>
              <option>Food</option>
              <option>Logistics</option>
              <option>Decoration</option>
              <option>Misc</option>
            </select>
          </div>
          <button type="submit" class="btn">Add Expense</button>
          <span id="form_msg" style="margin-left: 10px; color: var(--success-color);"></span>
        </form>
      </div>
    </div>

    <div>
      <div class="card">
        <h3>Expense History</h3>
        <div style="max-height: 400px; overflow-y: auto;">
          <table class="table" id="expenses_table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Amount (₱)</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($expenses->num_rows > 0): ?>
              <?php while ($exp = $expenses->fetch_assoc()): ?>
                <tr>
                  <td><?=htmlspecialchars($exp['item_name'])?></td>
                  <td>
                    <span style="background: var(--accent-purple); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">
                      <?=htmlspecialchars($exp['category'])?>
                    </span>
                  </td>
                  <td>₱<?=number_format(floatval($exp['amount']),2)?></td>
                  <td><?=date('M d, Y', strtotime($exp['created_at']))?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--secondary-color); padding: 2rem;">
                  No expenses recorded yet.
                </td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$('#expenseForm').on('submit', function(e){
  e.preventDefault();
  $('#form_msg').text('Saving...');
  $.ajax({
    url: 'add_expense.php',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json'
  }).done(function(resp){
    if(resp.success){
      // append new expense row with proper formatting
      let categoryBadge = `<span style="background: var(--accent-purple); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">${resp.category}</span>`;
      let tr = `<tr><td>${resp.item_name}</td><td>${categoryBadge}</td><td>₱${parseFloat(resp.amount).toFixed(2)}</td><td>${new Date().toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td></tr>`;
      $('#expenses_table tbody').prepend(tr);

      // update budget totals UI
      $('#spent_val').text(parseFloat(resp.total_spent).toFixed(2));
      $('#rem_val').text(parseFloat(resp.remaining).toFixed(2));
      $('#progress_bar').css('width', resp.pct+'%');
      $('#pct_text').text(resp.pct + '% used');
      $('#form_msg').text('Expense added successfully!').fadeOut(2000);
      $('#expenseForm')[0].reset();
    } else {
      $('#form_msg').text('Error: '+ (resp.message || 'unknown'));
    }
  }).fail(function(xhr){
    $('#form_msg').text('Request error');
  });
});

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
