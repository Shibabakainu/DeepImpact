<?php
session_start();
include 'db_connect.php';

// POSTリクエストからルームIDを取得
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
echo '退出対象のルームID: ' . $room_id;

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// 現在のホストユーザー名を取得
$sql_get_current_host = "SELECT host_user_name FROM rooms WHERE room_id = ?";
$stmt_get_current_host = $conn->prepare($sql_get_current_host);
if ($stmt_get_current_host) {
    $stmt_get_current_host->bind_param("i", $room_id);
    $stmt_get_current_host->execute();
    $stmt_get_current_host->bind_result($current_host_user_name);
    if ($stmt_get_current_host->fetch()) {
        // 現在のホストユーザー名を取得
        $currentHostUserName = htmlspecialchars($current_host_user_name, ENT_QUOTES, 'UTF-8');
    }
    $stmt_get_current_host->close();
} else {
    echo "現在のホストユーザー名の取得中にエラーが発生しました。";
}

// ホストユーザーであれば、次に入室したユーザーを新しいホストとして設定する
if ($currentHostUserName && $user_id) {
    // 最も早く入室したユーザーを新しいホストにする
    $sql_next_host = "SELECT user_name FROM room_players WHERE room_id = ? ORDER BY joined_at ASC LIMIT 1";
    $stmt_next_host = $conn->prepare($sql_next_host);
    if ($stmt_next_host) {
        $stmt_next_host->bind_param("i", $room_id);
        $stmt_next_host->execute();
        $stmt_next_host->bind_result($next_host_user_name);
        if ($stmt_next_host->fetch()) {
            $nextHostUserName = htmlspecialchars($next_host_user_name, ENT_QUOTES, 'UTF-8');

            // roomsテーブルを更新して新しいホストを設定する
            $sql_update_host = "UPDATE rooms SET host_user_name = ? WHERE room_id = ?";
            $stmt_update_host = $conn->prepare($sql_update_host);
            if ($stmt_update_host) {
                $stmt_update_host->bind_param("si", $nextHostUserName, $room_id);
                if ($stmt_update_host->execute()) {
                    echo "新しいホストとして $nextHostUserName を設定しました。";
                } else {
                    echo "新しいホストの設定中にエラーが発生しました。";
                }
                $stmt_update_host->close();
            } else {
                echo "新しいホストの設定ステートメントの準備中にエラーが発生しました。";
            }
        }
        $stmt_next_host->close();
    } else {
        echo "次のホストの取得ステートメントの準備中にエラーが発生しました。";
    }
} else {
    echo "ホストユーザーではないため、ルームを退出する権限がありません。";
}

$conn->close();
