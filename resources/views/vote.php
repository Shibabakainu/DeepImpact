<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// Get room_id and card_id from POST data
$room_id = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : null;
$card_id = isset($_POST['card_id']) ? $_POST['card_id'] : null;

if (!$room_id || !$card_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDまたはカードIDが指定されていません。']);
    exit();
}

// Update the card's voted status to '1' (indicating the card has been voted on)
$sql = "UPDATE room_cards SET voted = 1 WHERE room_id = ? AND card_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $card_id);

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
?>
