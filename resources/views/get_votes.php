<?php
include 'db_connect.php';

$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

if (!$room_id) {
    die("ルームIDが指定されていません。");
}

$sql = "
    SELECT rc.room_card_id, c.Card_name, c.Image_path
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.selected = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}

foreach ($cards as $card) {
    echo '<div class="selected-card" data-card-id="' . $card['room_card_id'] . '">';
    echo '<img src="../../images/' . $card['Image_path'] . '" alt="' . htmlspecialchars($card['Card_name'], ENT_QUOTES) . '">';
    echo '</div>';
}

$stmt->close();
$conn->close();
