//room_detail.php



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

// 現在のプレイヤー数を取得
$sql_current_players = "SELECT current_players FROM rooms WHERE room_name = ?";
$stmt_current_players = $conn->prepare($sql_current_players);
if ($stmt_current_players) {
    $stmt_current_players->bind_param("s", $room);
    $stmt_current_players->execute();
    $stmt_current_players->bind_result($people);
    $stmt_current_players->fetch();
    $stmt_current_players->close();
} else {
    echo "現在のプレイヤー数の取得エラー: " . $conn->error;
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
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <h2>ルーム名: <?php echo $room; ?></h2>
            <p>合言葉: <?php echo $setting; ?></p>
            <ul class="player-list">
                <li class="host"><span class="host-label">ホスト</span> <?php echo $loggedInUser; ?></li>
                <?php
                // プレイヤーリストを動的に生成
                for ($i = 2; $i <= 6; $i++) {
                    if ($i <= $people) {
                        echo "<li class='player'>プレイヤー$i</li>";
                    } else {
                        echo "<li class='player empty'></li>";
                    }
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
                <button class="back-other-btn" id="back-exit-btn">退出</button>
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
                body: `room_id=<?php echo $room; ?>`
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