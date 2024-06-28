<?php
session_start();
session_destroy();
header("Location: /resources/views/login/login.php"); // Redirect to login page
exit;
?>