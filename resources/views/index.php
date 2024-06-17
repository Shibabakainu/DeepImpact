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
            <button id="index-click-btn">ルール</button>
            <div id="index-popup-wrapper">
                <div id="index-popup-inside">
                    <div id="index-close">X</div>
                    <div class="text">
                        <h2>Story Teller</h2>
                        <p>ルールです。<br>改行</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const indexClickBtn = document.getElementById('index-click-btn');
        const indexPopupWrapper = document.getElementById('index-popup-wrapper');
        const indexClose = document.getElementById('index-close');

        // ボタンをクリックしたときにポップアップを表示させる
        indexClickBtn.addEventListener('click', () => {
            indexPopupWrapper.style.display = "block";
        });

        // ポップアップの外側又は「x」のマークをクリックしたときポップアップを閉じる
        indexPopupWrapper.addEventListener('click', e => {
            if (e.target.id === indexPopupWrapper.id || e.target.id === indexClose.id) {
                indexPopupWrapper.style.display = 'none';
            }
        });
    </script>
</body>

</html>