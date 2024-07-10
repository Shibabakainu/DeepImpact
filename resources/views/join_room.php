<?php
session_start();
include 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id && isset($_POST['room_name'])) {
    $room_name = $conn->real_escape_string($_POST['room_name']);
    
    // Check if the room exists and has space
    $sql = "SELECT room_id, current_players, max_players FROM rooms WHERE room_name = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $room_name);
        $stmt->execute();
        $stmt->bind_result($room_id, $current_players, $max_players);
        
        if ($stmt->fetch()) {
            if ($current_players < $max_players) {
                $stmt->close();
                
                // Update the player count in the room
                $new_player_count = $current_players + 1;
                $update_sql = "UPDATE rooms SET current_players = ? WHERE room_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("ii", $new_player_count, $room_id);
                    if ($update_stmt->execute()) {
                        // Add the player to the room_players table
                        $insert_sql = "INSERT INTO room_players (room_id, user_id) VALUES (?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        if ($insert_stmt) {
                            $insert_stmt->bind_param("ii", $room_id, $user_id);
                            if ($insert_stmt->execute()) {
                                // Redirect to room_detail.php after joining successfully
                                echo 'success';  // Changed from header to echo for debugging
                            } else {
                                echo 'Error adding player to room: ' . $insert_stmt->error;
                            }
                            $insert_stmt->close();
                        } else {
                            echo 'Error preparing statement to add player: ' . $conn->error;
                        }
                    } else {
                        echo 'Error updating player count: ' . $update_stmt->error;
                    }
                    $update_stmt->close();
                }
            } else {
                echo 'ルームが見つからないか、満室です';
            }
        } else {
            echo 'ルームが見つからないか、満室です';
        }
        $stmt->close();
    } else {
        echo 'Error preparing statement: ' . $conn->error;
    }
} else {
    echo '無効なリクエスト';
}

$conn->close();
?>
