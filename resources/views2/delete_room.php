<?php
session_start();
include 'db_connect.php';

// POSTリクエストからルームIDを取得
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : (isset($_SESSION['room_id']) ? $_SESSION['room_id'] : 0);
echo '退出対象のルームID: ' . $room_id . '<br>';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
echo 'ユーザーID: ' . $user_id . '<br>';

if ($room_id == 0) {
    echo "ルームIDが取得できませんでした。";
    exit;
}

if ($user_id === null) {
    echo "ユーザーIDが取得できませんでした。";
    exit;
}

try {
    // トランザクションを開始
    $conn->begin_transaction();

    // room_playersテーブルから該当ルームのプレイヤーデータを削除する
    $sql_delete_players = "DELETE FROM room_players WHERE user_id = ?";
    $stmt_delete_players = $conn->prepare($sql_delete_players);
    if ($stmt_delete_players) {
        $stmt_delete_players->bind_param("i", $user_id);
        if (!$stmt_delete_players->execute()) {
            throw new Exception("ルームのプレイヤーデータの削除中にエラーが発生しました。");
        }
        echo "ルームのプレイヤーデータを削除しました。<br>";
        $stmt_delete_players->close();
    } else {
        throw new Exception("ルームのプレイヤーデータの削除ステートメントの準備中にエラーが発生しました。");
    }

    // roomsテーブルから該当ルームを削除する
    $sql_delete_room = "DELETE FROM rooms WHERE room_id = ?";
    $stmt_delete_room = $conn->prepare($sql_delete_room);
    if ($stmt_delete_room) {
        $stmt_delete_room->bind_param("i", $room_id);
        if (!$stmt_delete_room->execute()) {
            throw new Exception("ルームの削除中にエラーが発生しました。");
        }
        echo "ルームを削除しました。<br>";
        $stmt_delete_room->close();
    } else {
        throw new Exception("ルームの削除ステートメントの準備中にエラーが発生しました。");
    }

    // トランザクションをコミット
    $conn->commit();

    // 削除が完了した後にroom_setting.phpへリダイレクトする
    header("Location: room_setting.php");
    exit; // 必ずexit()することで以降の処理が実行されないようにする

} catch (Exception $e) {
    // エラーが発生した場合はトランザクションをロールバック
    $conn->rollback();
    echo $e->getMessage();
}

$conn->close();
