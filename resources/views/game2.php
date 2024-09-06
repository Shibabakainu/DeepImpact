<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    // ユーザーIDがない場合は、適切なエラーメッセージを表示するかリダイレクト
    die("ログインが必要です。");
}

// URLのクエリパラメータからcurrent_playersを取得
$current_players = isset($_GET['current_players']) ? $_GET['current_players'] : 1; // 設定されていない場合は1をデフォルトとする

// プレイヤー情報を取得
$players = [];
$sql = "SELECT name FROM users";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row['name'];
    }
    $result->free();
} else {
    die("プレイヤーデータの取得に失敗しました: " . $conn->error);
}
$conn->close();

// ポップアップ表示の条件
$shouldShowPopup = true; // 必要に応じて条件を設定してください
?>
<!DOCTYPE html>
<html lang="ja">
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

        .top-left-text {
            position: fixed;
            top: 10px;
            left: 10px;
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
        #hand {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.card {
    width: 100px;
    height: 150px;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    text-align: center;
    line-height: 150px;
    font-size: 18px;
}
    </style>
    <script type="text/javascript">
        // Ensure it's hidden initially
        document.addEventListener("DOMContentLoaded", function() {
            var shouldShowPopup = <?php echo json_encode($shouldShowPopup); ?>;
            if (shouldShowPopup) {
                document.getElementById('menu-popup-wrapper').style.display = 'none';
            } else {
                document.getElementById('menu-popup-wrapper').style.display = 'flex'; 
            }
        });
    </script>
</head>
<body>

    <div class="container">

        <ul>
            <li>
                <div class="card" id="hand"></div>
            </li>
            
        </ul>


    </div>

    <div id="textbox">
        <div id="chatbox"></div>
        <input type="text" id="message" placeholder="Enter message..." />
        <button onclick="sendMessage()">Send</button>
    </div>

    
        <div id="hand"></div>
        <button onclick="drawCard()">Draw Card</button>
        <div id="player-list"></div> <!-- プレイヤーリストを表示するための要素 -->
    

    <div class="top-left-text">
        <p>現在のプレイヤー:</p>
        <ul>
            <?php foreach ($players as $player): ?>
                <li><?php echo htmlspecialchars($player, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
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
        var ws = new WebSocket('ws://192.168.1.100:8080');
        ws.onopen = function() {
            console.log('Connected to the server');
            ws.send(JSON.stringify({ type: 'join', username: 'Player1' }));
        };
        ws.onmessage = function(event) {
            var data=JSON.parse(event.data);
            switch(data.type){
                case 'update_hand':
                    updateHand(data.hand);
                    break;
                case 'game_state':
                    updateGameState(data.state);
                    break;
                case 'player_list':
                    updatePlayerList(data.players);
                    break;
                case 'chat_message':
                    var chatbox = document.getElementById('chatbox');
                    var newMessage = document.createElement('div');
                    newMessage.classList.add('message');
                    newMessage.textContent = event.data;
                    chatbox.appendChild(newMessage);
                    animateMessage(newMessage);
                    break;
                default:
                    console.log('unknown message type:', data.type);
                    break;
            }
        };

        ws.onclose = function() {
            console.log('Disconnected from the server');
        };
        ws.onerror = function(error) {
            console.log('WebSocket Error: ' + error);
        };
        function drawCard() {
            ws.send(JSON.stringify({ type: 'draw_card' }));
        }

        function playCard(card) {
            ws.send(JSON.stringify({ type: 'play_card', card: card }));
        }

        function vote(card) {
            ws.send(JSON.stringify({ type: 'vote', card: card }));
        }

        function updateGameState(state) {
            // ゲーム状態を更新する処理を実装
            console.log('game state updated:', state);
        }

        function updateHand(hand) {
            const handContainer = document.getElementById('hand');
            handContainer.innerHTML = '';

            // プレイヤーの手札を更新する処理を実装
            hand.forEach(card => {
                const cardElement = document.createElement('div');
                cardElement.className = 'card';
                cardElement.innerText = card;
                handContainer.appendChild(cardElement);
            });
        }
        function sendMessage() {
            var message = document.getElementById('message').value;
            ws.send(JSON.stringify({ type: 'chat_message', message: message }));
            document.getElementById('message').value = '';
        }

        function animateMessage(messageElement) {
            messageElement.style.animation = 'slide-in 10s linear forwards';
            setTimeout(function() {
                messageElement.remove();
            }, 10000);
        }

        function updatePlayerList(players) {
            const playerListContainer = document.getElementById('player-list');
            playerListContainer.innerHTML = '<h3>Players in the game:</h3>';

            // プレイヤーリストを更新する処理を実装
            players.forEach(player => {
                const playerElement = document.createElement('div');
                playerElement.className = 'player';
                playerElement.innerText = player;
                playerListContainer.appendChild(playerElement);
            });
        }

        document.getElementById('menu-click-btn').addEventListener('click', function() {
            const menuPopupWrapper = document.getElementById('menu-popup-wrapper');
            if (menuPopupWrapper.style.display === 'flex') {
                menuPopupWrapper.style.display = 'none';
            } else {
                menuPopupWrapper.style.display = 'flex';
            }
        });

        document.getElementById('rule-click-btn').addEventListener('click', function() {
            document.getElementById('rule-popup-wrapper').style.display = 'block';
        });

        document.getElementById('rule-close').addEventListener('click', function() {
            document.getElementById('rule-popup-wrapper').style.display = 'none';
        });

        document.querySelector('.back-btn').addEventListener('click', function() {
            document.getElementById('second-popup-wrapper').style.display = 'flex';
        });

        document.getElementById('second-popup-close').addEventListener('click', function() {
            document.getElementById('second-popup-wrapper').style.display = 'none';
        });

        document.getElementById('exit-btn').addEventListener('click', function() {
            window.location.href = '/DeepImpact/exit.php';
        });

        $("button").click(function() {
            $(this).toggleClass("toggle");
        });
    </script>

<?php
    // 表示するテキストをPHPで定義
    $text = "これは右下に表示されるテキストです";
    echo "<div class='bottom-right-text'>{$text}</div>";
    ?>

</body>
</html>