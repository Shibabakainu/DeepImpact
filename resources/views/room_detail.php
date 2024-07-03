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
$people = isset($_GET['people']) ? (int)$_GET['people'] : 0;

// DBに現在のプレイヤー数を保存
$sql_update_players = "UPDATE rooms SET current_players = ? WHERE room_name = ?";
$stmt_update_players = $conn->prepare($sql_update_players);
if ($stmt_update_players) {
    $stmt_update_players->bind_param("is", $people, $room);
    $stmt_update_players->execute();
    $stmt_update_players->close();
} else {
    echo "Error updating current players: " . $conn->error;
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
                <a href="room_setting.php"><button class="back">戻る</button></a>
                <a href="game.php"><button class="create">物語を作る</button></a>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            let peopleCount = <?php echo $people; ?>;
            const playerList = document.querySelector('.player-list');

            playerList.addEventListener('click', (e) => {
                if (e.target && e.target.matches('li.player.empty')) {
                    if (peopleCount < 6) {
                        peopleCount++;
                        e.target.textContent = `プレイヤー${peopleCount}`;
                        e.target.classList.remove('empty');
                    }
                } else if (e.target && e.target.matches('li.player')) {
                    if (peopleCount > 2) {
                        e.target.textContent = '';
                        e.target.classList.add('empty');
                        peopleCount--;
                    }
                }
            });
        });
    </script>

</body>

</html>