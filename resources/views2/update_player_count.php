<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = $_POST['room_name'];
    $current_players = (int)$_POST['current_players'];

    $sql_update_players = "UPDATE rooms SET current_players = ? WHERE room_name = ?";
    $stmt_update_players = $conn->prepare($sql_update_players);
    if ($stmt_update_players) {
        $stmt_update_players->bind_param("is", $current_players, $room_name);
        if ($stmt_update_players->execute()) {
            echo "プレイヤー数が更新されました。";
        } else {
            echo "プレイヤー数の更新エラー: " . $stmt_update_players->error;
        }
        $stmt_update_players->close();
    } else {
        echo "ステートメント準備エラー: " . $conn->error;
    }
    $conn->close();
}
