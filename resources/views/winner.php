<?php
// winner_.php

$servername = "localhost";
$username = "username"; // データベースのユーザー名
$password = "password"; // データベースのパスワード
$dbname = "storyteller";

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ルームIDを指定
$room_id = 1;

// プレイヤーの最終得点を取得
$sql = "SELECT u.name, rp.score 
        FROM room_players rp
        JOIN users u ON rp.user_id = u.id
        WHERE rp.room_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

// 最高得点を持つプレイヤーを見つける
$winner = null;
$max_score = -1;

foreach ($players as $player) {
    if ($player['score'] > $max_score) {
        $max_score = $player['score'];
        $winner = $player['name'];
    }
}

// 勝者を表示
if ($winner) {
    echo "<h1>Winner: " . htmlspecialchars($winner) . "</h1>";
} else {
    echo "<h1>No winner found</h1>";
}

$conn->close();
?>