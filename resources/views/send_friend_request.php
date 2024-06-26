<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // セッションにユーザーIDがない場合はログインページにリダイレクト
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_name'])) {
    $user_id = $_SESSION['user_id'];
    $friend_name = $_POST['friend_name'];

    // データベース接続
    include 'db_connect.php';

    // Get the user's name
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = $user['name'];
    $stmt->close();

    // フレンドリクエストを送信するクエリ
    $sql = "INSERT INTO friends (user_name, friend_name, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_name, $friend_name);

    if ($stmt->execute()) {
        echo "<p>フレンドリクエストを送信しました。</p>";
    } else {
        echo "<p>フレンドリクエストの送信に失敗しました。</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>リクエストに失敗しました。</p>";
}
?>
<div class="container2">
    <button class="return" onclick="location.href='friend.php'">戻る</button>
</div>
