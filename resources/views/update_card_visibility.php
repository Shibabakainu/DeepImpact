
<?php
include 'db_connect.php';

$card_id = $_GET['card_id'];
$visibility = $_GET['visibility'];

$stmt = $conn->prepare("UPDATE Card SET IsVisible = ? WHERE Card_id = ?");
$stmt->bind_param("ii", $visibility, $card_id);

$response = array('success' => false);
if ($stmt->execute()) {
    $response['success'] = true;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
