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
    <title>QR Code Scanner - Eventify</title>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
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
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
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

        /* --- Main Content --- */
        .main {
            flex: 1;
            padding: 2.5rem 3rem;
            margin-left: 250px;
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

        /* --- QR Scanner Content --- */
        .scanner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .scanner-card {
            background: var(--surface-light);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-default);
            border: 1px solid var(--header-bg-light);
            width: 100%;
            text-align: center;
        }

        .scanner-card h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .video-container {
            position: relative;
            display: inline-block;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        #qrVideo {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 12px;
        }

        .result-card {
            background: var(--surface-light);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-default);
            border: 1px solid var(--header-bg-light);
            width: 100%;
            margin-top: 1rem;
        }

        .result-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .status-message {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .user-info {
            text-align: left;
            background: rgba(0, 123, 255, 0.05);
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .user-info div {
            margin-bottom: 0.5rem;
        }

        .user-info strong {
            color: var(--primary-color);
        }

        .error-message {
            color: var(--danger-color);
            font-weight: 500;
        }

        .back-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: #5a6268;
            text-decoration: none;
            color: white;
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; height: auto; padding: 1rem; }
            .main { padding: 1.5rem; margin-left: 0; }
            .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .scanner-card { padding: 1.5rem; }
            #qrVideo { max-width: 300px; }
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
                <a href="calendar.php"><span class="material-icons-round">calendar_month</span> Calendar</a>
                <a href="users.php"><span class="material-icons-round">group</span> View all Users</a>
               <li class="nav-item">
                    <a class="nav-link" href="notification.php">
                        Notifications <span id="notifBadge" class="badge bg-danger">0</span>
                    </a>
                </li>
                <a href="qr_scanner.php" class="active"><span class="material-icons-round">qr_code_scanner</span> QR Scanner</a>
                <a href="admin_settings.php"><span class="material-icons-round">settings</span> Settings</a>
                <a href="Budgeting/budget.php"><span class="material-icons-round">account_balance_wallet</span> Budget</a>
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
                <h1>QR Code Scanner</h1>
                <p>Scan QR codes to identify users and verify attendance</p>
            </div>
            <button class="logout-btn" onclick="window.location='logout.php'">Logout</button>
        </div>

        <div class="scanner-container">
            <div class="scanner-card">
                <h2><span class="material-icons-round" style="vertical-align: -2px;">qr_code_scanner</span> QR Code Scanner</h2>
                <div class="video-container">
                    <video id="qrVideo"></video>
                </div>
                <div class="result-card">
                    <h3>Scan Result</h3>
                    <div id="scanResult" class="status-message">Initializing camera...</div>
                </div>
            </div>

            <a href="index.php" class="back-btn">← Back to Dashboard</a>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const qrVideo = document.getElementById("qrVideo");
            const scanResult = document.getElementById("scanResult");
            let scanner = null;

            // Initialize scanner
            startScanner();

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

            function startScanner() {
                scanner = new Instascan.Scanner({ video: qrVideo });
                scanner.addListener('scan', function (content) {
                    console.log('QR Code content:', content);

                    // Process QR content if it's a user QR
                    if (content.startsWith('USER:')) {
                        const userId = content.split(':')[1];
                        processUserQR(userId);
                    } else {
                        scanResult.innerHTML = `<div class="status-message">Scanned: ${content}</div>`;
                    }
                });

                Instascan.Camera.getCameras().then(function (cameras) {
                    if (cameras.length > 0) {
                        scanner.start(cameras[0]);
                        scanResult.innerHTML = '<div class="status-message">Camera ready. Point at QR code.</div>';
                    } else {
                        scanResult.innerHTML = '<div class="error-message">No cameras found.</div>';
                    }
                }).catch(function (e) {
                    console.error(e);
                    scanResult.innerHTML = '<div class="error-message">Camera access denied or unavailable.</div>';
                });
            }

            // Process user QR code
            async function processUserQR(userId) {
                try {
                    scanResult.innerHTML = '<div class="status-message">Processing user data...</div>';

                    const response = await fetch('get_user.php?id=' + userId);
                    const userData = await response.json();

                    if (userData.success) {
                        scanResult.innerHTML = `
                            <div class="user-info">
                                <div><strong>User Found:</strong></div>
                                <div><strong>Name:</strong> ${userData.user.name}</div>
                                <div><strong>Email:</strong> ${userData.user.email}</div>
                                <div><strong>Role:</strong> ${userData.user.role}</div>
                                <div><strong>ID:</strong> ${userData.user.id}</div>
                            </div>
                        `;
                    } else {
                        scanResult.innerHTML = '<div class="error-message">User not found or invalid QR code.</div>';
                    }
                } catch (error) {
                    console.error('Error processing user QR:', error);
                    scanResult.innerHTML = '<div class="error-message">Error processing QR code.</div>';
                }
            }
        });
    </script>

</body>
</html>
