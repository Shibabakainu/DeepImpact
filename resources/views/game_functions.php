<?php
include 'db_connect.php';

//投票が完了かどうかを確認
function isVotingComplete($room_id)
{
    global $conn;
    if (!$conn || $conn->connect_errno) {
        die("Database connection closed or unavailable");
    }    

    // Count distinct player_ids to see if every player has voted in the room
    $query = "SELECT COUNT(DISTINCT player_id) FROM votes WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->bind_result($distinctVoters);
    $stmt->fetch();
    $stmt->close();

    // Query to get the total number of players in the room
    $playerQuery = "SELECT COUNT(*) FROM room_players WHERE room_id = ?";
    $playerStmt = $conn->prepare($playerQuery);
    $playerStmt->bind_param("i", $room_id);
    $playerStmt->execute();
    $playerStmt->bind_result($totalPlayers);
    $playerStmt->fetch();
    $playerStmt->close();

    // Check if voting is complete
    return $distinctVoters == $totalPlayers;
}

function updateScore($room_id)
{
    global $conn;

    // Get the current turn number
    $turnQuery = "SELECT turn_number FROM rooms WHERE room_id = ?";
    $turnStmt = $conn->prepare($turnQuery);
    $turnStmt->bind_param("i", $room_id);
    $turnStmt->execute();
    $turnResult = $turnStmt->get_result();
    $turnRow = $turnResult->fetch_assoc();
    $currentTurn = $turnRow['turn_number'];
    $turnStmt->close();

    // Identify the player with the most voted card
    $query = "
        SELECT rc.player_position, COUNT(v.room_card_id) AS vote_count
        FROM room_cards rc
        INNER JOIN votes v ON rc.room_card_id = v.room_card_id
        WHERE rc.room_id = ? AND rc.selected = 1
        GROUP BY rc.player_position
        ORDER BY vote_count DESC
        LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $player_position = $row['player_position'];

        // Update score only if this turn hasn't been scored yet for this player
        $scoreQuery = "
            UPDATE room_players 
            SET score = score + 1, last_scored_turn = ?
            WHERE room_id = ? AND player_position = ? AND last_scored_turn < ?";
        $scoreStmt = $conn->prepare($scoreQuery);
        $scoreStmt->bind_param("iiii", $currentTurn, $room_id, $player_position, $currentTurn);
        $scoreStmt->execute();
        $scoreStmt->close();
    }

    $stmt->close();
}

// Function to fetch and display the scoreboard
function getScoreboardHtml($room_id)
{
    global $conn;

    // Get player names and their scores for the room, sorted by score in descending order
    $query = "
        SELECT u.name AS player_name, rp.score 
        FROM room_players rp
        INNER JOIN users u ON rp.user_id = u.id
        WHERE rp.room_id = ? 
        ORDER BY rp.score DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Generate scoreboard HTML
    $scoreboardHtml = '<div class="scoreboard">';
    $scoreboardHtml .= '<p>スコアボード</p>';
    while ($row = $result->fetch_assoc()) {
        $player_name = htmlspecialchars($row['player_name']);
        $score = $row['score'];
        $scoreboardHtml .= "<p> $player_name : $score</p>";
    }
    $scoreboardHtml .= '</div>';

    $stmt->close();
    return $scoreboardHtml;
}

//ターンを増加する
function incrementTurn($room_id) {
    global $conn;

    // Check the current turn number
    $turnQuery = "SELECT turn_number FROM rooms WHERE room_id = ?";
    $turnStmt = $conn->prepare($turnQuery);
    $turnStmt->bind_param("i", $room_id);
    $turnStmt->execute();
    $turnResult = $turnStmt->get_result();
    $turnRow = $turnResult->fetch_assoc();
    $currentTurn = $turnRow['turn_number'];
    $turnStmt->close();

    // If the current turn is less than 7, increment the turn
    if ($currentTurn < 7) {
        $query = "UPDATE rooms SET turn_number = turn_number + 1, turn_updated = 0 WHERE room_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // If the current turn is 7, set the game status to "finished"
        $query = "UPDATE rooms SET status = 'finished' WHERE room_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->close();
    }
}

//現在のターンを取得する
function getCurrentTurn($room_id) {
    global $conn;

    $query = "SELECT turn_number FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->bind_result($turn_number);
    $stmt->fetch();
    $stmt->close();

    return $turn_number;
}

//新しいターンにカードを1枚ドローする
function drawNewCard($room_id, $player_position) {
    global $conn;
    
    // Query to select a random card that hasn't been selected by this player in this room
    $query = "SELECT card_id FROM cards 
              WHERE card_id NOT IN (
                  SELECT card_id FROM room_cards 
                  WHERE room_id = ? AND player_position = ?
              ) 
              ORDER BY RAND() LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $room_id, $player_position);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $card_id = $row['card_id'];
        
        // Insert this card into room_cards table for the player
        $insertQuery = "INSERT INTO room_cards (room_id, card_id, player_position, selected) VALUES (?, ?, ?, 0)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iii", $room_id, $card_id, $player_position);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $stmt->close();
}


?>
