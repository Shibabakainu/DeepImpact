<?php
include 'db_connect.php';

$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

if (!$room_id) {
    die("ルームIDが指定されていません。");
}

// Query to get selected cards and their voters with profile images
$sql = "
    SELECT rc.room_card_id, c.Card_name, c.Image_path, u.name, u.id AS user_id, u.profile_image
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    LEFT JOIN votes v ON rc.room_card_id = v.room_card_id
    LEFT JOIN users u ON v.player_id = u.id
    WHERE rc.room_id = ? AND rc.selected = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    // Add card details and initialize voters array
    if (!isset($cards[$row['room_card_id']])) {
        $cards[$row['room_card_id']] = [
            'details' => [
                'Card_name' => $row['Card_name'],
                'Image_path' => $row['Image_path']
            ],
            'voters' => [] // Initialize voters array
        ];
    }

    // Add users who voted for this card, along with their profile image
    if (!empty($row['name'])) {
        $cards[$row['room_card_id']]['voters'][] = [
            'name' => $row['name'],
            'profile_image' => $row['profile_image'] ? $row['profile_image'] : 'default_profile.png'  // Default image if no profile image exists
        ];
    }
}

// Close statement and connection
$stmt->close();
$conn->close();

// Display the cards and the users who voted for them with profile images
foreach ($cards as $room_card_id => $card) {
    echo '<div class="selected-card" data-room-card-id="' . $room_card_id . '">';
    echo '<img src="../../images/'  . htmlspecialchars($card['details']['Image_path'], ENT_QUOTES) . '" alt="' . htmlspecialchars($card['details']['Card_name'], ENT_QUOTES) . '">';

    // Show icons for each voter
    if (!empty($card['voters'])) {
        echo '<div class="voters">';

        // Display voter icons
        foreach ($card['voters'] as $voter) {
            $profileImage = htmlspecialchars($voter['profile_image'], ENT_QUOTES);
            echo '<div class="voter-info">';
            echo '<img src="/DeepImpact/resources/views/login/profileicon/' . $profileImage . '" style="width:50px;height:auto;"  alt="' . htmlspecialchars($voter['name'], ENT_QUOTES) . '" title="' . htmlspecialchars($voter['name'], ENT_QUOTES) . '" class="voter-icon">';
            echo '<div class="voter-name">' . htmlspecialchars($voter['name'], ENT_QUOTES) . '</div>'; // Display voter name
            echo '</div>'; // Close voter-info
        }
        
        echo '</div>'; // Close voters
    }

    echo '</div>'; // Close selected-card
}
?>
