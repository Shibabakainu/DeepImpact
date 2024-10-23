<?php
session_start();
include 'db_connect.php';

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

// SQL to retrieve drawn cards for the user
$sql = "
    SELECT rc.room_card_id, c.Card_id, c.Card_name, c.Image_path
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.user_id = ? AND rc.status = 'drawn'  -- Check the status here
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('ii', $room_id, $user_id); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = [];
    while ($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'cards' => $cards]);
} else {
    echo json_encode(['success' => false, 'message' => 'カードデータの取得に失敗しました: ' . $conn->error]);
}

$conn->close();
?>
