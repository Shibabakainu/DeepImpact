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
                
                // Check if the user is already in the room
                $check_sql = "SELECT COUNT(*) as player_count FROM room_players WHERE room_id = ? AND user_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                if ($check_stmt) {
                    $check_stmt->bind_param("ii", $room_id, $user_id);
                    $check_stmt->execute();
                    $check_stmt->bind_result($player_count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($player_count == 0) {
                        // Find the next available player position
                        $position_sql = "SELECT player_position FROM room_players WHERE room_id = ? ORDER BY player_position";
                        $position_stmt = $conn->prepare($position_sql);
                        if ($position_stmt) {
                            $position_stmt->bind_param("i", $room_id);
                            $position_stmt->execute();
                            $position_result = $position_stmt->get_result();

                            $occupied_positions = [];
                            while ($row = $position_result->fetch_assoc()) {
                                $occupied_positions[] = $row['player_position'];
                            }
                            $position_stmt->close();

                            // Find the lowest available position
                            $new_player_position = 1;
                            for ($i = 1; $i <= $max_players; $i++) {
                                if (!in_array($i, $occupied_positions)) {
                                    $new_player_position = $i;
                                    break;
                                }
                            }

                            // Update the player count in the room
                            $new_player_count = $current_players + 1;
                            $update_sql = "UPDATE rooms SET current_players = ? WHERE room_id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            if ($update_stmt) {
                                $update_stmt->bind_param("ii", $new_player_count, $room_id);
                                if ($update_stmt->execute()) {
                                    // Add the player to the room_players table with player_position
                                    $insert_sql = "INSERT INTO room_players (room_id, user_id, player_position) VALUES (?, ?, ?)";
                                    $insert_stmt = $conn->prepare($insert_sql);
                                    if ($insert_stmt) {
                                        $insert_stmt->bind_param("iii", $room_id, $user_id, $new_player_position);
                                        if ($insert_stmt->execute()) {
                                            // Store the player_position in the session for future use
                                            $_SESSION['player_position'] = $new_player_position;
                                            // After successfully joining the room
                                            $_SESSION['room_id'] = $room_id;
                                            // Success response
                                            echo 'success';
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
                            echo 'Error retrieving player positions: ' . $conn->error;
                        }
                    } else {
                        // User is already in the room
                        echo 'success';  // User is already in the room, no need to insert again
                    }
                } else {
                    echo 'Error preparing statement to check player: ' . $conn->error;
                }
            } else {
                echo 'ルームが満室です';
            }
        } else {
            echo 'ルームが見つからない54 sです';
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
