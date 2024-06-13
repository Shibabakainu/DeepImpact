<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Teller</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="buttons">
            <button onclick="window.location.href='create_room.php'">ルーム作成</button>
            <button onclick="window.location.href='search_room.php'">ルーム検索</button>
            <button onclick="window.location.href='pachinko.php'">パチンコ</button>
            <button onclick="window.location.href='rules.php'">ルール</button>
        </div>
        <p class="footer">https://tenkir.fly.dev/rooms/9b3ad37d-24d1-482c-a40d-7a8935ab98c6</p>
    </div>
</body>

</html>