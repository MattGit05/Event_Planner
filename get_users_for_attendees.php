<?php
header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

include 'db_config.php';

$query = "SELECT id, name, email FROM users WHERE id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while($row = $result->fetch_assoc()){
    $users[] = $row;
}

echo json_encode(['success'=>true,'users'=>$users]);
?>
