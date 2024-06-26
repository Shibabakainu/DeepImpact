<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/friend_search.css">
</head>
<body>

<?php
session_start();
include 'header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // セッションにユーザーIDがない場合はログインページにリダイレクト
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_id'])) {
    $friend_id = $_POST['friend_id'];

    // データベース接続
    include 'db_connect.php';

    // フレンドを検索するクエリ
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $friend_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // フレンドが見つかった場合の処理
        $friend = $result->fetch_assoc();
        echo "<div class='container'>";
        echo "<h2>フレンド情報</h2>";
        echo "<p><b>フレンドのID</b>: " . htmlspecialchars($friend['email']) . "</p>";
        // ここにフレンドのその他の情報を表示する処理を追加できます
        echo "</div>";
    } else {
        // フレンドが見つからなかった場合の処理
        echo "<div class='container'>";
        echo "<p>指定されたIDのユーザーは見つかりませんでした。</p>";
        echo "</div>";
    }

    $stmt->close();
    $conn->close();
} else {
    // POST リクエストでない場合や friend_id がセットされていない場合のエラーハンドリング
    echo "<div class='container'>";
    echo "<p>検索に失敗しました。</p>";
    echo "</div>";
}
?>
</body>
            <div class="ccontainer">
                <button class="create" onclick="location.href='friend.php'">戻る</button>
            </div>
</html>
