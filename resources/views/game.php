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

    <div id="second-popup-wrapper">
        <div class="button_2">
            <p class="warning-text">本当に退出しますか？</p>
            <button class="popup-btn" id="second-popup-close">キャンセル</button>
            <button class="other-btn" id="exit-btn">退出</button>
        </div>
    </div>

    <script>
        const gameClickBtn = document.getElementById('menu-click-btn');
        const gamePopupWrapper = document.getElementById('menu-popup-wrapper');
        const gameClose = document.getElementById('menu-close');
        const backBtn = document.querySelector('.back-btn');
        const secondPopupWrapper = document.getElementById('second-popup-wrapper');
        const secondPopupClose = document.getElementById('second-popup-close');
        const exitBtn = document.getElementById('exit-btn');

        // メニューをクリックしたときにポップアップを表示させる
        gameClickBtn.addEventListener('click', () => {
            gamePopupWrapper.style.display = "flex";
        });

        // ポップアップの外側または「キャンセル」をクリックしたときポップアップを閉じる
        gamePopupWrapper.addEventListener('click', e => {
            if (e.target.id === gamePopupWrapper.id || e.target.id === gameClose.id) {
                gamePopupWrapper.style.display = 'none';
            }
        });

        // 「退出する」ボタンをクリックしたときに2つ目のポップアップを表示させる
        backBtn.addEventListener('click', () => {
            secondPopupWrapper.style.display = 'flex';
        });

        // 2つ目のポップアップの外側または「閉じる」ボタンをクリックしたときポップアップを閉じる
        secondPopupWrapper.addEventListener('click', e => {
            if (e.target.id === secondPopupClose.id) {
                secondPopupWrapper.style.display = 'none';
            }
        });

        // 「退出」ボタンをクリックしたときに指定されたURLに移動する
        exitBtn.addEventListener('click', () => {
            window.location.href = "index.php";
        });
    </script>
</body>

</html>