<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フレンド検索</title>
    <link rel="stylesheet" href="../css/friend.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php
        include 'db_connect.php'; // データベース接続スクリプトをインクルード

        // ユーザーIDを取得
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        } else {
            echo "<div class='notice'>ユーザーIDが指定されていません。</div>";
            exit;
        }

        // データベースからユーザーの情報を取得
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Display user data
            echo "<div class='friend-search'>";
            echo "<h2>フレンド検索</h2>";
            echo "<p><b>貴方のID</b>: " . htmlspecialchars($user['email']) . "</p>";
            echo "<div class='search-box'>";
            echo "<label for='friend-id'><b>ID検索</b></label>";
            echo "<input type='text' id='friend-id' name='friend-id' placeholder='相手のIDを入力してください'>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "ユーザーが見つかりません。";
        }
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>

</html>