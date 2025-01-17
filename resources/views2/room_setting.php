<?php
session_start();
include 'db_connect.php';

// エラーメッセージを取得してセッションから削除
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>設定画面</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/room_setting.css">
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>

</head>

<body>
    <audio autoplay loop>
        <source src="/DeepImpact/bgm/sekiranun.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio>

    <script>
        window.onload = function() {
            var bgm = document.getElementById('bgm');

            // 前回の再生位置があれば取得して、そこから再生する
            var savedTime = localStorage.getItem('bgmTime');
            if (savedTime) {
                bgm.currentTime = parseFloat(savedTime); // 保存された再生位置に移動
            }


            // BGMの自動再生
            bgm.play();

            // BGMの再生位置を定期的に保存
            setInterval(function() {
                localStorage.setItem('bgmTime', bgm.currentTime); // 再生位置を保存
            }, 1000); // 1秒ごとに再生位置を保存

            // ページが閉じる/リロードされるときに再生位置を保存
            window.addEventListener('beforeunload', function() {
                localStorage.setItem('bgmTime', bgm.currentTime);
            });
        };
    </script>
    <?php include 'header.php'; ?>
    <div class="container">
        <form id="createRoomForm">
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
            <button type="button" class="return" onclick="location.href='index.php'">戻る</button>
        </form>
    </div>

    <script>
        //sconst socket = io('http://192.168.3.79:8080');        
        const socket = io('http://storyteller.help');

        const userId = '<?php echo json_encode($_SESSION['user_id']) ?>';
        const userName = '<?php echo json_encode($_SESSION['user_name']) ?>';
        socket.on('connect', () => {
            console.log(socket.id);
        });
        socket.on('connect_error', (error) => {
            console.log(error);
        });
        document.getElementById('createRoomForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const roomName = document.getElementById('room').value;
            const setting = document.getElementById('setting').value;
            const maxPlayers = parseInt(document.getElementById('people').value, 10);
            const hostUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

            socket.emit('createRoom', {

                roomName,
                setting,
                maxPlayers,
                hostUserId
            });
        });

        socket.on('roomCreated', (data) => {
            const {
                roomId
            } = data;
            const password = document.getElementById('setting').value;
            const hostId = <?php echo json_encode($_SESSION['user_id']); ?>;

            socket.emit('joinRoom', {
                roomId,
                userId: userId,
                userName: userName,
                password,
                hostId,
            });
            window.location.href = `room_detail.php?room=${roomId}`;
        });

        socket.on('roomCreationError', (message) => {
            alert(message);
        });
        socket.on('joinRoomError', (data) => {
            alert(data.message);
        })
    </script>

    <?php

    if (!empty($error_message)) {
        echo "<script>alert('$error_message');</script>";
    }


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