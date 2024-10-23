<?php
session_start();
include 'db_connect.php';

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

// Check if the player has already drawn cards (to prevent redrawing)
$sql_check = "SELECT COUNT(*) as card_count FROM room_cards WHERE room_id = ? AND status = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $room_id, $player_position);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['card_count'] >= 5) {
    echo json_encode(['success' => false, 'message' => '既に5枚のカードが配られています。']);
    exit();
}

// SQL query to randomly select 5 cards that haven't been assigned yet in the room
$sql = "
    SELECT c.Card_id, c.Card_name, c.Image_path 
    FROM Card c
    LEFT JOIN room_cards rc ON c.Card_id = rc.card_id AND rc.room_id = ?
    WHERE rc.room_id IS NULL OR rc.status = 0
    ORDER BY RAND()
    LIMIT 5
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    // Store the room_card_id of the newly inserted card
    $sql_insert = "INSERT INTO room_cards (room_id, card_id, status) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $room_id, $row['Card_id'], $player_position);
    $stmt_insert->execute();
    
    // Get the last inserted ID (room_card_id)
    $room_card_id = $conn->insert_id;

    // Append room_card_id to the card details for the response
    $cards[] = [
        'room_card_id' => $room_card_id,  // Add this line
        'Card_id' => $row['Card_id'],
        'Card_name' => $row['Card_name'],
        'Image_path' => $row['Image_path']
    ];
}

// Return the cards as JSON response
echo json_encode(['success' => true, 'cards' => $cards]);

$conn->close();
?>
