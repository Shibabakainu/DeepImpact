<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['friend_name'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_name = $_GET['friend_name'];

// フレンドのユーザーIDを取得
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

    // メッセージを送信
    $stmt = $conn->prepare("INSERT INTO messages (user_id, recipient_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $friend_id, $content);
    if ($stmt->execute()) {
        $_SESSION['message_status'] = "メッセージを送信しました。"; // 成功メッセージ
    } else {
        $_SESSION['message_status'] = "メッセージの送信に失敗しました。"; // 失敗メッセージ
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
    <link rel="stylesheet" href="/DeepImpact/resources/css/send_message.css">
    <style>
        .message-container {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .message-status {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px;
            background-color: #e6ffe6;
            color: #4CAF50;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            display: none; /* 初期状態では非表示 */
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['message_status'])): ?>
        <div id="message-status" class="message-status">
            <?php echo htmlspecialchars($_SESSION['message_status'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['message_status']); ?>
    <?php endif; ?>

    <div class="message-container">
        <a href="frieview.php" class="back-button">戻る</a>
        <h1><?php echo htmlspecialchars($friend_name, ENT_QUOTES, 'UTF-8'); ?> にメッセージを送る</h1>
        <form method="post">
            <textarea name="message" required></textarea>
            <button type="submit">送信</button>
        </form>
    </div>

    <script>
        // ページ読み込み時にメッセージを表示し、5秒後に非表示
        window.addEventListener("load", function() {
            var messageStatus = document.getElementById("message-status");
            if (messageStatus) {
                messageStatus.style.display = "block"; // 表示
                setTimeout(function() {
                    messageStatus.style.display = "none"; // 5秒後に非表示
                }, 5000);
            }
        });
    </script>
</body>
</html>
