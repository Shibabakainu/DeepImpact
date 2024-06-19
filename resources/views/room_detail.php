<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="../css/room_detail.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <ul class="player-list">
                <li class="host"><span class="host-label">ホスト</span> プレイヤー1</li>
                <li>プレイヤー2</li>
                <li>プレイヤー3</li>
                <li>プレイヤー4</li>
                <li>プレイヤー5</li>
                <li>プレイヤー6</li>
            </ul>
            <div class="buttons">
                <a href="room_create.php"><button class="back">戻る</button></a>
                <a href="game.php"><button class="create">物語を作る</button></a>
            </div>
        </div>
    </main>
</body>

</html>