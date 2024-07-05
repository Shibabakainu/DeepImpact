//room_setting.php



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
                <div class="form-group">
                    <label for="room">ルーム名</label>
                    <input type="text" id="room" name="room" required>
                </div>
                <label for="setting">合言葉設定</label>
                <input type="text" id="setting" name="setting" required>
            </div>
            <button type="submit" name="create_room">作成</button>
            <button type="button" class="create" onclick="location.href='room_create.php'">戻る</button>
        </form>
    </div>

    <?php
    if (isset($_POST['create_room'])) {
        $room_name = $_POST['room'];
        $setting = $_POST['setting'];
        $max_players = 6; // デフォルトで6人

        // roomsテーブルに挿入
        $sql = "INSERT INTO rooms (room_name, host_user_name, max_players) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("ステートメントの準備エラー: " . $conn->error);
        }
        $stmt->bind_param("ssi", $room_name, $host_user_name, $max_players);

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
        } else {
            echo "エラー: " . $stmt->error;
        }

        $stmt->close();
    }
    $conn->close();
    ?>
</body>

</html>