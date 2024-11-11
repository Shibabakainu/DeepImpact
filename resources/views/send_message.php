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
    <link rel="stylesheet" href="/DeepImpact/resources/css/index.css">
    <style>
        /* 基本スタイル */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        h1 {
            color: #333;
        }

        /* フォームコンテナのスタイル */
        .message-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            position: relative;
        }

        /* 戻るボタンのスタイル */
        .back-button {
            position: absolute;
            top: -70px; /* 少し上に配置 */
            left: 50%;
            transform: translateX(-50%);
            background-color: #ff4d4d; /* 赤色 */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
            margin-bottom: 15px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <a href="frieview.php" class="back-button">戻る</a> <!-- 戻るボタン -->
        <h1><?php echo htmlspecialchars($friend_name, ENT_QUOTES, 'UTF-8'); ?> にメッセージを送る</h1>
        <form method="post">
            <textarea name="message" required></textarea>
            <button type="submit">送信</button>
        </form>
    </div>
</body>
</html>
