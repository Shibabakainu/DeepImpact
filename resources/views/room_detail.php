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
            <?php
            // フォームから送信されたデータを取得
            $setting = isset($_POST['setting']) ? htmlspecialchars($_POST['setting'], ENT_QUOTES, 'UTF-8') : '';
            $room = isset($_POST['room']) ? htmlspecialchars($_POST['room'], ENT_QUOTES, 'UTF-8') : '';
            $people = isset($_POST['people']) ? (int)$_POST['people'] : 0;
            ?>

            <h2>ルーム名: <?php echo $room; ?></h2>
            <p>合言葉: <?php echo $setting; ?></p>
            <ul class="player-list">
                <li class="host"><span class="host-label">ホスト</span> プレイヤー1</li>
                <?php
                // プレイヤーリストを動的に生成
                for ($i = 2; $i <= $people; $i++) {
                    echo "<li>プレイヤー$i</li>";
                }
                ?>
            </ul>
            <div class="buttons">
                <a href="room_setting.php"><button class="back">戻る</button></a>
                <a href="game.php"><button class="create">物語を作る</button></a>
            </div>
        </div>
    </main>
</body>
</html>
