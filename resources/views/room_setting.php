<?php
session_start();
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>設定画面</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/room_setting.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <form action="room_DB.php" method="POST">
            <div class="form-group">
                <label for="room">ルーム名</label>
                <input type="text" id="room" name="room" required>
            </div>
            <div class="form-group">
                <label for="setting">合言葉設定</label>
                <input type="text" id="setting" name="setting" required>
            </div>
            <div class="form-group">
                <label for="people">最大プレイヤー数</label>
                <input type="number" id="people" name="people" value="6" min="1" max="6" required>
            </div>
            <button type="submit" name="create_room">作成</button>
            <button type="button" class="create" onclick="location.href='room_create.php'">戻る</button>
        </form>
    </div>

    <?php
    // Closing PHP tag moved to the end for clean HTML separation
    if (isset($_POST['create_room'])) {
        $room_name = $_POST['room'];
        $setting = $_POST['setting'];
        $max_players = 6; // デフォルトで6人

        // Getting the host user's ID instead of name
        $host_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Check if host user ID is retrieved
        if (!$host_user_id) {
            die('エラー: ユーザーIDが取得できませんでした。');
        }

        // roomsテーブルに挿入
        $sql = "INSERT INTO rooms (room_name, host_id, max_players) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("ステートメントの準備エラー: " . $conn->error);
        }
        $stmt->bind_param("sii", $room_name, $host_user_id, $max_players);

        if ($stmt->execute()) {
            $room_id = $stmt->insert_id;

            // 合言葉をハッシュ化して挿入
            $password_hash = password_hash($setting, PASSWORD_DEFAULT);

            // room_passwordsテーブルに挿入
            $sql_password = "INSERT INTO room_passwords (room_id, password_hash) VALUES (?, ?)";
            $stmt_password = $conn->prepare($sql_password);
            if (!$stmt_password) {
                die("パスワードステートメントの準備エラー: " . $conn->error);
            }
            $stmt_password->bind_param("is", $room_id, $password_hash);

            if ($stmt_password->execute()) {
                echo "ルームが作成されました。";
            } else {
                echo "エラー: " . $stmt_password->error;
            }
            $stmt_password->close();
        } else {
            echo "エラー: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
    ?>
</body>

</html>
