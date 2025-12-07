<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: process/index.html");
    exit;
}

include 'db_config.php';

// Update last_active for this admin
$stmt = $conn->prepare("UPDATE users SET last_active=NOW() WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* KPI Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--surface-light);
            border-radius: 16px; /* Slightly more rounded */
            padding: 1.5rem;
            box-shadow: var(--shadow-default);
            border: 1px solid var(--header-bg-light); /* Subtle border */
        }

        .card h3 {
            font-size: 1rem;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card p {
            font-size: 2.5rem; /* Larger number */
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .card small {
            color: var(--secondary-color);
            display: block;
            margin-top: 5px;
        }

        /* Chart and Upcoming Section */
        .dashboard-content {
            display: grid;
            grid-template-columns: 3fr 2fr; /* Adjusted column ratio */
            gap: 1.5rem;
        }

        .chart-card {
            background: var(--surface-light);
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow-default);
            min-height: 400px;
        }
        .chart-card h3 { 
            margin-bottom: 1.5rem; 
            font-weight: 600;
            color: var(--text-color-light);
        }

        /* Upcoming Events Card */
        .upcoming-card {
            background: var(--surface-light);
            /* Removed fixed background color and centered content */
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow-default);
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .upcoming-card h3 { 
            margin-bottom: 1rem; 
            font-weight: 600;
            color: var(--text-color-light);
        }

        .upcoming-events-container {
            flex-grow: 1; /* Allows container to fill space */
            display: flex;
            flex-direction: column;
            gap: 15px; /* Increased gap */
            max-height: 450px;
            overflow-y: auto;
            padding-right: 5px; /* Space for scrollbar */
        }
        
        /* Custom scrollbar for dark mode */
        body.dark .upcoming-events-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }
        body.dark .upcoming-events-container::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .event-card {
            background: rgba(0, 123, 255, 0.1); /* Light background using primary color */
            padding: 12px;
            border-radius: 12px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        body.dark .event-card {
            background: rgba(91, 110, 248, 0.1);
            border-left-color: var(--primary-color);
        }
        .event-card:hover {
            transform: translateY(-2px);
        }

        .event-card h4 {
            font-size: 1.1rem;
            margin: 5px 0 3px 0;
            color: var(--text-color-light);
        }
        
        .event-card small {
            font-size: 0.8rem;
            color: var(--secondary-color);
            display: block;
            margin-bottom: 5px;
        }
        
        .event-card p {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--secondary-color);
        }

        .event-category {
            display: inline-block;
            background: var(--accent-purple);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 15px;
            margin-bottom: 5px;
        }
        
        .add-btn {
            background: var(--accent-purple);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 2rem;
            line-height: 50px;
            text-align: center;
            border: none;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 20px;
            box-shadow: 0 4px 15px rgba(106, 0, 244, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .add-btn:hover {
            transform: scale(1.05);
        }

        /* --- Modal Styles (Improved) --- */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 200;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: var(--surface-light);
            width: 90%;
            max-width: 550px; /* Slightly wider modal */
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        body.dark .modal-content { background: var(--surface-light); color: var(--text-color-light); }
        body.dark .modal-header { border-bottom: 1px solid #444; }
        body.dark .fixed-actions { background: var(--surface-light); border-bottom: 1px solid #444; }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--header-bg-light);
        }
        .modal-header h2 { color: var(--primary-color); font-size: 1.5rem; }

        .close-btn {
            font-size: 2rem;
            cursor: pointer;
            color: var(--secondary-color);
            transition: color 0.2s;
        }
        .close-btn:hover { color: var(--danger-color); }

        .fixed-actions {
            position: sticky;
            top: 0;
            background: var(--surface-light);
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--header-bg-light);
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .generate-btn {
            background: var(--header-bg-light);
            border: none;
            color: var(--text-color-light);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        .generate-btn:hover { background: #d0d3d6; }

        .save-btn {
            background: var(--accent-purple);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        .save-btn:hover { background: #5500c8; }
        .save-btn:disabled { background: #a5a5a5; cursor: not-allowed; }

        .modal-body {
            overflow-y: auto;
            padding: 1.5rem 2rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            background: var(--background-light);
            color: var(--text-color-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .form-group textarea {
            resize: vertical;
        }
        
        body.dark .form-group input, body.dark .form-group textarea {
            border: 1px solid #555;
            background: #1e1e2e;
        }
        /* Attendees */
        .add-attendee {
            background: var(--accent-purple);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .add-attendee:hover { opacity: 0.9; }

        .attendee-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .attendee-list {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .attendee-item input {
            flex: 1;
            padding: 0.6rem;
        }

        .remove-attendee {
            background: none;
            border: none;
            color: var(--danger-color);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .remove-attendee:hover { color: #8e1c2a; }

        .Nav {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
}

.Nav .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--card-bg);
    padding: 14px 22px;
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    transition: 0.25s ease;
    margin-bottom: 20px;
}

.Nav .nav-item:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.Nav .nav-item span.material-icons-round {
    font-size: 20px;
}


        /* Responsiveness */
        @media (max-width: 900px) {
            .dashboard-content {
                grid-template-columns: 1fr; /* Stack charts vertically */
            }
        }
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; height: auto; padding: 1rem; }
            .main { padding: 1.5rem; }
            .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .logout-btn { align-self: flex-end; }
            .cards { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
            .upcoming-events-container { max-height: 300px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div>
            <div class="logo">Eventify</div>
            <div class="subtitle">Professional event planner</div>
            <nav class="nav">
                <a href="#" class="active"><span class="material-icons-round">dashboard</span> Dashboard</a>
                <a href="calendar.php"><span class="material-icons-round">calendar_month</span> Calendar</a>
                <a href="users.php"><span class="material-icons-round">group</span> View all Users</a>
                <a href="#"><span class="material-icons-round">notifications</span> Notifications</a>
                <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
            </nav>
        </div>
        <div class="bottom-section">
            <div>Theme</div>
            <div class="theme-toggle">
                <label for="themeToggle">Dark</label>
                <input type="checkbox" id="themeToggle">
            </div>
            <div style="margin-top:1rem; font-size:0.8rem;">v1.0 • Local only</div>
        </div>
    </aside>

    <main class="main">
        <div class="header">
            <div>
                <h1>Dashboard</h1>
                <p>Overview & insights for your events</p>
            </div>
            <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
        </div>
      <div class="Nav">
            <a href="chat_users.php" class="nav-item">
                <span class="material-icons-round">chat</span>
                <span>Chat with Users</span>
            </a>

            <a href="my-events.php" class="nav-item" style="grid-column: 3;">
                <span class="material-icons-round">event</span>
                <span>My Events</span>
            </a>
        </div>

        <div class="cards">
            <div class="card">
                <h3><span class="material-icons-round" style="color: var(--primary-color);">upcoming_event</span> Upcoming</h3>
                <p id="upcomingEvents">0</p>
                <small>Events in future</small>
            </div>
            <div class="card">
                <h3><span class="material-icons-round" style="color: var(--success-color);">today</span> Due Today</h3>
                <p id="dueToday">0</p>
                <small>Happening today</small>
            </div>
            <div class="card">
                <h3><span class="material-icons-round" style="color: var(--info-color);">list_alt</span> Total Events</h3>
                <p id="totalEvents">0</p>
                <small>All saved events</small>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="chart-card">
                <h3>Events by Category</h3>
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="upcoming-card">
                <h3>Upcoming Soon</h3>
                <div class="upcoming-events-container">
                    <p>Loading events...</p>
                </div>
                <button class="add-btn" id="openModal">+</button>
            </div>
        </div>
    </main>

    <div class="modal" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Event</h2>
                </div>

            <div class="fixed-actions">
                <button class="generate-btn">Generate QR</button>
                <button class="save-btn" id="saveEvent">Save Event</button>
                <span id="closeModal" class="close-btn" title="Close Modal">&times;</span>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="eventTitle">Event Title</label>
                    <input type="text" id="eventTitle" placeholder="Enter event title" />
                </div>

                <div class="form-group">
                    <label for="eventDate">Date</label>
                    <input type="date" id="eventDate" />
                </div>

                <div class="form-group">
                    <label for="eventTime">Time</label>
                    <input type="time" id="eventTime" />
                </div>

                <div class="form-group">
                    <label for="eventCategory">Category</label>
                    <input type="text" id="eventCategory" placeholder="e.g., Work, Personal, School" />
                </div>

                <div class="form-group">
                    <label for="eventDesc">Description</label>
                    <textarea id="eventDesc" rows="3"></textarea>
                </div>

                <hr style="border-color: var(--header-bg-light); margin: 1.5rem 0;" />

                <div class="form-group">
                    <label>Attendees</label>
                    <button type="button" class="add-attendee" id="addAttendee">+ Add Attendee</button>
                    <div id="attendeeList" class="attendee-list"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", async () => {
        const ctx = document.getElementById("categoryChart");
        const totalCount = document.getElementById("totalEvents");
        const upcomingCount = document.getElementById("upcomingEvents");
        const dueTodayCount = document.getElementById("dueToday");
        const upcomingCard = document.querySelector(".upcoming-card");
        const modal = document.getElementById("eventModal");
        const closeModal = document.getElementById("closeModal");
        const openModalBtn = document.getElementById("openModal"); // Renamed for clarity
        const addAttendeeBtn = document.getElementById("addAttendee");
        const attendeeList = document.getElementById("attendeeList");
        let chartInstance = null; // Variable to hold Chart.js instance


        // ❌ Removed redundant upcomingContainer creation here, using the existing element in HTML
        const upcomingContainer = document.querySelector(".upcoming-events-container");

        // ✅ Open & Close Modal
        openModalBtn.addEventListener("click", () => {
            modal.style.display = "flex"; // use flex for centering
        });

        closeModal.addEventListener("click", () => {
            modal.style.display = "none";
        });

        window.addEventListener("click", (event) => {
            if (event.target === modal) modal.style.display = "none";
        });

        // ✅ ADD ATTENDEE FUNCTIONALITY
        if (addAttendeeBtn) {
            addAttendeeBtn.addEventListener("click", () => {
            const row = document.createElement("div");
            row.className = "attendee-item";
            
            // Simplified and cleaned up attendee row HTML, relying on CSS for styles
            row.innerHTML = `
                <input type="email" class="attendee-input" placeholder="Attendee email (e.g., john@example.com)">
                <button type="button" class="remove-attendee" title="Remove attendee">&times;</button>
            `;
            attendeeList.appendChild(row);

            // remove this attendee
            row.querySelector(".remove-attendee").addEventListener("click", () => row.remove());
            });
            
            // Add initial empty attendee input on modal open (optional: remove if you prefer starting empty)
            // addAttendeeBtn.click();
        }

        // Save Event button handler
        const saveEventBtn = document.getElementById("saveEvent");
        saveEventBtn.addEventListener("click", async () => {
            // Collect form data
            const title = document.getElementById("eventTitle").value.trim();
            const date = document.getElementById("eventDate").value.trim();
            const time = document.getElementById("eventTime").value.trim();
            const category = document.getElementById("eventCategory").value.trim();
            const description = document.getElementById("eventDesc").value.trim();
            const attendeeInputs = attendeeList.querySelectorAll(".attendee-input");
            const attendees = Array.from(attendeeInputs).map(input => input.value.trim()).filter(name => name !== "");

            // Basic validation
            if (!title || !date || !time || !category) {
            alert("Please fill in all required fields: Title, Date, Time, and Category.");
            return;
            }

            // Disable button to prevent duplicate clicks
            saveEventBtn.disabled = true;
            saveEventBtn.textContent = "Saving...";

            try {
            // NOTE: This assumes 'save_event.php' is implemented correctly
            const response = await fetch("save_event.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ title, date, time, category, description, attendees })
            });
            const result = await response.json();

            if (result.success) {
                alert("Event saved successfully!");
                modal.style.display = "none";
                // Clear form fields
                document.getElementById("eventTitle").value = "";
                document.getElementById("eventDate").value = "";
                document.getElementById("eventTime").value = "";
                document.getElementById("eventCategory").value = "";
                document.getElementById("eventDesc").value = "";
                attendeeList.innerHTML = "";

                // Reload dashboard data to reflect updates
                loadDashboardData();
            } else {
                alert("Error saving event: " + (result.message || "Unknown error"));
            }
            } catch (error) {
            alert("Network error: " + error.message);
            } finally {
            saveEventBtn.disabled = false;
            saveEventBtn.textContent = "Save Event";
            }
        });

        // ✅ Load Dashboard Data (Chart + Stats + Upcoming Events)
        async function loadDashboardData() {
            try {
            const response = await fetch("get_dashboard_stats.php");
            const data = await response.json();

            if (!data.success) {
                upcomingContainer.innerHTML = "<p>⚠️ Failed to load dashboard stats.</p>";
                return;
            }

            // Update counters
            totalCount.textContent = data.total_events;
            upcomingCount.textContent = data.upcoming;
            dueTodayCount.textContent = data.due_today;

            // Chart
            const labels = Object.keys(data.categories);
            const values = Object.values(data.categories);
            
            // Destroy previous chart instance before creating a new one
            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: "doughnut",
                data: {
                labels: labels,
                datasets: [{
                    label: "Events by Category",
                    data: values,
                    backgroundColor: ["#00d26a", "#ff4d6d", "#f9c74f", "#4361ee", "#4cc9f0"],
                    borderWidth: 1,
                    hoverOffset: 4
                }]
                },
                options: { 
                    plugins: { 
                        legend: { 
                            position: "bottom",
                            labels: {
                                color: document.body.classList.contains('dark') ? '#f5f5f5' : '#1a1a1a'
                            }
                        } 
                    } 
                }
            });

            // Show Upcoming Events
            if (data.upcoming_events && data.upcoming_events.length > 0) {
                upcomingContainer.innerHTML = data.upcoming_events.map(ev => `
                <div class="event-card">
                    <div class="event-category">${ev.category}</div>
                    <h4>${ev.title}</h4>
                    <small><span class="material-icons-round" style="font-size: 14px; vertical-align: -2px;">calendar_today</span> ${ev.date} · ${ev.time}</small>
                    <p>${ev.description ? ev.description.substring(0, 50) + (ev.description.length > 50 ? '...' : '') : "No description."}</p>
                </div>
                `).join("");
            } else {
                upcomingContainer.innerHTML = "<p style='text-align: center; margin-top: 30px; color: var(--secondary-color);'>No upcoming events planned.</p>";
            }
            } catch (error) {
            console.error("Dashboard load error:", error);
            upcomingContainer.innerHTML = `<p style='color: var(--danger-color);'>Error loading events: ${error.message}</p>`;
            }
        }

        // ✅ Dark mode toggle
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
            // Reload dashboard data or update chart theme
            loadDashboardData(); 
        });

        // ✅ Initial load
        loadDashboardData();
        
        // This function is still here from your original code, but appears unused in the final dashboard display.
        // Keeping it for completeness if a dedicated 'users online' widget is later added.
        async function loadUsers() {
             /* ... your existing loadUsers logic ... */
        }

        // loadUsers();
        // setInterval(loadUsers, 60000);
        
        });
    </script>

</body>
</html>