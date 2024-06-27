<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => '無効なリクエストです。'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'ログインしていません。';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_name'])) {
    $user_id = $_SESSION['user_id'];
    $friend_name = $_POST['friend_name'];

    // ユーザー名を取得
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = $user['name'];
    $stmt->close();

    // Check if the friend request already exists
    $sql = "SELECT * FROM friends WHERE user_name = ? AND friend_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_name, $friend_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Friend request already exists
        $friend = $result->fetch_assoc();
        if ($friend['status'] == 'pending') {
            $response['message'] = 'フレンド申請はすでに保留中です。';
        } else if ($friend['status'] == 'rejected') {
            $response['message'] = 'フレンド申請は拒否されました。';
        } else {
            $response['message'] = 'このユーザーは既にフレンドです。';
        }
    } else {
        // Insert the new friend request
        $sql = "INSERT INTO friends (user_name, friend_name, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_name, $friend_name);
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'フレンド申請が送信されました。';
        } else {
            $response['message'] = 'フレンド申請の送信に失敗しました。';
        }
        $stmt->close();
    }

    $conn->close();
} else {
    $response['message'] = '無効なリクエストです。';
}

echo json_encode($response);
?>
