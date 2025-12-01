<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

include 'db_config.php';

// Consider users online if last_active within last 5 minutes
$query = "SELECT id, name, email, role, 
          CASE WHEN last_active >= NOW() - INTERVAL 5 MINUTE THEN 1 ELSE 0 END AS is_online
          FROM users";
$result = $conn->query($query);

$users = [];
while($row = $result->fetch_assoc()){
    $users[] = $row;
}

echo json_encode(['success'=>true,'users'=>$users]);
?>
