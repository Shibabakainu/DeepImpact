<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Teller</title>
    <link rel="stylesheet" href="../css/index.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <img src="../../images/sttera.png" alt="Story Teller" class="header-image">
        <div class="buttons">
            <button onclick="window.location.href='room_create.php'">ルーム作成</button>
            <button onclick="window.location.href='search_room.php'">ルーム検索</button>
            <button onclick="window.location.href='pachinko.php'">パチンコ</button>
            <button id="click-btn">ルール</button>
            <div id="popup-wrapper">
                <div id="popup-inside">
                    <div id="close">X</div>
                    <div class="text">
                        <h2>Story Teller</h2>
                        <p>ルールです。<br>改行</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const clickBtn = document.getElementById('click-btn');
        const popupWrapper = document.getElementById('popup-wrapper');
        const close = document.getElementById('close');

        // ボタンをクリックしたときにポップアップを表示させる
        clickBtn.addEventListener('click', () => {
            popupWrapper.style.display = "block";
        });

        // ポップアップの外側又は「x」のマークをクリックしたときポップアップを閉じる
        popupWrapper.addEventListener('click', e => {
            if (e.target.id === popupWrapper.id || e.target.id === close.id) {
                popupWrapper.style.display = 'none';
            }
        });
    </script>
</body>
</html>