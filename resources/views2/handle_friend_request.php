<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // セッションにユーザーIDがない場合はログインページにリダイレクト
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_user']) && isset($_POST['action'])) {
    // データベース接続
    include 'db_connect.php';

    $request_user = $_POST['request_user'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];

    // ユーザー名を取得
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = $user['name'];
    $stmt->close();

    if ($action === 'accept') {
        // フレンドリクエストを承認する
        $sql = "UPDATE friends SET status = 'accepted' WHERE user_name = ? AND friend_name = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $request_user, $user_name);
        $stmt->execute();
        $stmt->close();

        // Reciprocal update to add the reverse relationship
        // Check if the reverse relationship already exists
        $sql = "SELECT * FROM friends WHERE user_name = ? AND friend_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_name, $request_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // If the reverse relationship doesn't exist, insert it
            $sql = "INSERT INTO friends (user_name, friend_name, status) VALUES (?, ?, 'accepted')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_name, $request_user);
            $stmt->execute();
            $stmt->close();
        } else {
            // If the reverse relationship exists, update its status
            $sql = "UPDATE friends SET status = 'accepted' WHERE user_name = ? AND friend_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_name, $request_user);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // フレンドリクエストを拒否する
        $sql = "DELETE FROM friends WHERE user_name = ? AND friend_name = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $request_user, $user_name);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    header("Location: frieview.php");
    exit;
} else {
    echo "不正なリクエストです。";
}
?>
