<?php
include 'db_connect.php';
include 'game_functions.php';

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDが指定されていません。']);
    exit();
}

// Get the current turn for the room
$current_turn = getCurrentTurn($room_id);

// Fetch voting details
$sql = "
    SELECT 
        rp.player_position,
        u.name AS player_name,
        c.Card_name,
        c.Image_path
    FROM votes v
    JOIN room_cards rc ON v.room_card_id = rc.room_card_id
    JOIN cards c ON rc.card_id = c.Card_id
    JOIN room_players rp ON v.player_id = rp.user_id AND rp.room_id = ?
    JOIN users u ON rp.user_id = u.id
    WHERE rc.room_id = ? AND rc.turn_number = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $room_id, $room_id, $current_turn);
$stmt->execute();
$result = $stmt->get_result();

$votingDetails = [];
while ($row = $result->fetch_assoc()) {
    $votingDetails[] = [
        'player_name' => $row['player_name'],
        'card_name' => $row['Card_name'],
        'image_path' => $row['Image_path']
    ];
}

echo json_encode(['success' => true, 'votingDetails' => $votingDetails]);

$stmt->close();
$conn->close();
?>
