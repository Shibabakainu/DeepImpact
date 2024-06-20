<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>game</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/game.css">
</head>

<body>
    <div class="menu-">
        <button id="menu-click-btn">メニュー</button>
        <div id="menu-popup-wrapper">
            <div class="button_1">
                <button class="popup-btn" id="menu-close">キャンセル</button>
                <button class="back-btn">退出する</button>
            </div>
        </div>
    </div>

    <div class="container">

        <h1>カードをホバーしてみてね</h1>

        <ul>
            <li>
                <div class="card" id="card1"><img src="/deepimpact/images/card1.png"></div>
            </li>
            <li>
                <div class="card" id="card2"><img src="/deepimpact/images/card2.png"></div>
            </li>
            <li>
                <div class="card" id="card3"><img src="/deepimpact/images/card3.png"></div>
            </li>
            <li>
                <div class="card" id="card4"><img src="/deepimpact/images/card4.png"></div>
            </li>
            <li>
                <div class="card" id="card5"><img src="/deepimpact/images/card5.png"></div>
            </li>
        </ul>
    </div>

    <script>
        const indexClickBtn = document.getElementById('menu-click-btn');
        const indexPopupWrapper = document.getElementById('menu-popup-wrapper');
        const indexClose = document.getElementById('menu-close');

        // ボタンをクリックしたときにポップアップを表示させる
        indexClickBtn.addEventListener('click', () => {
            indexPopupWrapper.style.display = "flex";
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