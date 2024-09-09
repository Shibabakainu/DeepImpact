<?php
session_start();
include 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

$card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : null;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;

if (!$card_id || !$room_id) {
    echo json_encode(['success' => false, 'message' => 'データが不完全です。']);
    exit();
}

// Update card status to 7
$sql = "UPDATE room_cards SET status = 7 WHERE room_id = ? AND card_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $room_id, $card_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'カード選択エラー']);
}

$conn->close();
?>
