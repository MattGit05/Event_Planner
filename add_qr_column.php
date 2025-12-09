<?php
include 'db_config.php';

$sql = "ALTER TABLE users ADD COLUMN qr_code VARCHAR(255) NULL";

if ($conn->query($sql) === TRUE) {
    echo "Column qr_code added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
