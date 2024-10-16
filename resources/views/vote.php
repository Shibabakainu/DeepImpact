<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

// Get room_id and room_card_id from POST data
$room_id = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : null;
$room_card_id = isset($_POST['room_card_id']) ? intval($_POST['room_card_id']) : null;

// Ensure room_id and room_card_id are provided
if (!$room_id || !$room_card_id) {
    echo json_encode(['success' => false, 'message' => 'ルームIDまたはカードIDが指定されていません。']);
    exit();
}

// Start a transaction
$conn->begin_transaction();

try {
    // Update the card's voted status to '1'
    $sql = "UPDATE room_cards SET voted = 1 WHERE room_id = ? AND room_card_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $room_id, $room_card_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Insert a vote into the votes table
        $sql_vote = "INSERT INTO votes (room_id, player_id, card_id) VALUES (?, ?, ?)";
        $stmt_vote = $conn->prepare($sql_vote);
        $stmt_vote->bind_param("iii", $room_id, $user_id, $room_card_id);
        $stmt_vote->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'カードが投票されました。']);
    } else {
        echo json_encode(['success' => false, 'message' => '指定されたカードが見つかりませんでした。']);
    }

    $stmt->close();
    $stmt_vote->close();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'カードの投票に失敗しました。']);
}

$conn->close();
