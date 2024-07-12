<?php
session_start();
include 'db_connect.php';

// 最大ルーム数の上限
$max_rooms = 10;

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
        $sql_room_players = "INSERT INTO room_players (room_id, user_id, host) VALUES (?, ?, ?)";
        $stmt_room_players = $conn->prepare($sql_room_players);
        if (!$stmt_room_players) {
            die("Error preparing room_players statement: " . $conn->error);
        }
        $host = true;
        $stmt_room_players->bind_param("iii", $room_id, $user_id, $host);

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
?>