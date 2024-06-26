<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フレンド一覧画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff0b3; /* 背景色を黄色に設定 */
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
            position: relative;
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .friend-list {
            margin-bottom: 20px;
        }
        .friend-item {
            background-color: #d3d3d3;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .friend-item:hover {
            background-color: #c0c0c0;
        }
        .friend-search,
        .logout {
            background-color: #d3d3d3;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            width: calc(100% - 20px);
        }
        .friend-search:hover,
        .logout:hover {
            background-color: #c0c0c0;
        }
        .chat {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: #d3d3d3;
            padding: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
        .chat:hover {
            background-color: #c0c0c0;
        }
    </style>
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
    <div class="chat">チャット(未定)</div>
</body>
</html>