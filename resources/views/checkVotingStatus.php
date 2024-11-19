<?php

include 'db_connect.php';
include 'game_functions.php';

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Fetch all players in the room
$playersQuery = "SELECT player_position FROM room_players WHERE room_id = ?";
$stmt = $conn->prepare($playersQuery);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$playersResult = $stmt->get_result();

$players = [];
while ($row = $playersResult->fetch_assoc()) {
    $players[] = $row;
}
$stmt->close();

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

// Get the current turn for the room
$current_turn = getCurrentTurn($room_id);

if ($room_id) {
    if (isVotingComplete($room_id, $current_turn)) {
        $response['debug'] = 'Voting complete';
        // Check if the turn has already been updated
        $checkTurnQuery = "SELECT turn_updated FROM rooms WHERE room_id = ?";
        $checkTurnStmt = $conn->prepare($checkTurnQuery);
        $checkTurnStmt->bind_param("i", $room_id);
        $checkTurnStmt->execute();
        $checkTurnStmt->bind_result($turnUpdated);
        $checkTurnStmt->fetch();
        $checkTurnStmt->close();
        $response['turnUpdated'] = $turnUpdated;
    
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

            // Hide the voted cards for this turn
            hideVotedCards($room_id);

            // Fetch players for drawing new cards
            $playersQuery = "SELECT player_position FROM room_players WHERE room_id = ?";
            $playersStmt = $conn->prepare($playersQuery);
            $playersStmt->bind_param("i", $room_id);
            $playersStmt->execute();
            $result = $playersStmt->get_result();
            $players = $result->fetch_all(MYSQLI_ASSOC);
            $playersStmt->close();
            
            //draw new card for a new turn
            foreach ($players as $player) {
                drawNewCard($room_id, $player['player_position']);
            }

            // Set turn_updated to 0
            $updateTurnQuery = "UPDATE rooms SET turn_updated = 0 WHERE room_id = ?";
            $updateTurnStmt = $conn->prepare($updateTurnQuery);
            $updateTurnStmt->bind_param("i", $room_id);
            $updateTurnStmt->execute();
            $updateTurnStmt->close();
        } else {
            // Just return the current turn number if already updated
            $response['turn'] = getCurrentTurn($room_id);
        }
    } else {
        $response['turn'] = getCurrentTurn($room_id);
        $response['debug'] = 'Voting not complete';
    }
}

echo json_encode($response);
?>
