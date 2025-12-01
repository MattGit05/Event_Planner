<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

include '../db_config.php';

// Fetch events for the logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM events WHERE created_by = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Events</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
body {
    background-color: #f6f8fc;
    font-family: 'Poppins', sans-serif;
}
.container {
    margin-top: 50px;
}
.card {
    margin-bottom: 20px;
}
</style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">My Events</h2>
    <?php if($result->num_rows > 0): ?>
        <div class="row">
            <?php while($event = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                        <p class="card-text"><small class="text-muted"><?= $event['date'] ?> at <?= $event['time'] ?></small></p>
                        <a href="modals/edit_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have no events yet. <a href="add_event.php">Create one</a>.</p>
    <?php endif; ?>
</div>
</body>
</html>
