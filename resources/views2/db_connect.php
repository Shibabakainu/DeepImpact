<?php
//$servername = "localhost";
//$username = "username";
//$password = "password";

$servername = "49.212.166.241";
//$servername = "192.168.3.79";
$username = "thread";
$password = "PassWord1412%";
$dbname = "storyteller";

// 接続の作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続の確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
