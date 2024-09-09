<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// SQL query to randomly select 5 cards from the database
$sql = "SELECT Card_id, Card_name, Image_path, IsVisible FROM Card WHERE IsVisible = 1 ORDER BY RAND() LIMIT 5";

if ($result = $conn->query($sql)) {
    $cards = [];

    while ($row = $result->fetch_assoc()) {
        $cards[] = [
            'Card_id' => $row['Card_id'],
            'Card_name' => $row['Card_name'],
            'Image_path' => $row['Image_path'],
            'IsVisible' => $row['IsVisible']
        ];
    }

    // Update the selected cards' IsVisible status to 2 (indicating they are now in the player's hand)
    if (!empty($cards)) {
        $selectedCardIds = array_column($cards, 'Card_id');
        $idsToUpdate = implode(",", $selectedCardIds);

        // Update the IsVisible field for the drawn cards
        $updateSql = "UPDATE Card SET IsVisible = 2 WHERE Card_id IN ($idsToUpdate)";
        $conn->query($updateSql);
    }

    // Return the cards as a JSON response
    echo json_encode(['success' => true, 'cards' => $cards]);
} else {
    // Handle errors in case the query fails
    echo json_encode(['success' => false, 'message' => 'カードの取得に失敗しました。']);
}

$conn->close();
?>
