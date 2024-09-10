<?php
session_start();
include 'db_connect.php';

// ユーザーがログインしているか確認
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// POSTデータから room_id と room_card_id を取得
$room_id = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : null;
$room_card_id = isset($_POST['room_card_id']) ? intval($_POST['room_card_id']) : null;

// room_id と room_card_id の両方が提供されているか確認
if (!$room_id || !$room_card_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDまたはカードIDが指定されていません。']);
    exit();
}

// カードの voted ステータスを '1' に更新（カードが投票されたことを示す）
$sql = "UPDATE room_cards SET voted = 1 WHERE room_id = ? AND room_card_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $room_card_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'カードが投票されました。']);
    } else {
        echo json_encode(['success' => false, 'message' => '指定されたカードが見つかりませんでした。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'カードの投票に失敗しました。']);
}

$stmt->close();
$conn->close();
