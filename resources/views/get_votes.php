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
    LEFT JOIN votes v ON rc.room_card_id = v.card_id
    LEFT JOIN users u ON v.player_id = u.id
    WHERE rc.room_id = ? AND rc.selected = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[$row['room_card_id']]['details'] = [
        'Card_name' => $row['Card_name'],
        'Image_path' => $row['Image_path']
    ];

    // Add users who voted for this card, along with their profile image
    if (!empty($row['name'])) {
        $cards[$row['room_card_id']]['voters'][] = [
            'name' => $row['name'],
            'profile_image' => $row['profile_image'] ? $row['profile_image'] : 'default_profile.png'  // Default image if no profile image exists
        ];
    }
}

$stmt->close();
$conn->close();

// Display the cards and the users who voted for them with profile images
foreach ($cards as $room_card_id => $card) {
    echo '<div class="selected-card" data-card-id="' . $room_card_id . '">';
    echo '<img src="../../images/' . $card['details']['Image_path'] . '" alt="' . htmlspecialchars($card['details']['Card_name'], ENT_QUOTES) . '">';
    
    // Show icons for each voter
    if (!empty($card['voters'])) {
        echo '<div class="voters">';
        foreach ($card['voters'] as $voter) {
            // Display the profile image (or a default if not available)
            $profileImage = htmlspecialchars($voter['profile_image'], ENT_QUOTES);
            echo '<img src="/DeepImpact/resources/views/login/profileicon/' . $profileImage . '" alt="' . htmlspecialchars($voter['username'], ENT_QUOTES) . '" title="' . htmlspecialchars($voter['username'], ENT_QUOTES) . '" class="voter-icon">';
        }
        echo '</div>';
    }

    echo '</div>';
}
?>
