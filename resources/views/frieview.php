<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フレンド一覧画面</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/friend.css">
</head>
<body>
    <?php
    // フレンドリストの配列
    $friends = ['フレンド名', 'フレンド名', 'フレンド名', 'フレンド名', 'フレンド名', 'フレンド名'];
    ?>
    <div class="container">
        <div class="title">フレンド一覧</div>
        <div class="friend-list">
            <?php foreach ($friends as $friend): ?>
                <div class="friend-item"><?php echo htmlspecialchars($friend, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
        </div>
        <button class="friend-search">フレンド検索</button>
        <button class="logout">退出</button>
    </div>
</body>
</html>