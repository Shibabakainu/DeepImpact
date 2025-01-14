<?php
session_start();
session_destroy();
header("Location: /DeepImpact/resources/views2/login/login.php"); // Redirect to login page
exit;
