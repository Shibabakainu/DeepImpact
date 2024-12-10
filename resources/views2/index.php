<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Teller</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/index.css">
    <style>
        /* ポップアップのスタイル */
        #login-popup-wrapper {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        #login-popup-inside {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }

        #login-popup-inside .text {
            margin-bottom: 20px;
        }

        #login-popup-inside button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <img src="/DeepImpact/images/sttera.png" alt="Story Teller" class="header-image">
        <div class="buttons">
            <button onclick="window.location.href='room_setting.php'">ルーム作成</button>
            <button onclick="window.location.href='room_search.php'">ルーム検索</button>
            <button onclick="window.location.href='frieview.php'">フレンド</button>
            <button id="index-click-btn">ルール</button>
            <div id="index-popup-wrapper">
                <div id="index-popup-inside">
                    <div class="text">
                        <div id="index-close">X</div>
                        <p>※注意事項※</p>
                        <ul>
                            <li>ゲーム推奨プレイ人数は6人となっています。</li><br>
                            <li>あとは適当に追加</li>
                        </ul>
                        <p>ゲーム開始時</p>
                        <ul>
                            <li>各プレイヤーに5枚のカードを配ります。</li>
                        </ul>
                        <p>カードの提出</p>
                        <ul>
                            <li>物語を確認し、自分の手札から物語のフレーズに合うと思うカードを1枚選択し、待機します。</li><br>
                            <li>全てのプレイヤーが選び終えると、画面中央に選ばれたカードが表示されます。</li>
                        </ul>
                        <p>投票</p>
                        <ul>
                            <li>各プレイヤーは、物語のフレーズに1番あっていると思うカードを選び、投票することができます。</li><br>
                            <li>注意として、自身が提出したカードに投票することはできません。</li>
                        </ul>
                        <p>得点</p>
                        <ul>
                            <li>投票が入ったカードを出したプレイヤーは、投票1つにつき、+1点を獲得します。</li><br>
                            <li>1番票を集めたカードに、投票をしていた場合には投票者にも+1点を獲得します。</li>
                        </ul>
                        <p>ラウンド終了</p>
                        <ul>
                            <li>各プレイヤーは新しいカードを1枚手に入れ、手札が5枚に戻ります。</li>
                        </ul>
                        <p>ゲーム終了</p>
                        <ul>
                            <li>物語の決められたチャプター(ターン)が全て終えると、ゲーム終了です。</li><br>
                            <li>最も得点の多いプレイヤーの勝利となります。</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$loggedIn) : ?>
        <div id="login-popup-wrapper" style="display: flex;">
            <div id="login-popup-inside">
                <div class="text">ログインしてください</div>
                <button onclick="window.location.href='/DeepImpact/resources/views2/login/login.php'">ログインページへ</button>
            </div>
        </div>
    <?php endif; ?>

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