<?php
session_start();
session_destroy();
header("Location: process/index.html");
exit();
?>
