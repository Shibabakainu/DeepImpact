<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    die("ログインが必要です。");
}

// URLまたはセッションからroom_idを取得（必要に応じて調整）
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;
if (!$room_id) {
    die("ルームIDが指定されていません。");
}

// プレイヤー情報を取得（room_playerテーブルからプレイヤー名を取得）
$players = [];
$sql = "
    SELECT u.name 
    FROM room_players rp
    JOIN users u ON rp.user_id = u.id
    WHERE rp.room_id = ?
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $room_id);  // Bind the room_id to the query
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $players[] = $row['name'];
    }
    $stmt->close();
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
        <div class="onhand">
            <div class="draw" id="draw"><button id="draw-cards">Draw Cards</button></div>

            <!-- Display 5 face-down cards initially -->
            <div id="selected-card-area" class="selected-card-area">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo '<div class="card">';
                    echo '<img src="../../images/hide' . $i . '.jpg" alt="Face Down Card">';
                    echo '</div>';
                }
                ?>
            </div>
            <?php
            include 'db_connect.php'; // データベース接続スクリプトをインクルード

            // ランダムに6枚のカードを選択するSQLクエリ
            $sql = "SELECT Card_id, Card_name, Image_path, IsVisible FROM Card WHERE IsVisible = 1 ORDER BY RAND() LIMIT 6";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $selectedCardIds = [];
                echo '<div id="selected-card-area" class="selected-card-area"></div>';
                echo '<div id="card-container" class="card-container">';
                while ($row = $result->fetch_assoc()) {
                    $selectedCardIds[] = $row["Card_id"];
                    $visibilityClass = $row['IsVisible'] == 3 ? 'selected-card' : '';
                    echo '<div class="card ' . $visibilityClass . '" data-value="' . $row["Card_id"] . '">';
                    echo '<img src="../../images/' . $row["Image_path"] . '" alt="' . $row["Card_name"] . '">';
                    echo '</div>';
                }
                echo '</div>';

                // 選択されたカードの IsVisible を 2 に更新
                if (!empty($selectedCardIds)) {
                    $idsToUpdate = implode(",", $selectedCardIds);
                    $updateSql = "UPDATE Card SET IsVisible = 2 WHERE Card_id IN ($idsToUpdate)";
                    $conn->query($updateSql);

                    // 選択されていないカードの IsVisible を 1 に更新
                    $updateOthersSql = "UPDATE Card SET IsVisible = 1 WHERE Card_id NOT IN ($idsToUpdate) AND IsVisible != 1";
                    $conn->query($updateOthersSql);
                }
            } else {
                echo " ";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            // Click event for drawing cards
            $("#draw-cards").click(function() {
                $.ajax({
                    url: 'draw_cards.php', // Server-side script to handle card drawing
                    method: 'POST',
                    dataType: 'json', // Expecting JSON response
                    success: function(response) {
                        // Clear the existing cards
                        $('#selected-card-area').empty();

                        // Loop through the response and display the cards
                        if (response.success) {
                            response.cards.forEach(function(card) {
                                $('#selected-card-area').append(
                                    '<div class="card" data-value="' + card.Card_id + '">' +
                                    '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                                    '</div>'
                                );
                            });
                        } else {
                            alert("Failed to draw cards: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error drawing cards.");
                    }
                });
            });
        });
    </script>

    <div id="textbox">
        <div id="chatbox"></div>
        <input type="text" id="message" placeholder="Enter message..." />
        <button onclick="sendMessage()">Send</button>
    </div>

    <div class="player-list">
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
    $text = "昔々、平和な国があり、その国は緑豊かな土地と、穏やかな人々に恵まれていました。しかし魔王が現れ軍勢を率いて国を支配しまし。魔王は強力な魔法が使え、心臓が３つあり、国は恐怖に包まれました。人々は魔王に立ち向かう勇者が現れるのを待ち望んでいました。
    そんな時、小さな町に住む<b>正義感の強い若い戦士</b>が立ち上がりました。";
    echo "<div class='story-card'>{$text}</div>";
?>

</body>
</html>