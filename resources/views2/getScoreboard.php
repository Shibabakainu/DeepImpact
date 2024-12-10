<?php
include 'db_connect.php';
include 'game_functions.php';

if (isset($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']);
    $scoreboardHtml = getScoreboardHtml($room_id);
    echo json_encode(['scoreboard' => $scoreboardHtml]);
}
