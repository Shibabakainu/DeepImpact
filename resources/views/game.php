<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>game</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/game.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .bottom-right-text {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
        @keyframes slide-in {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(-100%);
            }
        }
        #chatbox {
            position: fixed;
            top: 50px;
            right: 10px;
            white-space: nowrap;
        }

        .message {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 5px;
            margin: 5px;
            border-radius: 3px;
            animation: slide-in 10s linear forwards;
        }

        #textbox {
            position: fixed;
            bottom: 10px;
            left: 10px;
            display: flex;
            align-items: center;
        }

        #textbox input[type="text"] {
            padding: 10px;
            border-radius: 5px 0 0 5px;
            border: 1px solid #ccc;
        }

        #textbox button {
            padding: 10px;
            border-radius: 0 5px 5px 0;
            border: 1px solid #ccc;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <ul>
            <li>
                <div class="card" id="card1"><img src="/DeepImpact/images/card1.png"></div>
            </li>
            <li>
                <div class="card" id="card2"><img src="/DeepImpact/images/card2.png"></div>
            </li>
            <li>
                <div class="card" id="card3"><img src="/DeepImpact/images/card3.png"></div>
            </li>
            <li>
                <div class="card" id="card4"><img src="/DeepImpact/images/card4.png"></div>
            </li>
            <li>
                <div class="card" id="card5"><img src="/DeepImpact/images/card5.png"></div>
            </li>
        </ul>
    </div>
    <div id="textbox">
        <div id="chatbox"></div>
        <input type="text" id="message" placeholder="Enter message..." />
        <button onclick="sendMessage()">Send</button>
    </div>
    <div class="menu-">
        <div id="menu-popup-wrapper">
            <div class="button_1">
                <button class="back-btn">退出する</button>
                <button class="popup-btn" id="rule-click-btn">ルール</button>
                <div id="rule-popup-wrapper" style="display: none;">
                    <div id="rule-popup-inside">
                        <div class="text">
                            <div id="rule-close">X</div>
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
        <button data-action="Menu-Close" class="hamburger-close" id="menu-click-btn">
            <span></span>
        </button>
    </div>

    <div id="second-popup-wrapper">
        <div class="button_2">
            <p class="warning-text">本当に退出しますか？</p>
            <button class="popup-btn" id="second-popup-close">キャンセル</button>
            <button class="other-btn" id="exit-btn">退出</button>
        </div>
    </div>

    <script>
        var ws = new WebSocket('ws://localhost:8080');
        ws.onopen = function() {
            console.log('Connected to the server');
        };
        ws.onmessage = function(event) {
            var chatbox = document.getElementById('chatbox');
            var newMessage = document.createElement('div');
            newMessage.classList.add('message');
            newMessage.textContent = event.data;
            chatbox.appendChild(newMessage);
            animateMessage(newMessage);
        };
        ws.onclose = function() {
            console.log('Disconnected from the server');
        };

        function sendMessage() {
            var messageInput = document.getElementById('message');
            var message = messageInput.value;
            ws.send(message);
            messageInput.value = '';
        };

    function animateMessage(message) {
        var posX = window.innerWidth;
        function step() {
            posX -= 8;
            if (posX < -message.offsetWidth) {
                message.remove();
            } else {
                message.style.transform = 'translateX(' + posX + 'px)';
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    }


        const gameClickBtn = document.getElementById('menu-click-btn');
        const gamePopupWrapper = document.getElementById('menu-popup-wrapper');
        const backBtn = document.querySelector('.back-btn');
        const secondPopupWrapper = document.getElementById('second-popup-wrapper');
        const secondPopupClose = document.getElementById('second-popup-close');
        const exitBtn = document.getElementById('exit-btn');

        // 新しいルールポップアップ関連の変数
        const ruleClickBtn = document.getElementById('rule-click-btn');
        const rulePopupWrapper = document.getElementById('rule-popup-wrapper');
        const ruleClose = document.getElementById('rule-close');

        $(document).ready(function() {
            $("button").click(function() {
                $(this).toggleClass("toggle");
            });
        });

        // ハンバーガーメニューをクリックしたときポップアップを表示する/閉じる
        gameClickBtn.addEventListener('click', () => {
            if (gamePopupWrapper.style.display === 'flex') {
                gamePopupWrapper.style.display = 'none';
            } else {
                gamePopupWrapper.style.display = 'flex';
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

        // 新しいルールポップアップ関連のイベントリスナー
        ruleClickBtn.addEventListener('click', () => {
            rulePopupWrapper.style.display = "block";
        });

        rulePopupWrapper.addEventListener('click', e => {
            if (e.target.id === rulePopupWrapper.id || e.target.id === ruleClose.id) {
                rulePopupWrapper.style.display = 'none';
            }
        });
    </script>
</body>

<?php
// 表示するテキストをPHPで定義
$text = "これは右下に表示されるテキストです";
echo "<div class='bottom-right-text'>{$text}</div>";
?>


</html>