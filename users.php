<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: process/index.html");
    exit;
}

include 'db_config.php';

// Update admin's last_active
$stmt = $conn->prepare("UPDATE users SET last_active=NOW() WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<style>
    :root {
        --primary-color: #007bff;
        --secondary-color: #6c757d;
        --background-light: #f6f8fc;
        --surface-light: #ffffff;
        --text-color-light: #1a1a1a;
        --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.08);
        --success-color: #28a745;
        --danger-color: #dc3545;
        --online-color: #198754;
        --offline-color: #dc3545;
        --header-bg-light: #e9ecef; /* Light gray for header */
        --info-color: #17a2b8; /* For Edit button */
    }

    body.dark {
        --primary-color: #5b6ef8;
        --secondary-color: #adb5bd;
        --background-light: #1e1e2e;
        --surface-light: #2b2b3d;
        --text-color-light: #f5f5f5;
        --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.4);
        --header-bg-light: #3a3a50;
        --info-color: #4a69bd;
    }

    * { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
    body { display:flex; min-height:100vh; background-color:var(--background-light); color:var(--text-color-light); transition: background 0.3s, color 0.3s; }

    /* Sidebar */
    .sidebar { 
        width:250px; 
        background:var(--surface-light); 
        padding:2rem 1.5rem; /* Increased padding */
        box-shadow:var(--shadow-default); 
        display:flex; 
        flex-direction:column; 
        justify-content:space-between;
        z-index: 10;
        border-right: 1px solid rgba(0,0,0,0.05); /* Subtle border */
    }
    .logo { font-size:1.8rem; font-weight:700; color:var(--primary-color); margin-bottom:0.5rem; }
    .subtitle { font-size:0.8rem; color:var(--secondary-color); margin-bottom:2rem; }
    .nav { display:flex; flex-direction:column; gap:0.5rem; }
    .nav a, .nav button { 
        text-decoration:none; 
        color:var(--text-color-light); 
        font-weight:500; 
        display:flex; 
        align-items:center; 
        gap:1rem; 
        padding:0.75rem 1rem; /* Increased padding */
        border-radius:10px; /* More rounded corners */
        transition:background 0.2s, color 0.2s; 
        background:none; 
        border:none; 
        cursor:pointer; 
    }
    .nav a:hover, .nav button:hover { 
        background:rgba(0, 123, 255, 0.1); /* Lighter hover */
        color: var(--primary-color);
    }
    /* Active Link Style - Important for UX */
    .nav a.active {
        background: var(--primary-color);
        color: white !important;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    }
    .bottom-section { margin-top:3rem; font-size:0.9rem; color:var(--secondary-color); }
    .theme-toggle { display:flex; align-items:center; justify-content: space-between; margin-top:1rem; padding: 0.5rem 0; }
    .theme-toggle label { font-size: 0.9rem; }
    
    /* Main Section */
    .main { flex:1; padding:2rem 3rem; }
    .header { 
        display:flex; 
        justify-content:space-between; 
        align-items:center; 
        padding-bottom: 1.5rem; 
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--header-bg-light); /* Clean separator */
    }
    .header h1 { font-size:2rem; color:var(--text-color-light); font-weight:600; }
    
    /* Action Buttons Group */
    .action-group { display: flex; gap: 15px; align-items: center; }

    /* Logout Button */
    .logout-btn { 
        background:var(--danger-color); 
        color:#fff; 
        border:none; 
        padding:0.6rem 1.2rem; 
        border-radius:8px; 
        cursor:pointer; 
        font-weight:600; 
        transition: background 0.2s, transform 0.2s;
    }
    .logout-btn:hover { background:#c82333; transform: translateY(-1px); }

    /* Users Table Container */
    .container { 
        width: 100%;
        margin-top:2rem; 
        padding:0; 
        background:var(--surface-light); 
        border-radius:12px; 
        box-shadow:var(--shadow-default); 
        overflow-x: auto; 
    }
    table { width:100%; border-collapse:collapse; }
    th, td { 
        padding:1.2rem 1.5rem; 
        text-align:left; 
        border-bottom:1px solid rgba(0,0,0,0.05); 
        transition: background 0.3s;
    }
    body.dark td { border-bottom:1px solid rgba(255,255,255,0.1); }
    
    th { 
        background:var(--header-bg-light); 
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        color: var(--secondary-color);
    }
    
    tbody tr:hover {
        background: rgba(0, 123, 255, 0.05); 
    }

    body.dark tbody tr:hover {
        background: rgba(91, 110, 248, 0.1);
    }
    
    .online { color:var(--online-color); font-weight:600; }
    .offline { color:var(--offline-color); font-weight:600; }
    
    /* Action Button Group in Table Row */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    /* Table Action Buttons */
    .action-btn {
        background: none;
        border: 1px solid var(--secondary-color);
        color: var(--secondary-color);
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }

    .edit-btn {
        border-color: var(--info-color);
        color: var(--info-color);
    }
    .edit-btn:hover {
        background: var(--info-color);
        color: white;
    }

    .delete-btn {
        border-color: var(--danger-color);
        color: var(--danger-color);
    }
    .delete-btn:hover {
        background: var(--danger-color);
        color: white;
    }

    /* Add User Button - Modern Look */
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
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
    }
    .add-btn:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    .add-btn::before {
        content: "+";
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* Modal Styling (Reused for all modals) */
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
        max-width:450px; 
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
    /* Delete Modal specific style */
    #deleteUserModal .modal-box h2 {
        border-bottom-color: rgba(220, 53, 69, 0.2);
    }


    .modal-box label { font-weight:600; margin-top:15px; display:block; font-size: 0.9rem; }
    .modal-box input, .modal-box select {
        width:100%;
        padding:12px;
        margin-top:5px;
        border:1px solid var(--header-bg-light); 
        border-radius:8px;
        background: var(--background-light);
        color: var(--text-color-light);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .modal-box input:focus, .modal-box select:focus {
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
        background:var(--success-color);
        color:#fff;
    }
    .btn-save:hover { background:#1e7e34; }

    /* Dark Mode Overrides for Modal */
    body.dark .modal-box { background:var(--surface-light); color:var(--text-color-light); }
    body.dark .modal-box input,
    body.dark .modal-box select { 
        background:var(--background-light); 
        color:var(--text-color-light); 
        border:1px solid #444; 
    }
    body.dark .modal-box input:focus, body.dark .modal-box select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(91, 110, 248, 0.3);
    }
    body.dark .nav a:hover, body.dark .nav button:hover { 
        background:rgba(91, 110, 248, 0.1);
    }
    
    /* Responsiveness */
    @media (max-width: 768px) {
        body { flex-direction: column; }
        .sidebar { width: 100%; height: auto; padding: 1rem; border-right: none; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .main { padding: 1rem; }
        .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .action-group { width: 100%; justify-content: space-between; }
        .action-buttons { flex-direction: column; gap: 0.2rem; }
        .action-btn { width: 100%; }
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
                <a href="my-events.php"><span class="material-icons-round">event</span> My Events</a>
                <a href="calendar.php"><span class="material-icons-round">calendar_month</span> Calendar</a>
                <a href="notification.php"><span class="material-icons-round">notifications</span> Notifications</a>
                <a href="users.php" class="active"><span class="material-icons-round">group</span> Users</a>
                <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
            </nav>
        </div>
        <div class="bottom-section">
            <div>Theme</div>
            <div class="theme-toggle">
                <label for="themeToggle">Dark Mode</label>
                <input type="checkbox" id="themeToggle">
            </div>
            <div style="margin-top:1rem; font-size:0.8rem;">v1.0 • Local only</div>
        </div>
    </aside>

    <main class="main">
        <div class="header">
            <h1>All Registered Users</h1>
            <div class="action-group">
                <button class="add-btn" onclick="openAddUserModal()">Add User</button>
                <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
            </div>
        </div>

        <div class="container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th> 
                    </tr>
                </thead>
                <tbody id="usersList">
                    <tr><td colspan="6">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 1. Add User Modal -->
    <div class="modal-bg" id="addUserModal">
        <div class="modal-box">
            <h2>Add New User</h2>

            <form id="addUserForm" action="component/create_user.php" method="POST">

                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save</button>
                </div>

            </form>
        </div>
    </div>
    
    <!-- 2. Edit User Modal -->
    <div class="modal-bg" id="editUserModal">
        <div class="modal-box">
            <h2>Edit User</h2>

            <form id="editUserForm">
                <!-- Hidden field for the user ID being edited -->
                <input type="hidden" id="edit-id" name="id">

                <label for="edit-name">Name</label>
                <input type="text" id="edit-name" name="name" required>

                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" required>

                <label for="edit-password">New Password (Optional)</label>
                <input type="password" id="edit-password" name="password">
                <small style="display: block; margin-top: 5px; color: var(--secondary-color);">Leave blank to keep current password.</small>

                <label for="edit-role">Role</label>
                <select id="edit-role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditUserModal()">Cancel</button>
                    <button type="submit" class="btn-save">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Delete Confirmation Modal (NEW) -->
    <div class="modal-bg" id="deleteUserModal">
        <div class="modal-box">
            <h2 style="color: var(--danger-color);">Confirm Deletion</h2>
            <p>Are you sure you want to delete the user: 
                <strong><span id="delete-user-name" style="color: var(--danger-color);"></span></strong> 
                (ID: <span id="delete-user-id-display"></span>)?
            </p>
            <p style="margin-top: 10px;">This action cannot be undone and the account will be permanently removed.</p>
            
            <input type="hidden" id="delete-user-id-hidden">

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn-save" style="background: var(--danger-color);" onclick="executeDelete()">Delete User</button>
            </div>
        </div>
    </div>

<script>
    // Dark mode toggle
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

    // --- General Modal Functions ---

    function openAddUserModal() {
        document.getElementById("addUserModal").style.display = "flex";
    }

    function closeAddUserModal() {
        document.getElementById("addUserModal").style.display = "none";
    }

    function openEditUserModal(userId, name, email, role) {
        document.getElementById("edit-id").value = userId;
        document.getElementById("edit-name").value = name;
        document.getElementById("edit-email").value = email;
        document.getElementById("edit-role").value = role;
        document.getElementById("edit-password").value = ""; 
        document.getElementById("editUserModal").style.display = "flex";
    }

    function closeEditUserModal() {
        document.getElementById("editUserModal").style.display = "none";
    }

    // NEW: Delete Modal Functions
    function openDeleteModal(userId, userName) {
        document.getElementById("delete-user-id-hidden").value = userId;
        document.getElementById("delete-user-id-display").textContent = userId;
        document.getElementById("delete-user-name").textContent = userName;
        document.getElementById("deleteUserModal").style.display = "flex";
    }

    function closeDeleteModal() {
        document.getElementById("deleteUserModal").style.display = "none";
    }
    
    // --- Action Functions ---

    // 1. DELETE EXECUTION FUNCTION
    async function executeDelete() {
        const userId = document.getElementById("delete-user-id-hidden").value;
        closeDeleteModal(); // Close the modal immediately after confirmation

        try {
            const formData = new FormData();
            formData.append('user_id', userId);

            const response = await fetch('component/delete_user.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message); // Using alert for user feedback
                loadUsers(); // Refresh the list
            } else {
                alert(`Error: ${data.message}`);
            }
        } catch (err) {
            console.error('Delete error:', err);
            alert('An unexpected error occurred during deletion.');
        }
    }

    // 2. EDIT FUNCTION (Trigger the modal)
    function editUser(userId, name, email, role) {
        openEditUserModal(userId, name, email, role);
    }

    // 3. UPDATE SUBMIT (Handle the form submission)
    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('component/update_user.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                closeEditUserModal();
                loadUsers(); // Refresh the list
            } else {
                alert(`Update Failed: ${data.message}`);
            }

        } catch (err) {
            console.error('Update error:', err);
            alert('An unexpected error occurred during update.');
        }
    });

    // --- Load Users Function (Modified to call openDeleteModal) ---
    async function loadUsers() {
        try {
            const res = await fetch('get_user.php');
            const data = await res.json();
            const tbody = document.getElementById('usersList');
            
            if(data.success && Array.isArray(data.users)) {
                tbody.innerHTML = data.users.map((u,i)=>`
                    <tr>
                        <td>${i+1}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                        <td class="${u.is_online ? 'online':'offline'}">
                            ${u.is_online ? 'Online' : 'Offline'}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" 
                                        onclick="editUser(${u.id}, '${u.name.replace(/'/g, "\\'")}', '${u.email}', '${u.role}')">
                                    Edit
                                </button>
                                <!-- UPDATED: Calls the modal function -->
                                <button class="action-btn delete-btn" 
                                        onclick="openDeleteModal(${u.id}, '${u.name.replace(/'/g, "\\'")}')">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                const message = data.message || 'No users found.';
                tbody.innerHTML = `<tr><td colspan="6">${message}</td></tr>`;
            }
        } catch(err) {
            console.error(err);
            document.getElementById('usersList').innerHTML = `<tr><td colspan="6">Error loading users</td></tr>`;
        }
    }

    loadUsers();
    setInterval(loadUsers, 30000); // Poll for updates every 30 seconds

    // Close modal when clicking outside
    window.addEventListener("click", function(e) {
        let addUserModal =  document.getElementById("addUserModal");
        let editUserModal = document.getElementById("editUserModal");
        let deleteUserModal = document.getElementById("deleteUserModal"); // Reference to new modal
        
        if (e.target === addUserModal) closeAddUserModal();
        if (e.target === editUserModal) closeEditUserModal();
        if (e.target === deleteUserModal) closeDeleteModal(); // Close delete modal
    });

</script>
</body>
</html>