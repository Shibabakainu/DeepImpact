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
    'showDrawButton' => true,
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
            // Update the score and increment the turn
            updateScore($room_id);
            incrementTurn($room_id);

            // リセット: 前のターンで選択したカード制限を解除
            resetCardSelection($room_id);

            // Set turn_updated to 1 to mark that updates are done
            $updateTurnQuery = "UPDATE rooms SET turn_updated = 1 WHERE room_id = ?";
            $updateTurnStmt = $conn->prepare($updateTurnQuery);
            $updateTurnStmt->bind_param("i", $room_id);
            $updateTurnStmt->execute();
            $updateTurnStmt->close();

            // Reset the `drew` flag for all players in the room
            $resetDrewQuery = "UPDATE room_players SET drew = 0 WHERE room_id = ?";
            $resetDrewStmt = $conn->prepare($resetDrewQuery);
            $resetDrewStmt->bind_param("i", $room_id);
            $resetDrewStmt->execute();
            $resetDrewStmt->close();

            $response['votingComplete'] = true;
            $response['turn'] = getCurrentTurn($room_id);

            // Capture scoreboard output
            ob_start();
            getScoreboardHtml($room_id);
            $response['scoreboard'] = ob_get_clean();

            // Hide the voted cards for this turn
            hideVotedCards($room_id);

            // Set turn_updated to 0 after client updates
            $updateTurnQuery = "UPDATE rooms SET turn_updated = 0 WHERE room_id = ?";
            $updateTurnStmt = $conn->prepare($updateTurnQuery);
            $updateTurnStmt->bind_param("i", $room_id);
            $updateTurnStmt->execute();
            $updateTurnStmt->close();
        } else {
            // If already updated, return the current turn
            $response['turn'] = $current_turn;
        }
    } else {
        $response['debug'] = 'Voting not complete';
    }

    // Check if the player can draw cards (based on the `drew` flag)
    $player_position = isset($_SESSION['player_position']) ? intval($_SESSION['player_position']) : null;
    if ($player_position) {
        $checkDrewQuery = "SELECT drew FROM room_players WHERE room_id = ? AND player_position = ?";
        $checkDrewStmt = $conn->prepare($checkDrewQuery);
        $checkDrewStmt->bind_param("ii", $room_id, $player_position);
        $checkDrewStmt->execute();
        $checkDrewStmt->bind_result($drew);
        $checkDrewStmt->fetch();
        $checkDrewStmt->close();

        $response['showDrawButton'] = ($drew == 0);
    } else {
        $response['showDrawButton'] = false; // Default to false if player position is not found
    }
}

echo json_encode($response);


/**
 * Reset the card selection for all players in the specified room.
 * This function clears the "selected" flag in the room_cards table for a new turn.
 *
 * @param int $room_id The ID of the room.
 */
function resetCardSelection($room_id)
{
    global $conn;

    $resetQuery = "UPDATE room_cards SET selected = 0 WHERE room_id = ?";
    $resetStmt = $conn->prepare($resetQuery);
    $resetStmt->bind_param("i", $room_id);

    if (!$resetStmt->execute()) {
        error_log("Failed to reset card selections for room_id $room_id: " . $resetStmt->error);
    }

    $resetStmt->close();
}
