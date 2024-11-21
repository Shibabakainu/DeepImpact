<?php
session_start();
require_once 'db_connect.php'; // Your database connection
require_once 'game_functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate inputs
if (!isset($_POST['room_id'], $_POST['room_card_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$room_id = $_POST['room_id'];
$room_card_id = $_POST['room_card_id'];

// Check if the player is part of the room
$sql = "SELECT * FROM room_players WHERE room_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Player not in this room']);
    exit;
}

// Check if the selected card exists in the room_cards table for this room
$sql = "SELECT * FROM room_cards WHERE room_id = ? AND room_card_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $room_card_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Card not found in this room', 'room_card_id' => $room_card_id, 'room_id' => $room_id]);
    exit;
}

// Check if the player has already voted for this card
$sql = "SELECT * FROM votes WHERE room_id = ? AND player_id = ? AND room_card_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $room_id, $user_id, $room_card_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Player has already voted for this card']);
    exit;
}

// Get the current turn for the room
$current_turn = getCurrentTurn($room_id);

// Insert the vote into the votes table with the current turn
$sql = "INSERT INTO votes (room_id, player_id, room_card_id, turn) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $room_id, $user_id, $room_card_id, $current_turn);

if ($stmt->execute()) {
    // Update the room_cards table to reflect that this card has been voted for
    $sql = "UPDATE room_cards SET voted = 1 WHERE room_id = ? AND room_card_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $room_id, $room_card_id);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Vote recorded']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to record vote']);
}

$stmt->close();
$conn->close();
?>
