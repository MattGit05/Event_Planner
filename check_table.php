<?php
include 'db_config.php';

$result = $conn->query("SHOW TABLES LIKE 'events'");
if ($result->num_rows > 0) {
    echo "Table 'events' exists.";
} else {
    echo "Table 'events' does not exist.";
}

$conn->close();
?>
