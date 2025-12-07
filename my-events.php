<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: process/index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Eventify | My Events</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<style>
/* --- CSS Variables (Consistent with other pages) --- */
:root {
    --primary-color: #007bff; /* Primary Blue */
    --accent-color: #ff6b6b; /* New: Soft Red Accent for emphasis/danger */
    --success-color: #28a745;
    --danger-color: #dc3545; /* Logout Red (slightly adjusted from original) */
    --accent-purple: #6a00f4; /* Primary action color (Add Event/Save) */
    --background-light: #f6f8fc;
    --surface-light: #ffffff;
    --text-color-light: #1a1a1a;
    --secondary-color: #6c757d;
    --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.08);
    --header-bg-light: #e9ecef;
    --border-color: #e0e0e0; /* New: for inputs/dividers */
}

/* --- Dark Theme Variables --- */
body.dark {
    --primary-color: #7d8bff; /* Lighter blue for dark contrast */
    --accent-color: #ff8a8a;
    --success-color: #26b949;
    --danger-color: #dc3545;
    --accent-purple: #9b59b6; /* Slightly different purple for contrast */
    --background-light: #1c1c28; /* Deeper background */
    --surface-light: #2c2c3e; /* Darker surface */
    --text-color-light: #ffffff;
    --secondary-color: #b5b5c3;
    --shadow-default: 0 6px 16px rgba(0, 0, 0, 0.5); /* Stronger dark shadow */
    --header-bg-light: #3a3a50;
    --border-color: #44445c;
}

/* --------- GLOBAL & SIDEBAR STYLES (Consistent) --------- */
* { 
    margin:0; 
    padding:0; 
    box-sizing:border-box; 
    font-family:"Poppins", sans-serif;
    /* Smooth out all transitions */
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
    border-right: 1px solid var(--border-color); /* Use variable */
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.logo { font-size:2rem; font-weight:800; color:var(--primary-color); margin-bottom:0.5rem; }
.subtitle { font-size:0.85rem; color:var(--secondary-color); margin-bottom:2.5rem; } /* Adjusted margin/size */

.nav { display:flex; flex-direction:column; gap:0.25rem; } /* Reduced gap */
.nav a { 
    text-decoration:none; 
    color:var(--text-color-light); 
    font-weight:500; 
    display:flex; 
    align-items:center; 
    gap:1rem; 
    padding:0.9rem 1rem; /* Slightly more padding */
    border-radius:12px; /* Softer corners */
    transition:background 0.2s, color 0.2s, transform 0.1s; 
}
.nav a:hover { 
    background:rgba(0, 123, 255, 0.1);
    color: var(--primary-color);
    transform: translateX(3px); /* Subtle hover effect */
}
.nav a.active {
    background: var(--primary-color);
    color: white !important;
    font-weight: 600; /* Bolder active link */
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.35); /* Stronger shadow */
}

.bottom-section { 
    margin-top:4rem; 
    font-size:0.9rem; 
    color:var(--secondary-color);
}
.theme-toggle { 
    display:flex; 
    align-items:center; 
    justify-content: space-between;
    margin-top:1rem;
    padding: 0.5rem 0;
}

/* --------- MAIN STYLES --------- */
.main { 
    flex:1; 
    padding:3rem 3.5rem; /* Increased padding */
    overflow-y:auto;
}

.header { 
    display:flex; 
    justify-content:space-between; 
    align-items:flex-start; /* Align to top */
    flex-wrap:wrap; 
    gap:1.5rem; 
    margin-bottom:2rem; /* Increased margin */
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color); /* Thinner border */
}

.header h1 { 
    font-size:2.2rem; 
    color:var(--text-color-light);
    font-weight: 700;
}
.header p {
    color: var(--secondary-color);
    font-size: 1rem;
    margin-top: 0.2rem;
}

.logout-btn { 
    background:var(--danger-color); 
    color:white; 
    border:none; 
    padding:0.7rem 1.4rem; /* More prominent button */
    border-radius:10px; 
    cursor:pointer; 
    font-weight:600;
    transition: background 0.2s, transform 0.2s;
}
.logout-btn:hover { 
    background: #c82333; 
    transform: translateY(-1px);
}

.filters { 
    display:flex; 
    justify-content:flex-end; 
    gap:1rem; 
    flex-wrap:wrap; 
    margin-bottom:2rem; 
    padding: 0.5rem 0;
}

.filters input, .filters select { 
    padding:0.75rem 1rem; /* More vertical padding */
    border:1px solid var(--border-color); 
    border-radius:10px; /* Softer corners */
    font-size:0.9rem;
    background: var(--surface-light);
    color: var(--text-color-light);
    transition: border-color 0.2s, box-shadow 0.2s;
    min-width: 150px;
}
body.dark .filters input, body.dark .filters select {
    background: var(--surface-light); /* Keep consistent with general inputs */
}
.filters input:focus, .filters select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15); /* Subtle focus ring */
    outline: none;
}


.events-container { 
    display:grid; 
    grid-template-columns:repeat(auto-fit, minmax(320px,1fr)); /* Slightly wider min width */
    gap:2rem; /* Increased gap */
}

.event-card { 
    background:var(--surface-light); 
    border-radius:18px; /* Rounder corners */
    padding:1.8rem; /* More padding */
    border: 1px solid transparent; /* New: subtle border */
    box-shadow:var(--shadow-default); 
    display:flex; 
    flex-direction:column; 
    justify-content:space-between;
    transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
}
.event-card:hover {
    transform: translateY(-5px); /* More noticeable lift */
    box-shadow: 0 10px 20px rgba(0,0,0,0.15); /* Stronger shadow */
    border-color: var(--primary-color); /* Highlight border on hover */
}
body.dark .event-card:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.4);
}

.event-card h3 { 
    font-size:1.5rem; 
    margin:0.5rem 0 0.4rem 0;
    font-weight: 700;
    line-height: 1.2;
}
.event-card p {
    font-size: 0.95rem;
    line-height: 1.5;
    color: var(--secondary-color);
    margin-top: 0.75rem;
}

.event-category { 
    font-weight:600; 
    color:white; 
    background: var(--accent-purple); /* Use accent purple for category background */
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
    letter-spacing: 0.5px;
}

.event-meta { 
    margin-top:1.2rem; 
    font-size:0.85rem; 
    color:var(--secondary-color);
    display: flex;
    align-items: center;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color); /* Solid border for meta */
}
.event-meta strong { color: var(--text-color-light); }

.event-actions { 
    display:flex; 
    gap:12px; /* Increased gap */
    margin-top:1.5rem;
}
.event-actions button { 
    border:none; 
    border-radius:10px; 
    padding:0.7rem 1.1rem; 
    font-size:0.9rem; 
    cursor:pointer;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-qr { 
    background:#ffc300; /* Yellow for QR */
    color:var(--text-color-light);
}
.btn-edit { 
    background:rgba(0, 123, 255, 0.1); 
    border:1px solid var(--primary-color); 
    color:var(--primary-color);
}
.btn-delete { 
    background:rgba(220, 53, 69, 0.1); 
    border:1px solid var(--danger-color); 
    color:var(--danger-color);
}
.event-actions button:hover { 
    opacity: 1; 
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.add-btn { 
    background:var(--accent-purple); 
    color:white; 
    width:55px; /* Larger button */
    height:55px; 
    border-radius:50%; 
    font-size:2.2rem; 
    line-height: 55px;
    text-align: center;
    border:none; 
    cursor:pointer; 
    position:fixed; 
    bottom:30px; 
    right:30px;
    box-shadow: 0 8px 20px rgba(106, 0, 244, 0.4);
    transition: transform 0.2s, box-shadow 0.2s;
    z-index: 900;
}
.add-btn:hover { 
    transform: scale(1.1); 
    box-shadow: 0 10px 25px rgba(106, 0, 244, 0.6);
}


/* Modal Background */
.modal {
  position: fixed;
  inset: 0;
  display: none;
  background: rgba(0, 0, 0, 0.7); /* Darker backdrop */
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(8px); /* Stronger blur */
}

/* Modal Container */
.new-modal {
  background: var(--surface-light);
  width: 90%;
  max-width: 600px; /* Wider modal */
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 20px; /* Rounder corners */
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  animation: slideIn 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); /* Smoother animation */
}

@keyframes slideIn {
  from { transform: translateY(-30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal-header {
  padding: 1.5rem 2.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--primary-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 2.5rem;
  cursor: pointer;
  color: var(--secondary-color);
  transition: color 0.2s, transform 0.2s;
}

.close-btn:hover {
  color: var(--danger-color);
  transform: rotate(90deg);
}

.modal-body {
  padding: 2rem 2.5rem;
}

.input-group {
  margin-bottom: 1.5rem;
}

.input-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.6rem;
  font-size: 1rem;
  color: var(--text-color-light);
}

.input-group input,
.input-group select,
.input-group textarea {
  width: 100%;
  padding: 0.9rem 1.2rem;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  outline: none;
  background: var(--background-light);
  color: var(--text-color-light);
  font-size: 1rem;
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
}

textarea {
  resize: vertical;
  height: 120px;
}

.row {
  display: flex;
  gap: 15px; /* Increased gap */
}

.half {
  flex: 1;
}

.attendees-box {
  margin-top: 1rem;
}

.attendee-list {
  padding: 10px;
  border: 1px dashed var(--border-color);
  border-radius: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.attendee-list label {
  font-weight: 400;
  font-size: 0.9rem;
  margin-bottom: 0.4rem;
}

.add-attendee {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background 0.2s;
}

.modal-actions {
  padding: 1.5rem 2.5rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.btn {
  padding: 0.8rem 1.5rem;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: opacity 0.2s, transform 0.2s;
}

.btn.cancel {
  background: var(--danger-color);
  color: white;
}

.btn.primary {
  background: var(--accent-purple);
  color: white;
}

.btn:hover {
  opacity: 0.95;
  transform: translateY(-1px);
}

.save-btn:disabled {
  background: #a5a5a5;
  cursor: not-allowed;
  transform: none;
}

/* Dark Mode Overrides (Refinement) */
body.dark .new-modal {
  background: var(--surface-light);
  color: var(--text-color-light);
}
body.dark .input-group input,
body.dark .input-group select,
body.dark .input-group textarea {
  background: var(--background-light);
  border-color: var(--border-color);
}
body.dark .input-group input::placeholder,
body.dark .input-group textarea::placeholder {
  color: #777;
}


/* Responsive adjustments */
@media (max-width: 768px) {
    body { flex-direction: column; }
    .sidebar { width: 100%; height: auto; padding: 1rem; }
    .main { padding: 1.5rem; }
    .header { flex-direction: column; align-items: stretch; }
    .header div { margin-bottom: 0.5rem; }
    .events-container { grid-template-columns: 1fr; }
    .filters { justify-content: space-between; }
    .filters input, .filters select { min-width: 48%; }
    .row { flex-direction: column; gap: 0; }
    .input-group.half { margin-bottom: 1.5rem; }
    .modal-header, .modal-body, .modal-actions { padding: 1rem 1.5rem; }
}
</style>
</head>
<body>



<aside class="sidebar">
  <div>
    <div class="logo">Eventify</div>
    <div class="subtitle">Professional event planner</div>
    <nav class="nav">
              <a href="index.php"><span class="material-icons-round">dashboard</span> Dashboard</a>
      <a href="my-events.php" class="active"><span class="material-icons-round">event</span> My Events</a>
      <a href="calendar.php"><span class="material-icons-round">calendar_month</span> Calendar</a>
       <a href="users.php"><span class="material-icons-round">group</span> View all Users</a>
      <a href="notification.php"><span class="material-icons-round">notifications</span> Notifications</a>
      <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
      <a href="Budgeting/budget.php"><span class="material-icons-round">account_balance_wallet</span> Budget</a>
    </nav>
  </div>
  <div class="bottom-section">
    <div>Theme</div>
    <div class="theme-toggle">
      <label>Dark</label>
      <input type="checkbox" id="themeToggle">
    </div>
    <div style="margin-top:1rem; font-size:0.8rem;">v1.0 • Connected</div>
  </div>
</aside>

<main class="main">
  <div class="header">
    <div>
      <h1>My Events</h1>
      <p>Manage and track all your scheduled items.</p>
    </div>
    <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
  </div>

  <div class="filters">
    <input type="text" id="searchInput" placeholder="Search by name..." />
   <select id="categoryFilter">
      <option value="">All categories</option>
      <option value="Work">Work</option>
      <option value="Personal">Personal</option>
      <option value="School">School</option>
      <option value="Other">Other</option>
    </select>
    <select id="sortFilter">
      <option value="newest">Newest</option>
      <option value="oldest">Oldest</option>
      <option value="date">By Date</option>
    </select>
</div>

  <div class="events-container" id="eventsContainer">
    <p>Loading events...</p>
  </div>

  <button class="add-btn" id="addEventBtn">+</button>
</main>

<div class="modal" id="eventModal">
  <div class="modal-content new-modal">
    <div class="modal-header">
      <h2 class="modal-title">Add Event</h2>
      <button id="closeModal" class="close-btn">&times;</button>
    </div>

    <form id="eventForm" class="modal-body">

      <div class="input-group">
        <label>Event Title</label>
        <input type="text" id="eventTitle" placeholder="Enter event name">
      </div>

      <div class="input-group">
        <label>Category</label>
        <select id="eventCategory">
          <option value="">Select Category</option>
          <option>Seminar</option>
          <option>Workshop</option>
          <option>Meeting</option>
          <option>Training</option>
          <option>Orientation</option>
          <option>Webinar</option>
          <option>Sport</option>
          <option>Festival</option>
          <option>Celebration</option>
        </select>
      </div>

      <div class="input-group">
        <label>Description</label>
        <textarea id="eventDescription" placeholder="Write details..."></textarea>
      </div>

      <div class="row">
        <div class="input-group half">
          <label>Date</label>
          <input type="date" id="eventDate">
        </div>
        <div class="input-group half">
          <label>Time</label>
          <input type="time" id="eventTime">
        </div>
      </div>

      <div class="attendees-box">
        <label>Attendees</label>
        <div class="attendee-list" id="attendeeList">
          <button type="button" class="add-attendee" id="addAttendeeBtn">+ Add Attendee</button>
        </div>
      </div>

    </form>

    <div class="modal-actions">
      <button class="btn cancel" id="cancelEvent">Cancel</button>
      <button class="btn primary" id="saveEvent">Save Event</button>
    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const eventsContainer = document.getElementById("eventsContainer");
    const eventModal = document.getElementById("eventModal");
    const addEventBtn = document.getElementById("addEventBtn");
    const closeModal = document.getElementById("closeModal");
    const saveEventBtn = document.getElementById("saveEvent");

    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortFilter = document.getElementById("sortFilter");

    // Elements for form input
    const eventTitle = document.getElementById("eventTitle");
    const eventDate = document.getElementById("eventDate");
    const eventTime = document.getElementById("eventTime");


    let allEvents = []; // store loaded events for filtering

    // Dark mode toggle (Consistent theme application)
    const themeToggle = document.getElementById("themeToggle");
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

    // Modal open/close
    addEventBtn.addEventListener("click", () => openModal());
    closeModal.addEventListener("click", () => closeModalFn());
    window.addEventListener("click", (e) => { if (e.target === eventModal) closeModalFn(); });

    function openModal() {
        eventModal.style.display = "flex";
        attendeeList.innerHTML = '<button type="button" class="add-attendee" id="addAttendeeBtn">+ Add Attendee</button>';
        // Re-attach event listener for the button
        document.getElementById("addAttendeeBtn").addEventListener("click", () => {
            loadUsers();
        });
    }
    function closeModalFn() {
        eventModal.style.display = "none";
        saveEventBtn.dataset.editId = "";
        eventTitle.value = "";
        eventDate.value = "";
        eventTime.value = "";
        document.getElementById("eventCategory").value = "";
        document.getElementById("eventDescription").value = "";
        attendeeList.innerHTML = "";
        eventModal.querySelector("h2").textContent = "Add Event";
    }

    // Load users for attendees
    const attendeeList = document.getElementById("attendeeList");
    let allUsers = [];

    async function loadUsers() {
        try {
            const res = await fetch("get_users_for_attendees.php");
            const data = await res.json();
            if (data.success) {
                allUsers = data.users;
                renderUserCheckboxes();
            } else {
                attendeeList.innerHTML = `<p style='color: var(--danger-color);'>Failed to load users.</p>`;
            }
        } catch (err) {
            console.error("Error loading users:", err);
            attendeeList.innerHTML = `<p style='color: var(--danger-color);'>Error loading users.</p>`;
        }
    }

    function renderUserCheckboxes(selectedAttendees = []) {
        attendeeList.innerHTML = "";
        if (allUsers.length === 0) {
            attendeeList.innerHTML = "<p>No users available.</p>";
            return;
        }
        allUsers.forEach(user => {
            const isSelected = selectedAttendees.includes(user.name) || selectedAttendees.includes(user.email);
            const checkbox = document.createElement("div");
            checkbox.className = "attendee-item";
            checkbox.innerHTML = `
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" value="${user.id}" ${isSelected ? 'checked' : ''}>
                    <span>${user.name} (${user.email})</span>
                </label>
            `;
            attendeeList.appendChild(checkbox);
        });
    }

    function getAttendees() {
        const checkedBoxes = attendeeList.querySelectorAll("input[type='checkbox']:checked");
        return Array.from(checkedBoxes).map(cb => {
            const user = allUsers.find(u => u.id == cb.value);
            return user ? user.name : "";
        }).filter(name => name !== "");
    }

    // Load events
    async function loadEvents() {
        console.log("loadEvents called");
        try {
            console.log("Fetching get-events.php");
            const res = await fetch("get-events.php"); // Assuming get-events.php is implemented
            console.log("Fetch response:", res);
            const data = await res.json();
            console.log("Data received:", data);
            if (!data.success) { eventsContainer.innerHTML = `<p style='color: var(--danger-color);'>Error: ${data.message}</p>`; return; }
            allEvents = data.events; // save events for filtering
            applyFilters();
        } catch (err) {
            console.error("Error in loadEvents:", err);
            eventsContainer.innerHTML = `<p style="color: var(--danger-color);">Failed to load events: ${err.message}</p>`;
        }
    }

    // Render events (with edit/delete)
    function renderEvents(events) {
        eventsContainer.innerHTML = "";
        if (events.length === 0) { eventsContainer.innerHTML = "<p style='color: var(--secondary-color); text-align: center; width: 100%;'>No events found matching the criteria.</p>"; return; }

        events.forEach(ev => {
            const card = document.createElement("div");
            card.className = "event-card";
            card.dataset.id = ev.id;
            
            // Format attendees string
            const attendeesCount = ev.attendees?.length || 0;
            const attendeesSummary = attendeesCount > 0 ? 
                `<strong>Attendees:</strong> ${attendeesCount} people` : 
                `No attendees yet`;

            card.innerHTML = `
                <div class="event-category">${ev.category || 'N/A'}</div>
                <h3>${ev.title}</h3>
                <small style="color: var(--primary-color); font-weight: 500;">
                    <span class="material-icons-round" style="font-size: 16px; vertical-align: -3px;">calendar_today</span> 
                    ${ev.date} · ${ev.time}
                </small>
                <p>${ev.description ? ev.description.substring(0, 80) + (ev.description.length > 80 ? '...' : '') : "No description provided."}</p>
                <div class="event-meta">
                    <span class="material-icons-round" style="font-size: 16px;">group</span>
                    ${attendeesSummary}
                </div>
                <div class="event-actions">
                    <button class="btn-qr" title="Generate QR Code">QR</button>
                    <button class="btn-edit" title="Edit Event"><span class="material-icons-round" style="font-size: 16px; vertical-align: -3px;">edit</span> Edit</button>
                    <button class="btn-delete" title="Delete Event"><span class="material-icons-round" style="font-size: 16px; vertical-align: -3px;">delete</span> Delete</button>
                </div>
            `;
            eventsContainer.appendChild(card);

            // Delete
            card.querySelector(".btn-delete").addEventListener("click", async () => {
                if (!confirm(`Are you sure you want to delete the event: ${ev.title}?`)) return;
                try {
                    const res = await fetch("delete_event.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: ev.id }) });
                    const result = await res.json();
                    if (result.success) { alert("Event deleted!"); loadEvents(); }
                    else alert("Deletion Failed: " + result.message);
                } catch (err) { alert("Network error during deletion."); }
            });

            // Edit
            card.querySelector(".btn-edit").addEventListener("click", () => {
                openModal();
                eventTitle.value = ev.title;
                eventDate.value = ev.date;
                eventTime.value = ev.time;
                document.getElementById("eventCategory").value = ev.category || "";
                document.getElementById("eventDescription").value = ev.description;
                // Load users and pre-select attendees
                loadUsers().then(() => {
                    renderUserCheckboxes(ev.attendees || []);
                });

                saveEventBtn.dataset.editId = ev.id;
                eventModal.querySelector("h2").textContent = "Edit Event";
            });
        });
    }

    // Save Event (Add or Edit)
    saveEventBtn.addEventListener("click", async () => {
        const id = saveEventBtn.dataset.editId;
        const title = eventTitle.value.trim();
        const date = eventDate.value;
        const time = eventTime.value;
        const category = document.getElementById("eventCategory").value;
        const description = document.getElementById("eventDescription").value.trim();
        const attendees = getAttendees();

        if (!title || !date || !time || !category) return alert("Please fill in all required fields: Title, Date, Time, and Category.");

        saveEventBtn.disabled = true;
        saveEventBtn.textContent = id ? "Updating..." : "Saving...";

        try {
            const res = await fetch(id ? "edit_event.php" : "save_event.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id, title, date, time, category, description, attendees })
            });
            const result = await res.json();
            if (result.success) {
                alert(id ? "Event updated successfully!" : "Event saved successfully!");
                closeModalFn();
                loadEvents();
            }
            else alert("Error: " + (result.message || "Unknown server error."));
        } catch (err) {
            console.error(err);
            alert("Network error: Failed to save event.");
        } finally {
            saveEventBtn.disabled = false;
            saveEventBtn.textContent = id ? "Save Changes" : "Save Event";
        }
    });

    // Filter functionality
    function applyFilters() {
        let filtered = [...allEvents];

        // Search by title
        const searchTerm = searchInput.value.toLowerCase();
        if (searchTerm) filtered = filtered.filter(ev => ev.title.toLowerCase().includes(searchTerm));

        // Filter by category
        const category = categoryFilter.value;
        if (category) filtered = filtered.filter(ev => ev.category === category);

        // Sort
        const sort = sortFilter.value;
        if (sort === "newest") filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        if (sort === "oldest") filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        if (sort === "date") filtered.sort((a, b) => new Date(a.date) - new Date(b.date));

        renderEvents(filtered);
    }

    // Attach filter listeners
    searchInput.addEventListener("input", applyFilters);
    categoryFilter.addEventListener("change", applyFilters);
    sortFilter.addEventListener("change", applyFilters);

    // Initial load
    loadEvents();
});
</script>

</body>
</html>