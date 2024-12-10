<?php
session_start();
include 'db_connect.php';
include 'game_functions.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// Get the room_id from the session or URL
$room_id = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : null;
if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDが指定されていません。']);
    exit();
}

// Get player's position (e.g., 1 to 6) within the room from session or database
$player_position = isset($_SESSION['player_position']) ? $_SESSION['player_position'] : null;
if (!$player_position) {
    echo json_encode(['success' => false, 'message' => 'プレイヤー位置が特定できません。']);
    exit();
}

// Get the current turn
$current_turn = getCurrentTurn($room_id);

// Check the 'drew' flag for the player
$checkDrew = "SELECT drew FROM room_players WHERE room_id = ? AND player_position = ?";
$stmt = $conn->prepare($checkDrew);
$stmt->bind_param("ii", $room_id, $player_position);
$stmt->execute();
$stmt->bind_result($drew);
$stmt->fetch();
$stmt->close();

if ($drew == 1) {
    echo json_encode(['success' => false, 'message' => 'このターンのカードは既に引きました。']);
    exit();
}

// Determine the number of cards to draw based on the turn
$cards_to_draw = ($current_turn == 1) ? 5 : 1;

// Check how many cards the player already has
$sql_check = "SELECT COUNT(*) as card_count FROM room_cards WHERE room_id = ? AND player_position = ? AND hide = 0";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $room_id, $player_position);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_card_count = $row['card_count'];
$stmt->close();

// Prevent drawing if the player already has the max cards
if (($current_turn == 1 && $current_card_count >= 5) || ($current_turn > 1 && $current_card_count >= 6)) {
    echo json_encode(['success' => false, 'message' => '既にカードを引きました。']);
    exit();
}

// Draw the required number of cards
$sql = "
    SELECT c.Card_id, c.Card_name, c.Image_path 
    FROM Card c
    LEFT JOIN room_cards rc ON c.Card_id = rc.card_id AND rc.room_id = ? AND rc.hide = 0
    WHERE rc.room_id IS NULL
    ORDER BY RAND()
    LIMIT ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $cards_to_draw);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    // Store the room_card_id of the newly inserted card
    $sql_insert = "INSERT INTO room_cards (room_id, card_id, player_position) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $room_id, $row['Card_id'], $player_position);
    $stmt_insert->execute();
    
    // Get the last inserted ID (room_card_id)
    $room_card_id = $conn->insert_id;

    // Append room_card_id to the card details for the response
    $cards[] = [
        'room_card_id' => $room_card_id,
        'Card_id' => $row['Card_id'],
        'Card_name' => $row['Card_name'],
        'Image_path' => $row['Image_path']
    ];
}

// Update the drew flag to 1 after successful draw
$updateDrew = "UPDATE room_players SET drew = 1 WHERE room_id = ? AND player_position = ?";
$stmt = $conn->prepare($updateDrew);
$stmt->bind_param("ii", $room_id, $player_position);
$stmt->execute();
$stmt->close();

// Return the cards as JSON response
echo json_encode(['success' => true, 'cards' => $cards]);

$conn->close();
?>
