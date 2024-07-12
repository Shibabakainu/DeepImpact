<?php
session_start();
include 'db_connect.php';

// Get the room_id and user_id
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// Validate room_id and user_id
if ($room_id && $user_id) {
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Remove the player from the room_players table
        $sql_remove_player = "DELETE FROM room_players WHERE room_id = ? AND user_id = ?";
        $stmt_remove_player = $conn->prepare($sql_remove_player);
        if (!$stmt_remove_player) {
            throw new Exception($conn->error);
        }
        $stmt_remove_player->bind_param("ii", $room_id, $user_id);
        if (!$stmt_remove_player->execute()) {
            throw new Exception($stmt_remove_player->error);
        }
        $stmt_remove_player->close();
        
        // Decrement the current_players count in the rooms table
        $sql_decrement_players = "UPDATE rooms SET current_players = current_players - 1 WHERE room_id = ?";
        $stmt_decrement_players = $conn->prepare($sql_decrement_players);
        if (!$stmt_decrement_players) {
            throw new Exception($conn->error);
        }
        $stmt_decrement_players->bind_param("i", $room_id);
        if (!$stmt_decrement_players->execute()) {
            throw new Exception($stmt_decrement_players->error);
        }
        $stmt_decrement_players->close();
        
        // Commit the transaction
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        echo "エラー: " . $e->getMessage();
    }
    
    $conn->close();
} else {
    echo "エラー: 無効なルームIDまたはユーザーIDです。";
}
?>
