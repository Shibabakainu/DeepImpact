<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['friend_name'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_name = $_GET['friend_name'];

// 送信先のフレンドのユーザーIDを取得
$stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
$stmt->bind_param("s", $friend_name);
$stmt->execute();
$result = $stmt->get_result();
$friend = $result->fetch_assoc();

if (!$friend) {
    echo "フレンドが見つかりません。";
    exit;
}

$friend_id = $friend['id'];
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $friend_id, $content);
    if ($stmt->execute()) {
        echo "メッセージを送信しました。";
    } else {
        echo "メッセージの送信に失敗しました。";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メッセージを送る</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($friend_name, ENT_QUOTES, 'UTF-8'); ?> にメッセージを送る</h1>
    <form method="post">
        <textarea name="message" required></textarea>
        <button type="submit">送信</button>
    </form>
</body>
</html>
