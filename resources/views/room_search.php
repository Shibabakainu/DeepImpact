<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/resources/css/room_search.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
        <form action="http://どっかのURL/search" method="get">
            <label for="movie">合言葉</label>
            <input type="search" id="movie" name="q" />
            <button type="submit">🔍</button>
            </form>
            <div class="buttons">
                <button class="create" onclick="location.href='index.php'">戻る</button>
            </div>
        </div>
    </main>
</body>

</html>
