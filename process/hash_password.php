<?php
$defaultPassword = 'Admin123!';
$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
echo $hashedPassword;
?>
