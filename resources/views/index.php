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
    <audio autoplay loop>
        <source src="/DeepImpact/bgm/sekiranun.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio>
    <script>
        window.onload = function() {
            var bgm = document.getElementById('bgm');

            // 前回の再生位置があれば取得して、そこから再生する
            var savedTime = localStorage.getItem('bgmTime');
        };
    </script>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <img src="/DeepImpact/images/sttera.png" alt="Story Teller" class="header-image">
        <div class="buttons">
            <button onclick="window.location.href='room_setting.php'">ルーム作成</button>
            <button onclick="window.location.href='room_search.php'">ルーム検索</button>
            <button onclick="window.location.href='frieview.php'">フレンド</button>
            <button id="index-click-btn">ヘルプ</button>
            <div id="index-popup-wrapper">
                <div id="index-popup-inside">
                    <div id="index-close">X</div>
                    <div id="popup-content">
                        <!-- ここにチュートリアルコンテンツが読み込まれます -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 画像を拡大表示するためのモーダル -->
    <div id="imageModal" class="modal" style="display: none;">
        <span id="closeModal" class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <?php if (!$loggedIn) : ?>
        <div id="login-popup-wrapper" style="display: flex;">
            <div id="login-popup-inside">
                <div class="text">ログインしてください</div>
                <button onclick="window.location.href='/DeepImpact/resources/views/login/login.php'">ログインページへ</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const indexClickBtn = document.getElementById('index-click-btn');
        const indexPopupWrapper = document.getElementById('index-popup-wrapper');
        const indexClose = document.getElementById('index-close');
        const popupContent = document.getElementById('popup-content');

        function loadTutorial() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/DeepImpact/resources/views/tutorial.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        popupContent.innerHTML = xhr.responseText;


                        // tutorial.php内の画像クリック処理
                        const clickableImage = document.getElementById('clickableImage');
                        if (clickableImage) {
                            clickableImage.addEventListener('click', function() {
                                const modal = document.getElementById('imageModal');
                                const modalImage = document.getElementById('modalImage');
                                modal.style.display = 'flex'; // モーダルを表示
                                modalImage.src = this.src; // クリックした画像のsrcをモーダルに設定
                            });
                        }

                        // モーダルを閉じる処理
                        const closeModal = document.getElementById('closeModal');
                        const modal = document.getElementById('imageModal');
                        closeModal.addEventListener('click', function() {
                            modal.style.display = 'none'; // バツマークをクリックしてモーダルを閉じる
                        });

                        // モーダルの外側をクリックして閉じる
                        modal.addEventListener('click', function(e) {
                            if (e.target === modal) {
                                modal.style.display = 'none'; // 外側をクリックしてモーダルを閉じる
                            }
                        });


                    } else {
                        console.error("Error loading tutorial: " + xhr.status + " " + xhr.statusText);
                    }
                }
            };
            xhr.onerror = function() {
                console.error("Request failed.");
            };
            xhr.send();
        }

        // ルールボタンをクリックしたときにポップアップを表示し、チュートリアルを読み込む
        indexClickBtn.addEventListener('click', () => {
            indexPopupWrapper.style.display = "block";
            loadTutorial(); // コンテンツを動的に読み込む
        });

        // ポップアップ外や「X」ボタンをクリックしたらポップアップを閉じる
        indexPopupWrapper.addEventListener('click', e => {
            if (e.target.id === indexPopupWrapper.id || e.target.id === indexClose.id) {
                indexPopupWrapper.style.display = 'none';
            }
        });
    </script>
</body>

</html>