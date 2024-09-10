<?php
session_start();
include 'db_connect.php';

// Get room_id from GET data
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

if (!$room_id) {
    echo 'ルームIDが指定されていません。';
    exit();
}

// Fetch all selected cards
$sql = "
    SELECT c.Card_id, c.Card_name, c.Image_path 
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.selected = 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();

$html = '';
while ($row = $result->fetch_assoc()) {
    $html .= '<div class="selected-card" data-card-id="' . $row['Card_id'] . '">';
    $html .= '<img src="../../images/' . $row['Image_path'] . '" width="130px" alt="' . htmlspecialchars($row['Card_name'], ENT_QUOTES) . '">';
    $html .= '</div>';
}

echo $html;

$stmt->close();
$conn->close();
