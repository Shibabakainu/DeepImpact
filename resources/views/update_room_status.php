<?php
session_start();
include 'db_connect.php';

// Get the room_id from the POST request
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;

if ($room_id) {
    // Update the room status to 'in game'
    $sql_update_status = "UPDATE rooms SET status = 'in_game' WHERE room_id = ?";
    $stmt_update_status = $conn->prepare($sql_update_status);
    if ($stmt_update_status) {
        $stmt_update_status->bind_param("i", $room_id);
        if ($stmt_update_status->execute()) {
            echo "success";
        } else {
            echo "エラー: " . $stmt_update_status->error;
        }
        $stmt_update_status->close();
    } else {
        echo "エラー: " . $conn->error;
    }
} else {
    echo "エラー: 無効なルームIDです。";
}

$conn->close();
?>
