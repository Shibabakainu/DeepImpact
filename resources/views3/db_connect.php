<?php
//$servername = "localhost";
//$username = "username";
//$password = "password";

$servername = "10.0.2.15";
$username = "shiba";
$password = "Shiba@0612";
$dbname = "storyteller";

// 接続の作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続の確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
