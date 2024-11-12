<?php

include 'db_connect.php';
include 'game_functions.php';

header('Content-Type: application/json');

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$response = [
    'votingComplete' => false,
    'turn' => null,
    'scoreboard' => '',
];

if ($room_id) {
    if (isVotingComplete($room_id)) {
        // If voting is complete, update the score, increment the turn, and get the new turn number
        updateScore($room_id);
        incrementTurn($room_id);

        // Set votingComplete to true to signal the front end
        $response['votingComplete'] = true;

        // Fetch the current turn number
        $response['turn'] = getCurrentTurn($room_id);

        // Capture the scoreboard output as a string
        ob_start();
        getScoreboard($room_id);
        $response['scoreboard'] = ob_get_clean();
    } else {
        // Voting is not complete; just return the current turn
        $response['turn'] = getCurrentTurn($room_id);
    }
}

echo json_encode($response);
?>
