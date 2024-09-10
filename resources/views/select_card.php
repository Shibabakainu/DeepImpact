<?php
session_start();
include 'db_connect.php';

// POSTリクエストからデータを取得
$card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : null;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;

// 既にカードが選択されているか確認（optional）
$sql_check = "SELECT selected FROM room_cards WHERE card_id = ? AND room_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $card_id, $room_id);
$stmt_check->execute();
$stmt_check->bind_result($selected);
$stmt_check->fetch();
$stmt_check->close();

if ($selected == 1) {
    // 既に選ばれていたらエラーを返す
    echo json_encode(['success' => false, 'message' => 'このカードは既に選ばれています。']);
    exit;
}

$sql = "UPDATE room_cards SET selected = 1 WHERE room_id = ? AND card_id = ? AND selected = 0 LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $room_id, $card_id);
$result = $stmt->execute();

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'SQLの準備に失敗しました: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ii', $card_id, $room_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'card_id' => $card_id, 'message' => 'カードが選択されました。']);
} else {
    echo json_encode(['success' => false, 'message' => 'カード選択の更新に失敗しました: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
