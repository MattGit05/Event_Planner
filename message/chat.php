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
        echo "Select a user to chat with.";
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
?>

<div id="chat-box" style="height:300px; overflow-y:auto; border:1px solid #ccc; padding:10px;"></div>

<input type="text" id="message" placeholder="Type message...">
<button onclick="sendMessage()">Send</button>

<script>
let otherID = <?php echo $otherID; ?>;

function loadMessages() {
    fetch("get_message.php?other_id=" + otherID)
    .then(res => res.json())
    .then(data => {
        let box = document.getElementById("chat-box");
        box.innerHTML = "";

        data.forEach(msg => {
            let side = msg.sender_id == otherID ? "left" : "right";
            box.innerHTML += `
                <div style="text-align:${side}; margin:5px;">
                    <span style="padding:6px 10px; background:#eee; border-radius:10px;">
                        ${msg.message}
                    </span>
                </div>
            `;
        });

        box.scrollTop = box.scrollHeight;
    });
}

function sendMessage() {
    let message = document.getElementById("message").value;

    let formData = new FormData();
    formData.append("receiver_id", otherID);
    formData.append("message", message);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    }).then(res => res.text())
      .then(data => {
        if (data == "success") {
            document.getElementById("message").value = "";
            loadMessages();
        }
    });
}

// Auto refresh messages every 1 second
setInterval(loadMessages, 1000);
loadMessages();
</script>
