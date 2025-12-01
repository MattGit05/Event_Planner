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
    <title>Eventify | Calendar</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <style>
        /* --- General Variables --- */
        :root {
            --primary-color: #007bff; /* Eventify Primary Blue */
            --primary-dark: #0056b3;
            --background-light: #f6f8fc;
            --surface-light: #ffffff;
            --text-color-light: #1a1a1a;
            --secondary-color: #6c757d;
            --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.08);
            --event-bg: #4cc9f0; /* Light Blue for Events */
            --event-border: #4361ee; /* Darker Blue for Event Borders */
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        /* Dark Theme Variables */
        body.dark {
            --primary-color: #5b6ef8;
            --primary-dark: #4a59d0;
            --background-light: #1e1e2e;
            --surface-light: #2b2b3d;
            --text-color-light: #f5f5f5;
            --secondary-color: #adb5bd;
            --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        /* --- Global Styles --- */
        * { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
        body {
            display:flex;
            min-height:100vh;
            background-color:var(--background-light);
            color:var(--text-color-light);
            transition: background 0.3s, color 0.3s;
        }

        /* --- Sidebar Styles --- */
        .sidebar {
            width:250px;
            background:var(--surface-light);
            padding:2rem 1.5rem;
            box-shadow:var(--shadow-default);
            display:flex;
            flex-direction:column;
            border-right: 1px solid rgba(0,0,0,0.05);
        }
        .logo { font-size:1.8rem; font-weight:700; color:var(--primary-color); margin-bottom:0.5rem; text-align: left; }
        .subtitle { font-size:0.8rem; color:var(--secondary-color); margin-bottom:2rem; }

        .nav { display:flex; flex-direction:column; gap:0.5rem; }
        .nav a {
            text-decoration:none;
            color:var(--text-color-light);
            font-weight:500;
            display:flex;
            align-items:center;
            gap:1rem;
            padding:0.75rem 1rem;
            border-radius:10px;
            transition:background 0.2s, color 0.2s;
            background:none;
            border:none;
            cursor:pointer;
        }
        .nav a:hover {
            background:rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
        }
        .nav a.active {
            background: var(--primary-color);
            color: white !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        /* --- Main Content --- */
        .main {
            flex: 1;
            padding: 2.5rem;
        }
        .header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom: 2rem;
        }
        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0;
        }

        /* Add Event Button - Consistent with Admin Dashboard */
        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .add-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
        }
        .add-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        .add-btn .material-icons-round {
            font-size: 1.4rem;
        }

        #calendar {
            background: var(--surface-light);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-default);
            min-height: 700px;
        }

        /* --- FullCalendar Overrides --- */
        .fc .fc-button-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 8px 15px;
            text-transform: capitalize;
            font-weight: 500;
            transition: 0.2s;
        }
        .fc .fc-button-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .fc-toolbar-title {
            font-weight: 600;
            font-size: 1.5rem;
            color: var(--text-color-light);
        }
        .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .fc-event {
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 4px;
        }

        /* Dark Mode Overrides for Calendar */
        body.dark .sidebar, body.dark #calendar {
            background: var(--surface-light);
            color: var(--text-color-light);
            border-color: #444;
        }
        body.dark .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(91, 110, 248, 0.15);
        }
        body.dark .fc-scrollgrid, body.dark .fc-view-harness, body.dark .fc-col-header-cell {
             border-color: #444 !important;
        }
        body.dark .fc-daygrid-body-unbalanced .fc-daygrid-day > .fc-daygrid-day-frame,
        body.dark .fc-day-other .fc-daygrid-day-number {
             border-top: 1px solid #444;
             opacity: 0.8;
        }

        /* --- Modal Styles (Copied from users.php) --- */
        .modal-bg {
            position:fixed;
            top:0; left:0;
            width:100%; height:100%;
            background:rgba(0,0,0,0.6);
            display:none;
            justify-content:center;
            align-items:center;
            z-index:1000;
            backdrop-filter: blur(4px);
        }

        .modal-box {
            background:var(--surface-light);
            padding:30px;
            width:100%;
            max-width:500px;
            border-radius:16px;
            box-shadow:var(--shadow-default);
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(-20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-box h2 {
            margin-bottom:20px;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
            padding-bottom: 10px;
        }
        .modal-box label { font-weight:600; margin-top:15px; display:block; font-size: 0.9rem; }
        .modal-box input, .modal-box select, .modal-box textarea {
            width:100%;
            padding:12px;
            margin-top:5px;
            border:1px solid rgba(0, 0, 0, 0.1);
            border-radius:8px;
            background: var(--background-light);
            color: var(--text-color-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .modal-box textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal-box input:focus, .modal-box select:focus, .modal-box textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .modal-actions {
            margin-top:25px;
            display:flex;
            justify-content:flex-end;
            gap:10px;
        }

        .btn-cancel, .btn-save {
            padding:10px 18px;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-weight:600;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-cancel {
            background:var(--secondary-color);
            color:#fff;
        }
        .btn-cancel:hover { background:#5a6268; }

        .btn-save {
            background:var(--primary-color);
            color:#fff;
        }
        .btn-save:hover { background:var(--primary-dark); }

        /* Dark Mode Overrides for Modal */
        body.dark .modal-box { background:var(--surface-light); color:var(--text-color-light); }
        body.dark .modal-box input,
        body.dark .modal-box select,
        body.dark .modal-box textarea {
            background:var(--background-light);
            color:var(--text-color-light);
            border:1px solid #444;
        }
        body.dark .modal-box input:focus, body.dark .modal-box select:focus, body.dark .modal-box textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(91, 110, 248, 0.3);
        }

        /* View Event Modal Specific Styles */
        #viewEventModal h2 { color: var(--event-border); border-bottom-color: rgba(67, 97, 238, 0.2); }
        .detail-item { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed rgba(0,0,0,0.1); }
        .detail-item strong { display: inline-block; width: 80px; color: var(--primary-color); }
        body.dark .detail-item { border-bottom: 1px dashed rgba(255,255,255,0.1); }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">Eventify</div>
        <div class="subtitle">Professional event planner</div>
        <nav class="nav">
            <a href="index.php"><span class="material-icons-round">dashboard</span> Dashboard</a>
            <a href="my-events.php"><span class="material-icons-round">event</span> My Events</a>
            <a href="calendar.php" class="active"><span class="material-icons-round">calendar_month</span> Calendar</a>
            <a href="users.php"><span class="material-icons-round">group</span> Users</a>
            <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
        </nav>
        <!-- Dark Mode Toggle (For sidebar consistency) -->
        <div class="bottom-section" style="margin-top: auto;">
            <div style="font-size:0.9rem; color:var(--secondary-color);">Theme</div>
            <div class="theme-toggle" style="display:flex; justify-content:space-between; align-items:center; margin-top:5px;">
                <label for="themeToggle" style="font-size: 0.9rem;">Dark Mode</label>
                <input type="checkbox" id="themeToggle">
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>📅 Event Calendar</h1>
            <button class="add-btn" onclick="openAddEventModal()">
                <span class="material-icons-round">add</span> Add Event
            </button>
        </div>
        <div id="calendar"></div>
    </div>

    <!-- 1. Add New Event Modal -->
    <div class="modal-bg" id="addEventModal">
        <div class="modal-box">
            <h2>Schedule New Event</h2>

            <label for="event-title">Event Title</label>
            <input type="text" id="event-title" name="title" placeholder="e.g., Annual Sales Kickoff" required>

            <label for="event-date">Date</label>
            <input type="date" id="event-date" name="date" required>

            <label for="event-time">Time</label>
            <input type="time" id="event-time" name="time" required>

            <label for="event-category">Category</label>
            <select id="event-category" name="category" required>
                <option value="">Select category</option>
                <option value="Work">Work</option>
                <option value="Personal">Personal</option>
                <option value="School">School</option>
                <option value="Other">Other</option>
            </select>

            <label for="event-description">Description</label>
            <textarea id="event-description" name="description" placeholder="Optional description"></textarea>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeAddEventModal()">Cancel</button>
                <button type="submit" class="btn-save" onclick="handleNewEventSubmit()">Create Event</button>
            </div>
        </div>
    </div>

    <!-- 2. View Event Modal (Replaces alert) -->
    <div class="modal-bg" id="viewEventModal">
        <div class="modal-box">
            <h2>Event Details</h2>

            <div class="detail-item"><strong>Title:</strong> <span id="view-title"></span></div>
            <div class="detail-item"><strong>Start:</strong> <span id="view-start"></span></div>
            <div class="detail-item"><strong>End:</strong> <span id="view-end">N/A</span></div>
            <div class="detail-item"><strong>Category:</strong> <span id="view-category"></span></div>
            <div class="detail-item"><strong>Description:</strong> <span id="view-description"></span></div>
            <div class="detail-item"><strong>ID:</strong> <span id="view-id"></span></div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeViewEventModal()">Close</button>
                <button type="button" class="btn-save" style="background: var(--danger-color);">Delete</button>
            </div>
        </div>
    </div>


    <script>
        let calendar; // Global calendar variable

        document.addEventListener("DOMContentLoaded", function () {
            // --- Theme Toggle Setup ---
            const toggle = document.getElementById("themeToggle");
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

            // --- FullCalendar Initialization ---
            const calendarEl = document.getElementById("calendar");
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                height: "auto",
                selectable: true,
                dayMaxEvents: true, // Allow "more" link
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay",
                },

                // Event Loading (Refreshed by calendar.refetchEvents())
                events: async (info, successCallback, failureCallback) => {
                    try {
                        // Fetch from get-events.php
                        const response = await fetch("get-events.php");
                        const data = await response.json();

                        if (!data.success) {
                            console.error("Failed to load events:", data.message);
                            return failureCallback(data.message || "Failed to load events.");
                        }

                        // Map database structure to FullCalendar event object
                        const formatted = data.events.map((e) => ({
                            id: e.id,
                            title: e.title,
                            start: e.date + 'T' + e.time, // Combine date and time
                            allDay: false, // Assuming not all-day
                            backgroundColor: "#4cc9f0",
                            borderColor: "#4361ee",
                            extendedProps: {
                                category: e.category,
                                description: e.description || 'No description provided.'
                            }
                        }));
                        successCallback(formatted);
                    } catch (err) {
                        console.error('Network or Parse Error:', err);
                        failureCallback(err);
                    }
                },

                // Handle event click using the custom modal
                eventClick: function (info) {
                    openViewEventModal(info.event);
                },

                // Handle date/day click for quick scheduling
                dateClick: function(info) {
                    const dateInput = document.getElementById('event-date');
                    const timeInput = document.getElementById('event-time');
                    dateInput.value = info.dateStr;
                    timeInput.value = '09:00'; // Default time
                    openAddEventModal();
                }
            });

            calendar.render();
        });

        // --- Modal Functions ---

        function openAddEventModal() {
            document.getElementById("addEventModal").style.display = "flex";
        }

        function closeAddEventModal() {
            document.getElementById("addEventModal").style.display = "none";
            // Clear form
            document.getElementById("event-title").value = "";
            document.getElementById("event-date").value = "";
            document.getElementById("event-time").value = "";
            document.getElementById("event-category").value = "";
            document.getElementById("event-description").value = "";
        }

        function openViewEventModal(event) {
            document.getElementById("view-title").textContent = event.title;
            document.getElementById("view-start").textContent = event.start ? event.start.toLocaleString() : 'N/A';
            document.getElementById("view-end").textContent = event.end ? event.end.toLocaleString() : 'N/A';
            document.getElementById("view-category").textContent = event.extendedProps.category || 'N/A';
            document.getElementById("view-description").textContent = event.extendedProps.description || 'N/A';
            document.getElementById("view-id").textContent = event.id || 'N/A';

            document.getElementById("viewEventModal").style.display = "flex";
        }

        function closeViewEventModal() {
            document.getElementById("viewEventModal").style.display = "none";
        }

        // Close modal when clicking outside
        window.addEventListener("click", function(e) {
            const addModal = document.getElementById("addEventModal");
            const viewModal = document.getElementById("viewEventModal");

            if (e.target === addModal) closeAddEventModal();
            if (e.target === viewModal) closeViewEventModal();
        });


        // --- Event Creation Logic ---

        async function handleNewEventSubmit() {
            const title = document.getElementById('event-title').value.trim();
            const date = document.getElementById('event-date').value;
            const time = document.getElementById('event-time').value;
            const category = document.getElementById('event-category').value;
            const description = document.getElementById('event-description').value.trim();

            if (!title || !date || !time || !category) {
                alert("Please fill in all required fields.");
                return;
            }

            const submitButton = document.querySelector('#addEventModal .btn-save');
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';

            try {
                const response = await fetch('save_event.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title,
                        date,
                        time,
                        category,
                        description,
                        attendees: [] // Empty for now
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeAddEventModal();
                    calendar.refetchEvents();
                    console.log("Event created successfully:", data.message);
                } else {
                    console.error("Event creation failed:", data.message);
                    alert(`Error creating event: ${data.message}`);
                }
            } catch (err) {
                console.error('Network error during event creation:', err);
                alert('An unexpected network error occurred.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Create Event';
            }
        }
    </script>
</body>
</html>
