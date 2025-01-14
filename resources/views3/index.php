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

    <audio autoplay loop>
        <source src="/DeepImpact/bgm/sekiranun.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio>

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
    </script>
</body>

</html>