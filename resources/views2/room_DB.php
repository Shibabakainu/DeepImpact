<?php
session_start();
include 'db_connect.php';

// 最大ルーム数の上限

$max_rooms = 300;

// 現在のルーム数をカウント
$sql_count_rooms = "SELECT COUNT(*) as room_count FROM rooms";
$result = $conn->query($sql_count_rooms);
if ($result) {
    $row = $result->fetch_assoc();
    $current_rooms = $row['room_count'];
    if ($current_rooms >= $max_rooms) {
        $_SESSION['error_message'] = 'ルーム数の上限に達しています';
        header('Location: room_setting.php');
        exit;
    }
} else {
    die('エラー: 現在のルーム数を取得できませんでした。');
}

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データベースからユーザー名を取得
$host_id = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($id);
        if ($stmt->fetch()) {
            $host_id = $id;
        }
        $stmt->close();
    }
}

// ユーザーIDが取得できない場合はエラー
if (!$host_id) {
    die('エラー: ユーザーIDが取得できませんでした。');
}

// フォームデータを取得
$room_name = isset($_POST['room']) ? htmlspecialchars($_POST['room'], ENT_QUOTES, 'UTF-8') : '';
$setting = isset($_POST['setting']) ? htmlspecialchars($_POST['setting'], ENT_QUOTES, 'UTF-8') : '';
$max_players = isset($_POST['people']) ? (int)$_POST['people'] : 6; // Default to 6 if not provided

// roomsテーブルに挿入
$sql = "INSERT INTO rooms (room_name, host_id, current_players, max_players) VALUES (?, ?, 1, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("sii", $room_name, $host_id, $max_players);

if ($stmt->execute()) {
    echo "Room inserted successfully with ID: " . $stmt->insert_id . "<br>";
    $room_id = $stmt->insert_id;
    $_SESSION['room_id'] = $room_id; // room_idをセッションに保存

    // 合言葉をハッシュ化して挿入
    $password_hash = password_hash($setting, PASSWORD_DEFAULT);

    // room_passwordsテーブルに挿入
    $sql_password = "INSERT INTO room_passwords (room_id, password_hash) VALUES (?, ?)";
    $stmt_password = $conn->prepare($sql_password);
    if (!$stmt_password) {
        die("Error preparing password statement: " . $conn->error);
    }
    $stmt_password->bind_param("is", $room_id, $password_hash);

    if ($stmt_password->execute()) {
        // room_playersテーブルにホストプレイヤーを挿入

        // 1. Check for the next available player position in the room
        $sql_position = "SELECT COALESCE(MAX(player_position), 0) as max_position FROM room_players WHERE room_id = ?";
        $stmt_position = $conn->prepare($sql_position);
        if (!$stmt_position) {
            die("Error preparing position statement: " . $conn->error);
        }
        $stmt_position->bind_param("i", $room_id);
        $stmt_position->execute();
        $result_position = $stmt_position->get_result();
        $row_position = $result_position->fetch_assoc();

        $host_position = $row_position['max_position'] + 1; // Increment to the next available position

        // Debugging to confirm the player position
        echo "Next available player position: " . $host_position . "<br>";

        $stmt_position->close();

        // 2. Check if the same room_id and player_position already exists
        $sql_check_duplicate = "SELECT COUNT(*) as count FROM room_players WHERE room_id = ? AND player_position = ?";
        $stmt_check_duplicate = $conn->prepare($sql_check_duplicate);
        $stmt_check_duplicate->bind_param("ii", $room_id, $host_position);
        $stmt_check_duplicate->execute();
        $result_duplicate = $stmt_check_duplicate->get_result();
        $row_duplicate = $result_duplicate->fetch_assoc();

        if ($row_duplicate['count'] > 0) {
            die("Error: Duplicate entry for room_id $room_id and player_position $host_position.");
        }

        // 3. Insert host player with the next available position
        $sql_room_players = "INSERT INTO room_players (room_id, user_id, host, player_position) VALUES (?, ?, ?, ?)";
        $stmt_room_players = $conn->prepare($sql_room_players);
        if (!$stmt_room_players) {
            die("Error preparing room_players statement: " . $conn->error);
        }
        $host = true;
        $stmt_room_players->bind_param("iiii", $room_id, $user_id, $host, $host_position);
        $_SESSION['player_position'] = $host_position;

        if ($stmt_room_players->execute()) {
            // ルーム詳細ページにリダイレクト
            header("Location: room_detail.php?room=$room_name&setting=$setting");
            exit;
        } else {
            echo "エラー: " . $stmt_room_players->error;
        }

        $stmt_room_players->close();
    } else {
        echo "エラー: " . $stmt_password->error;
    }

    $stmt_password->close();
} else {
    echo "エラー: " . $stmt->error;
}

$stmt->close();
$conn->close();
