<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データベースからユーザー名を取得
$loggedInUser = 'ゲスト';
if ($user_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $loggedInUser = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        }
        $stmt->close();
    }
}

// クエリパラメータからデータを取得
$setting = isset($_GET['setting']) ? htmlspecialchars($_GET['setting'], ENT_QUOTES, 'UTF-8') : '';
$room = isset($_GET['room']) ? htmlspecialchars($_GET['room'], ENT_QUOTES, 'UTF-8') : '';

// ルーム情報を取得
$sql_room_info = "SELECT room_id, host_id, current_players FROM rooms WHERE room_name = ?";
$stmt_room_info = $conn->prepare($sql_room_info);
if ($stmt_room_info) {
    $stmt_room_info->bind_param("s", $room);
    $stmt_room_info->execute();
    $stmt_room_info->bind_result($room_id, $host_id, $people);
    $stmt_room_info->fetch();
    $stmt_room_info->close();
} else {
    echo "ルーム情報の取得エラー: " . $conn->error;
}

// ホスト名を取得
$host_name = 'ホスト';
if ($host_id) {
    $stmt_host = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt_host) {
        $stmt_host->bind_param("i", $host_id);
        $stmt_host->execute();
        $stmt_host->bind_result($host_name_result);
        if ($stmt_host->fetch()) {
            $host_name = htmlspecialchars($host_name_result, ENT_QUOTES, 'UTF-8');
        }
        $stmt_host->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/room_detail.css">
</head>

<body>
    <main>
        <div class="container">
            <h2>ルーム名: <?php echo $room; ?></h2>
            <p>合言葉: <?php echo $setting; ?></p>
            <ul class="player-list">
                <li class="host"><span class="host-label">ホスト</span> <?php echo $host_name; ?></li>
                <?php
                // プレイヤーリストを動的に生成
                $sql_players = "SELECT u.name FROM users u JOIN room_players rp ON u.id = rp.user_id WHERE rp.room_id = ? AND u.id != ? ORDER BY rp.joined_at";
                $stmt_players = $conn->prepare($sql_players);
                if ($stmt_players) {
                    $stmt_players->bind_param("ii", $room_id, $host_id);
                    $stmt_players->execute();
                    $stmt_players->bind_result($player_name);

                    while ($stmt_players->fetch()) {
                        echo "<li class='player'>" . htmlspecialchars($player_name, ENT_QUOTES, 'UTF-8') . "</li>";
                    }

                    $stmt_players->close();
                } else {
                    echo "プレイヤーリストの取得エラー: " . $conn->error;
                }

                // 空のプレイヤースロット
                for ($i = $people + 1; $i <= 6; $i++) {
                    echo "<li class='player empty'></li>";
                }
                ?>
            </ul>
            <div class="buttons">
                <button class="back">戻る</button>
                <a href="game.php"><button class="create">物語を作る</button></a>
            </div>
        </div>

        <div id="back-popup-wrapper">
            <div class="back_button">
                <p class="back-text">本当に退出しますか？</p>
                <button class="back-popup-btn" id="back-popup-close">キャンセル</button>
                <form action="delete_room.php" method="POST">
                    <button type="submit" class="back-other-btn" id="back-exit-btn">退出</button>
                </form>
            </div>
        </div>

    </main>
    <script>
        //ポップアップ表示
        const back_Btn = document.querySelector('.back');
        const back_Popup_Wrapper = document.getElementById('back-popup-wrapper');
        const back_Popup_Close = document.getElementById('back-popup-close');
        const back_exitBtn = document.getElementById('back-exit-btn');

        // 「戻る」ボタンをクリックしたときにポップアップを表示させる
        back_Btn.addEventListener('click', () => {
            back_Popup_Wrapper.style.display = 'flex';
        });

        // ポップアップの外側または「閉じる」ボタンをクリックしたときポップアップを閉じる
        back_Popup_Wrapper.addEventListener('click', e => {
            if (e.target.id === back_Popup_Close.id) {
                back_Popup_Wrapper.style.display = 'none';
            }
        });

        // 「退出」ボタンをクリックしたときに指定されたURLに移動する
        back_exitBtn.addEventListener('click', () => {
            // PHPのスクリプトにPOSTリクエストを送信する
            fetch('delete_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `room_id=<?php echo $room_id; ?>`
            }).then(response => {
                // レスポンスを処理する
                if (response.ok) {
                    // 成功した場合の処理（例：設定画面にリダイレクトするなど）
                    window.location.href = "room_setting.php";
                } else {
                    // エラーが発生した場合の処理
                    console.error('削除リクエストでエラーが発生しました。');
                }
            }).catch(error => {
                console.error('削除リクエスト中にエラーが発生しました。', error);
            });
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            let peopleCount = <?php echo $people; ?>;
            const playerList = document.querySelector('.player-list');

            playerList.addEventListener('click', (e) => {
                if (e.target && e.target.matches('li.player.empty')) {
                    if (peopleCount < 6) {
                        peopleCount++;
                        e.target.textContent = `プレイヤー${peopleCount}`;
                        e.target.classList.remove('empty');

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                } else if (e.target && e.target.matches('li.player')) {
                    if (peopleCount > 2) {
                        e.target.textContent = '';
                        e.target.classList.add('empty');
                        peopleCount--;

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                }
            });
        });
    </script>

</body>

</html>