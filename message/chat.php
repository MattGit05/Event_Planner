<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../process/index.html");
    exit;
}
require "../db_config.php";

// Determine otherID
$role = $_SESSION['role'];
if ($role === 'admin') {
    $otherID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0; // Admin chats with specific user
    if ($otherID == 0) {
        // In a real app, this should redirect to a user selection screen
        echo "<div class='chat-container'><p class='error-message'>Select a user to chat with.</p></div>";
        exit;
    }
} else {
    // User chats with admin
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $otherID = $admin ? $admin['id'] : 1; // fallback to 1
    $stmt->close();
}

// Fetch the name of the user being chatted with (for header)
$otherUserName = "Unknown User";
if ($otherID > 0) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $otherID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
        $otherUserName = $user['name'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | <?php echo htmlspecialchars($otherUserName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff; /* Blue for user's messages */
            --secondary-color: #f0f0f0; /* Light gray for other's messages */
            --bg-color: #ffffff;
            --border-color: #e0e0e0;
            --input-bg: #f7f7f7;
            --text-color: #333;
            --shadow-light: 0 4px 10px rgba(0, 0, 0, 0.05);
            --header-bg: #f8f9fa;
        }

        /* --- Global Styles --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 20px;
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        /* --- Chat Container --- */
        .chat-container {
            width: 100%;
            max-width: 600px;
            background: var(--bg-color);
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        /* --- Header --- */
        .chat-header {
            padding: 15px 20px;
            background: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        /* --- Chat Box (Messages Display) --- */
        #chat-box {
            height: 450px; /* Increased height */
            overflow-y: auto;
            padding: 20px;
            background-color: #fafafa;
            flex-grow: 1;
        }
        
        /* Scrollbar styling for a cleaner look */
        #chat-box::-webkit-scrollbar { width: 8px; }
        #chat-box::-webkit-scrollbar-thumb { background-color: #c0c0c0; border-radius: 10px; }
        #chat-box::-webkit-scrollbar-track { background-color: #f1f1f1; }

        /* --- Individual Message Styling --- */
        .message-row {
            display: flex;
            margin-bottom: 15px;
            max-width: 85%;
        }

        .message-row.right {
            justify-content: flex-end;
            margin-left: auto;
        }

        .message-bubble {
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Message from the other user (Left) */
        .message-row.left .message-bubble {
            background-color: var(--secondary-color);
            color: var(--text-color);
            border-bottom-left-radius: 4px;
        }

        /* Message from the current user (Right) */
        .message-row.right .message-bubble {
            background-color: var(--primary-color);
            color: white;
            border-bottom-right-radius: 4px;
        }

        /* --- Input Area --- */
        .chat-input-area {
            display: flex;
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            background: var(--header-bg);
        }

        #message {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            margin-right: 10px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: var(--input-bg);
        }
        #message:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s;
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
        button:active {
            transform: translateY(0);
        }

        /* Responsive Adjustments */
        @media (max-width: 650px) {
            .chat-container {
                max-width: 100%;
                margin: 0;
                border-radius: 0;
            }
            body {
                padding: 0;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">Chatting with: <?php echo htmlspecialchars($otherUserName); ?></div>
    
    <div id="chat-box">
        <p style="text-align: center; color: #999;">Loading messages...</p>
    </div>

    <div class="chat-input-area">
        <input type="text" id="message" placeholder="Type your message..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
let otherID = <?php echo $otherID; ?>;
let currentUserID = <?php echo $_SESSION['user_id']; ?>; // Get current user ID for determining side

function loadMessages() {
    fetch("get_message.php?other_id=" + otherID)
    .then(res => res.json())
    .then(data => {
        let box = document.getElementById("chat-box");
        let shouldScroll = box.scrollTop + box.clientHeight >= box.scrollHeight - 20; // Check if user is near the bottom

        box.innerHTML = "";

        data.forEach(msg => {
            // Determine if the message is from the current user (right) or the other user (left)
            let side = msg.sender_id == currentUserID ? "right" : "left";
            
            // Note: The PHP logic has a small bug: msg.sender_id == otherID is not correct logic for determining 'left' or 'right'.
            // It should be compared against the CURRENT user ID, which we added above.

            box.innerHTML += `
                <div class="message-row ${side}">
                    <div class="message-bubble">
                        ${msg.message}
                    </div>
                </div>
            `;
        });

        // Only auto-scroll if the user was near the bottom before loading new messages
        if (shouldScroll) {
            box.scrollTop = box.scrollHeight;
        }
    })
    .catch(error => console.error("Error loading messages:", error));
}

function sendMessage() {
    let messageInput = document.getElementById("message");
    let message = messageInput.value.trim();

    if (!message) return; // Don't send empty messages

    let formData = new FormData();
    formData.append("receiver_id", otherID);
    formData.append("message", message);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() == "success") {
            messageInput.value = "";
            loadMessages();
        } else {
            console.error("Failed to send message:", data);
            alert("Error sending message.");
        }
    })
    .catch(error => {
        console.error("Network error sending message:", error);
        alert("Network error. Failed to send message.");
    });
}

// Auto refresh messages every 1 second
setInterval(loadMessages, 1000);
loadMessages();
</script>

</body>
</html>