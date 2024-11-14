<?php

include 'db_connect.php';
include 'game_functions.php';

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Check if the game is finished
$statusQuery = "SELECT status FROM rooms WHERE room_id = ?";
$statusStmt = $conn->prepare($statusQuery);
$statusStmt->bind_param("i", $room_id);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$statusRow = $statusResult->fetch_assoc();
$statusStmt->close();

if ($statusRow['status'] == 'finished') {
    echo json_encode(["game_over" => true, "message" => "The game is finished."]);
    exit();
}

$response = [
    'votingComplete' => false,
    'turn' => null,
    'scoreboard' => '',
];

if ($room_id) {
    if (isVotingComplete($room_id)) {
        // Check if the turn has already been updated
        $checkTurnQuery = "SELECT turn_updated FROM rooms WHERE room_id = ?";
        $checkTurnStmt = $conn->prepare($checkTurnQuery);
        $checkTurnStmt->bind_param("i", $room_id);
        $checkTurnStmt->execute();
        $checkTurnStmt->bind_result($turnUpdated);
        $checkTurnStmt->fetch();
        $checkTurnStmt->close();
    
        if ($turnUpdated == 0) {
            // Update the score and turn only once
            updateScore($room_id);
            incrementTurn($room_id);
    
            // Set turn_updated to 1 to mark that updates are done
            $updateTurnQuery = "UPDATE rooms SET turn_updated = 1 WHERE room_id = ?";
            $updateTurnStmt = $conn->prepare($updateTurnQuery);
            $updateTurnStmt->bind_param("i", $room_id);
            $updateTurnStmt->execute();
            $updateTurnStmt->close();
    
            $response['votingComplete'] = true;
            $response['turn'] = getCurrentTurn($room_id);
    
            // Capture scoreboard output
            ob_start();
            getScoreboardHtml($room_id);
            $response['scoreboard'] = ob_get_clean();
            
            //draw new card for a new turn
            foreach ($players as $player) {
                drawNewCard($room_id, $player['player_position']);
            }
        } else {
            // Just return the current turn number if already updated
            $response['turn'] = getCurrentTurn($room_id);
        }
    } else {
        $response['turn'] = getCurrentTurn($room_id);
    }
}

echo json_encode($response);
?>
