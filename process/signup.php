<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db_config.php'; // Your MySQL connection
require 'phpqrcode/qrlib.php'; 

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

        // -------------------------------------------
        // ✅ 1. GET NEW USER ID
        // -------------------------------------------
        $newUserId = $conn->insert_id;

        // -------------------------------------------
        // ✅ 2. QR CONTENT
        // -------------------------------------------
        $qrContent = "USER:$newUserId";

        // -------------------------------------------
        // ✅ 3. OUTPUT FILE (make sure folder exists)
        // -------------------------------------------
        $qrFile = "qrcodes/user_" . $newUserId . ".png";

        // -------------------------------------------
        // ✅ 4. GENERATE QR CODE
        // -------------------------------------------
        QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 5);

        // -------------------------------------------
        // ✅ 5. SAVE QR FILE NAME IN DB
        // -------------------------------------------
        $stmt2 = $conn->prepare("UPDATE users SET qr_code=? WHERE id=?");
        $shortPath = "qrcodes/user_" . $newUserId . ".png"; // path for display
        $stmt2->bind_param("si", $shortPath, $newUserId);
        $stmt2->execute();
        $stmt2->close();

        // -------------------------------------------
        // ✅ 6. SEND SUCCESS RESPONSE
        // -------------------------------------------
        echo json_encode(['status'=>'success','message'=>'Account created successfully with QR code']);
    
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to create account']);
    }

    $stmt->close();
    $conn->close();
}
?>
