<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db_config.php'; // Your MySQL connection

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = 'user'; // default role

    if(empty($name) || empty($email) || empty($password)){
        echo json_encode(['status'=>'error','message'=>'All fields are required']);
        exit;
    }

     if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email address"
    ]);
    exit;
}


    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Email already exists']);
        exit;
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss",$name,$email,$hashed_password,$role);

    if($stmt->execute()){
        echo json_encode(['status'=>'success','message'=>'Account created successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to create account']);
    }

    $stmt->close();
    $conn->close();
}
?>
