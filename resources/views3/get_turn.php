<?php
include 'db_connect.php';

$room_id = $_GET['room_id'];

$response = ["success" => false, "turn_number" => null];

$query = "SELECT turn_number FROM rooms WHERE room_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response["success"] = true;
    $response["turn_number"] = $row["turn_number"];
}

echo json_encode($response);
?>
