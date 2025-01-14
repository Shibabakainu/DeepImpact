<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// Retrieve room_id from the GET request
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;
if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDが指定されていません。']);
    exit();
}

// Assuming player position is saved in the session
$player_position = isset($_SESSION['player_position']) ? $_SESSION['player_position'] : null;
if (!$player_position) {
    echo json_encode(['success' => false, 'message' => 'プレイヤーの位置が指定されていません。']);
    exit();
}

// SQL to retrieve unselected (on-hand) cards
$sql_unselected = "
    SELECT rc.room_card_id, c.Card_id, c.Card_name, c.Image_path
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.player_position = ? AND rc.selected = 0 AND rc.hide = 0
";

// SQL to retrieve selected cards for the vote area
$sql_selected = "
    SELECT rc.room_card_id, c.Card_id, c.Card_name, c.Image_path
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.selected = 1 AND rc.hide = 0
";

$cards_unselected = [];
$cards_selected = [];

if ($stmt = $conn->prepare($sql_unselected)) {
    // Bind room_id and player_position to the query for unselected cards
    $stmt->bind_param('ii', $room_id, $player_position);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cards_unselected[] = $row;
    }
    $stmt->close();
}

if ($stmt = $conn->prepare($sql_selected)) {
    // Bind room_id to the query for selected cards
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cards_selected[] = $row;
    }
    $stmt->close();
}

// Send JSON response with both unselected (on-hand) and selected cards
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'cards_unselected' => $cards_unselected,
    'cards_selected' => $cards_selected
]);

$conn->close();
?>
