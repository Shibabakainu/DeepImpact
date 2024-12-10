<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'ユーザーがログインしていません。']);
    exit();
}

// Validate room_id and room_card_id
$room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;
$room_card_id = isset($_POST['room_card_id']) ? $_POST['room_card_id'] : null;

if (!$room_id || !$room_card_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDまたはカードIDが指定されていません。']);
    exit();
}

// Ensure player_position is set in session
if (!isset($_SESSION['player_position'])) {
    echo json_encode(['success' => false, 'message' => 'プレイヤーポジションが設定されていません。']);
    exit();
}

$player_position = $_SESSION['player_position'];

// Log the IDs for debugging
error_log("Room ID: " . $room_id . ", Room Card ID: " . $room_card_id . ", Player Position: " . $player_position);

// Check if the player has already selected a card
$sql_check_selected = "SELECT * FROM room_cards WHERE room_id = ? AND player_position = ? AND selected = 1";
$stmt_check = $conn->prepare($sql_check_selected);
$stmt_check->bind_param('ii', $room_id, $player_position);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => '既にカードを選択しています。']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

// Check if the card exists in room_cards
$sql = "SELECT * FROM room_cards WHERE room_card_id = ? AND room_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $room_card_id, $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'カードが見つかりません。']);
    $stmt->close();
    exit();
}

// Update the room_cards table to set this card as selected
$sql_update = "UPDATE room_cards SET selected = 1 WHERE room_card_id = ? AND room_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ii', $room_card_id, $room_id);

if ($stmt_update->execute()) {
    echo json_encode(['success' => true, 'card_id' => $room_card_id, 'message' => 'カードが選択されました。']);
} else {
    echo json_encode(['success' => false, 'message' => 'カードの選択に失敗しました。']);
}

// Close statements and connection
$stmt_update->close();
$stmt->close();
$conn->close();
