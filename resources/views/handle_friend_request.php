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

    // Disable triggers
    $conn->query("SET @DISABLE_TRIGGERS = TRUE");

    if ($action === 'accept') {
        // フレンドリクエストを承認する
        $sql = "UPDATE friends SET status = 'accepted' WHERE user_name = ? AND friend_name = ? AND status = 'pending'";
    } else {
        // フレンドリクエストを拒否する
        $sql = "DELETE FROM friends WHERE user_name = ? AND friend_name = ? AND status = 'pending'";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $request_user, $user_name);
    $stmt->execute();
    $stmt->close();

    // Enable triggers
    $conn->query("SET @DISABLE_TRIGGERS = FALSE");

    $conn->close();

    header("Location: frieview.php");
    exit;
} else {
    echo "不正なリクエストです。";
}
?>
