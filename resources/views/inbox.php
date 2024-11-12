<?php
session_start();
include 'db_connect.php';

// ログインしていない場合はログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 受信者がログインしているユーザーIDであるメッセージを取得
$stmt = $conn->prepare("SELECT users.name AS sender_name, messages.content, messages.sent_at 
                        FROM messages 
                        INNER JOIN users ON messages.user_id = users.id 
                        WHERE messages.recipient_id = ? 
                        ORDER BY messages.sent_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>受信メッセージ</title>
</head>
<body>
    <h1>受信メッセージ</h1>
    <?php while ($message = $result->fetch_assoc()): ?>
        <div class="message">
            <p><strong>送信者:</strong> <?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>メッセージ:</strong> <?php echo nl2br(htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8')); ?></p>
            <p><small><?php echo htmlspecialchars($message['sent_at'], ENT_QUOTES, 'UTF-8'); ?></small></p>
        </div>
        <hr>
    <?php endwhile; ?>
</body>
</html>
