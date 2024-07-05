//room_DB.php



<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データベースからユーザー名を取得
$host_user_name = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $host_user_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        }
        $stmt->close();
    }
}

// ユーザー名が取得できない場合はエラー
if (!$host_user_name) {
    die('エラー: ユーザー名が取得できませんでした。');
}

// フォームデータを取得
$room_name = isset($_POST['room']) ? htmlspecialchars($_POST['room'], ENT_QUOTES, 'UTF-8') : '';
$setting = isset($_POST['setting']) ? htmlspecialchars($_POST['setting'], ENT_QUOTES, 'UTF-8') : '';
$max_players = isset($_POST['people']) ? (int)$_POST['people'] : 0;

// roomsテーブルに挿入
$sql = "INSERT INTO rooms (room_name, host_user_name, max_players) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ssi", $room_name, $host_user_name, $max_players);

if ($stmt->execute()) {
    $room_id = $stmt->insert_id;

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
        // ルーム詳細ページにリダイレクト
        header("Location: room_detail.php?room=$room_name&setting=$setting&people=$max_players");
        exit;
    } else {
        echo "エラー: " . $stmt_password->error;
    }

    $stmt_password->close();

    // room_playersテーブルに挿入
    $sql_room_players = "INSERT INTO room_players (room_id, user_id) VALUES (?, ?)";
    $stmt_room_players = $conn->prepare($sql_room_players);
    if (!$stmt_room_players) {
        die("Error preparing room_players statement: " . $conn->error);
    }
    $stmt_room_players->bind_param("ii", $room_id, $user_id);

    if ($stmt_room_players->execute()) {
        // Success: User participation recorded
    } else {
        echo "エラー: " . $stmt_room_players->error;
    }

    $stmt_room_players->close();
} else {
    echo "エラー: " . $stmt->error;
}

$stmt->close();
$conn->close();
