<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データベースからユーザー名を取得
$host_user_name = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $host_user_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        }
        $stmt->close();
    }
}

// ユーザー名が取得できない場合はエラー
if (!$host_user_name) {
    die('エラー: ユーザー名が取得できませんでした。');
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>設定画面</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/room_setting.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <form action="room_DB.php" method="POST">
            <div class="form-group">
                <label for="setting">合言葉設定</label>
                <input type="text" id="setting" name="setting" required>
            </div>
            <div class="form-group">
                <label for="room">ルーム名</label>
                <input type="text" id="room" name="room" required>
            </div>
            <div class="form-group">
                <label for="people">人数</label>
                <select id="people" name="people">
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </div>
            <button type="submit" name="create_room">作成</button>
            <button type="button" class="create" onclick="location.href='room_create.php'">戻る</button>
        </form>
    </div>

    <?php
    if (isset($_POST['create_room'])) {
        $room_name = $_POST['room'];
        $setting = $_POST['setting'];
        $max_players = $_POST['people'];

        // roomsテーブルに挿入
        $sql = "INSERT INTO rooms (room_name, host_user_name, max_players) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
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
                die("Error preparing password statement: " . $conn->error);
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