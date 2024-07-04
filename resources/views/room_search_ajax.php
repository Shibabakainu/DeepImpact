<?php
include 'db_connect.php';

$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$sql = "SELECT room_name, current_players, status FROM rooms WHERE room_name LIKE '%$search%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 各行のデータを出力
    while ($row = $result->fetch_assoc()) {
        echo '<div class="room">';
        echo '<div class="room-name">' . $row["room_name"] . '</div>';
        echo '<div class="room-status">プレイヤー: ' . $row["current_players"] . '/6</div>';
        echo '<div class="room-progress ' . strtolower($row["status"]) . '">' . $row["status"] . '</div>';
        echo '</div>';
    }
} else {
    echo "ルームが見つかりません";
}
$conn->close();
