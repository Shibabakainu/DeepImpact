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
        /* メッセージボタンのスタイル */
        .message-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            position: fixed;
            right: 130px;
            /* 「ベルアイコン」の左側に配置 */
            top: 20px;
            z-index: 1000;
            /* 他の要素より前面に表示 */
        }

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

        #bgm-volume-value,
        #sfx-volume-value {
            font-size: 12px;
            color: #777;
            margin-left: 10px;
        }

        #volume-textarea-wrapper {
            background-color: rgba(0, 0, 0, .5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 996;
            animation: fadeIn 0.7s cubic-bezier(0.33, 1, 0.68, 1) forwards;

        }



        #volume-textarea-close {
            position: absolute;
            top: 0;
            right: 5px;
            cursor: pointer;
        }


        #volume-textarea-inside {
            background-image: url('/DeepImpact/images/aiueo.png');
            background-size: cover;
            /* 画像をコンテナ全体にフィットさせる */
            background-position: center;
            /* 背景画像を中央に配置 */
            text-align: center;
            width: 150%;
            max-width: 50%;
            height: 45%;
            margin: 10% auto;
            background-color: azure;
            padding: 20px;
            position: relative;
            z-index: 997;
            overflow: scroll;
            border-radius: 5px;
            font-family: 'Arial', sans-serif;
            /* モダンなフォント */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* ソフトな影を追加 */
        }




        /* ラベルのスタイル */
        label {
            display: block;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            color: #090404;
        }

        /* スライダーのスタイル */
        input[type="range"] {
            -webkit-appearance: none;
            /* デフォルトのスタイルをリセット */
            width: 60%;
            /* 幅を全体に広げる */
            height: 10px;
            background: linear-gradient(to right, #4caf50, #8bc34a);
            /* グラデーション背景 */
            border-radius: 5px;
            /* 角を丸くする */
            outline: none;
            transition: background 0.3s;
            /* 背景色の変更をスムーズに */
        }

        /* スライダーのつまみ（thumb）のスタイル */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            background: #4caf50;
            /* つまみの色 */
            border-radius: 50%;
            /* 丸い形状 */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* 軽い影 */
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            /* 色とサイズの変更をスムーズに */
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            background: #45a049;
            /* ホバー時に濃い緑色に */
            transform: scale(1.1);
            /* 少し大きくする */
        }

        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #4caf50;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- メッセージボタン -->
    <!--<button onclick="window.location.href='/DeepImpact/resources/views/inbox.php'" class="message-button">メッセージ</button>-->

    <!-- <img src="/DeepImpact/images/bell.jpg" style="max-width: 5%; height: auto; position: fixed; right: 200px; top: 100px;" class="bell"> -->

    <!-- メッセージボックスのポップアップ -->
    <div id="messageBox" class="message-box">
        <div class="message-header">
            <span class="close-btn">&times;</span>
            <button id="dragButton" class="drag-button">ドラッグで移動</button>
        </div>
        <div class="message-content">
            <p>この機能は撤廃しました。</p>
        </div>
    </div>

    <script>
        const bellImage = document.querySelector('.bell');
        const messageBox = document.getElementById('messageBox');
        const closeBtn = document.querySelector('.close-btn');
        const dragButton = document.getElementById('dragButton');

        bellImage.addEventListener('click', function(event) {
            const rect = bellImage.getBoundingClientRect();
            messageBox.style.top = (rect.bottom + window.scrollY + 10) + 'px'; // 修正: 数値を文字列に変換
            messageBox.style.left = (rect.left + window.scrollX) + 'px'; // 修正: 数値を文字列に変換
            messageBox.style.display = 'block';
        });

        closeBtn.addEventListener('click', function() {
            messageBox.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target !== messageBox && !messageBox.contains(event.target) && event.target !== bellImage) {
                messageBox.style.display = 'none';
            }
        });

        let offsetX = 0,
            offsetY = 0,
            isDragging = false;

        dragButton.addEventListener('mousedown', function(e) {
            isDragging = true;
            offsetX = e.clientX - messageBox.getBoundingClientRect().left;
            offsetY = e.clientY - messageBox.getBoundingClientRect().top;
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
        });

        function drag(e) {
            if (isDragging) {
                messageBox.style.left = (e.clientX - offsetX) + 'px'; // 修正: 数値を文字列に変換
                messageBox.style.top = (e.clientY - offsetY) + 'px'; // 修正: 数値を文字列に変換
            }
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }
    </script>

    <!--こうかおん  てか無理かも～できへん助けてなんで鳴らへんねんおかしいやん
    無理よ～一回だけ鳴るようになったよ-->
    <audio id="hoverSound" src="/DeepImpact/bgm/03_ぷい.mp3" preload="auto"></audio>
    <script type="text/javascript">
        // 効果音用のAudio要素を取得
        const hoverSound = document.getElementById('hoverSound');
        hoverSound.preload = 'auto';

        // カードにマウスを乗せたときの効果音再生
        $(document).on('mouseenter', '.card', function() {
            hoverSound.currentTime = 0; // 効果音をリセットして最初から再生
            hoverSound.play().catch(error => console.error("ホバーサウンド再生に失敗:", error));
        });

        // 効果音スライダーのイベントリスナーを追加
        document.getElementById('sfx-volume').addEventListener('input', function(event) {
            const volume = event.target.value / 100; // 0-100 の値を 0-1 に変換
            hoverSound.volume = volume; // 効果音の音量を設定
            document.getElementById('sfx-volume-value').innerText = `${event.target.value}%`; // 現在の値を表示
        });
    </script>


    <!-- ボタンを設置、クリックでBGMを再生/停止 -->
    <button id="bgm-toggle-btn" class="bgm-btn">
        <span id="bgm-icon">🔊</span>
    </button>

    <audio id="bgm" src="/DeepImpact/bgm/PerituneMaterial_Poema.mp3" preload="auto" loop autoplay>
        <!-- オーディオ要素：BGMを再生、ループ設定を有効化 -->
        <source src="/DeepImpact/bgm/PerituneMaterial_Poema.mp3" type="audio/mpeg">
    </audio>
    <script>
        // 最初のクリックでミュート解除 (Chrome制限対応)
        document.body.addEventListener('click', () => {
            bgm.muted = false;
            bgm.play().catch(console.error);
        }, {
            once: true
        }); // このイベントは一度だけ実行

        const context = new AudioContext();

        // Setup an audio graph with AudioNodes and schedule playback.

        // Resume AudioContext playback when user clicks a button on the page.
        document.querySelector('button').addEventListener('click', function() {
            context.resume().then(() => {
                console.log('AudioContext playback resumed successfully');
            });
        });

        // DOMの読み込みが完了したときに実行される処理
        document.addEventListener('DOMContentLoaded', function() {
            const bgm = document.getElementById('bgm');
            const bgmToggleBtn = document.getElementById('bgm-toggle-btn');
            const bgmIcon = document.getElementById('bgm-icon');
            let isPlaying = false;

            // ボタンがクリックされたときのイベントハンドラを定義
            bgmToggleBtn.addEventListener('click', function() {
                if (isPlaying) {
                    // 再生中ならBGMを一時停止
                    bgm.pause();
                    bgmIcon.textContent = '🔇'; // アイコンをミュートのものに変更
                } else {
                    // 停止中ならBGMを再生
                    bgm.play();
                    bgmIcon.textContent = '🔊'; // アイコンをスピーカーのものに変更
                }
                isPlaying = !isPlaying; // フラグを反転（再生⇔停止を切り替え）
            });

            // ユーザーがページを離れる前に音楽を停止する処理
            window.addEventListener('beforeunload', () => {
                bgm.pause(); // ページが閉じられる前にBGMを停止
            });

            // 1秒後にボタンを自動的にクリック
            setTimeout(function() {
                bgmToggleBtn.click(); // ここでボタンがクリックされる
            }, 2000); // 1000ミリ秒 = 1秒
        });
    </script>


    <?php if (!$loggedIn) : ?>
        <div id="login-popup-wrapper" style="display: flex;">
            <div id="login-popup-inside">
                <div class="text">ログインしてください</div>
                <button onclick="window.location.href='/DeepImpact/resources/views2/login/login.php'">ログインページへ</button>
            </div>
        </div>
    <?php endif; ?>

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
                    <div id="popup-content"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="volume-textarea-wrapper" style="display: none;">
        <div id="volume-textarea-inside">
            <div class="text">
                <div id="volume-textarea-close">X</div>
                <!-- 音量調節スライダー -->
                <label for="bgm-volume">BGM 音量: <span id="bgm-volume-value">100%</span></label>
                <input id="bgm-volume" type="range" min="0" max="100" value="100">
            </div>
            <div>
                <label for="sfx-volume">効果音 音量: <span id="sfx-volume-value">100%</span></label>
                <input id="sfx-volume" type="range" min="0" max="100" value="100">
            </div>
        </div>
    </div>

    <div id="imageModal" class="modal" style="display: none;">
        <span id="closeModal" class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        const indexClickBtn = document.getElementById('index-click-btn');
        const indexPopupWrapper = document.getElementById('index-popup-wrapper');
        const indexClose = document.getElementById('index-close');
        const popupContent = document.getElementById('popup-content');

        function loadTutorial() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/DeepImpact/resources/views2/tutorial.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        popupContent.innerHTML = xhr.responseText;

                        const clickableImages = document.querySelectorAll('.clickableImage');

                        clickableImages.forEach(image => {
                            image.addEventListener('click', function() {
                                const modal = document.getElementById('imageModal');
                                const modalImage = document.getElementById('modalImage');
                                modal.style.display = 'flex';
                                modalImage.src = this.src;
                            });
                        });

                        const closeModal = document.getElementById('closeModal');
                        const modal = document.getElementById('imageModal');
                        closeModal.addEventListener('click', function() {
                            modal.style.display = 'none';
                        });

                        modal.addEventListener('click', function(e) {
                            if (e.target === modal) {
                                modal.style.display = 'none';
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

        indexClickBtn.addEventListener('click', () => {
            indexPopupWrapper.style.display = "block";
            loadTutorial();
        });

        indexPopupWrapper.addEventListener('click', e => {
            if (e.target.id === indexPopupWrapper.id || e.target.id === indexClose.id) {
                indexPopupWrapper.style.display = 'none';
            }
        });


        // 音量調節のスライダーをセットアップ
        document.getElementById('bgm-volume').addEventListener('input', function(event) {
            // スライダーの値を取得し、0-100 の範囲を 0-1 に変換
            const volume = event.target.value / 100;
            // BGMオーディオ要素を取得
            const bgm = document.getElementById('bgm');
            // 取得した値をBGMの音量に設定
            bgm.volume = volume;
            // 現在の音量をパーセンテージ形式で表示
            document.getElementById('bgm-volume-value').innerText = `${event.target.value}%`;
        });

        // 効果音の音量調節スライダーをセットアップ
        document.getElementById('sfx-volume').addEventListener('input', function(event) {
            // スライダーの値を取得し、0-100 の範囲を 0-1 に変換
            const volume = event.target.value / 100;
            // 効果音オーディオ要素を取得
            const hoverSound = document.getElementById('hoverSound');
            // 取得した値を効果音の音量に設定
            hoverSound.volume = volume;
            // 現在の音量をパーセンテージ形式で表示
            document.getElementById('sfx-volume-value').innerText = `${event.target.value}%`;
        });

        document.getElementById("volume-btn").addEventListener("click", function() {
            document.getElementById("volume-textarea-wrapper").style.display = "block";
        });

        document.getElementById("volume-textarea-close").addEventListener("click", function() {
            document.getElementById("volume-textarea-wrapper").style.display = "none";
        });
    </script>
</body>

</html>