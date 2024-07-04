<?php
$servername = "192.168.100.70";
$username = "thread";
$password = "PassWord1412%";
$dbname = "storyteller";

// 接続の作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続の確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ルーム検索のクエリ処理
if (isset($_GET['q'])) {
    $search = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT room_name, current_players, status FROM rooms WHERE room_name LIKE '%$search%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
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
    // 接続を閉じる
    $conn->close();
    exit; // これでルーム検索時にはこのスクリプトの実行を終了する
}
