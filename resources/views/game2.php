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

// プレイヤーのポジション（1から6）を取得
$player_position = isset($_SESSION['player_position']) ? $_SESSION['player_position'] : null;
if (!$player_position) {
    die("プレイヤーのポジションが不明です。");
}


// プレイヤーに配られた5枚のカードを取得
$sql = "
    SELECT rc.room_card_id, c.Card_id, c.Card_name, c.Image_path, rc.selected 
    FROM room_cards rc 
    JOIN Card c ON rc.card_id = c.Card_id 
    JOIN room_players rp ON rc.room_id = rp.room_id 
    WHERE rc.room_id = ? AND rp.user_id = ? AND rc.status = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('iii', $room_id, $user_id, $player_position); // Use user_id to filter cards for the current player
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cards = [];
    while ($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
    $stmt->close();
} else {
    die("カードデータの取得に失敗しました: " . $conn->error);
}

// ターン数の初期化（初回のみ）
if (!isset($_SESSION['turn'])) {
    $_SESSION['turn'] = 1; // 初期ターンは1
}

// ユーザーがポップアップでOKを押した場合、ターンを進める
if (isset($_POST['next_turn'])) {
    $_SESSION['turn']++;
}

// 新しく始めるボタンが押されたらセッションをリセット
if (isset($_POST['reset_game'])) {
    $_SESSION['turn'] = 1;
}

// 最大ターン数
$max_turns = 6;

// 現在のターン数を取得
$turn = $_SESSION['turn'];

// ターンが最大に達したらセッションをリセット（任意の条件で）
if ($turn > $max_turns) {
    $_SESSION['turn'] = $max_turns;
    $message = "ゲーム終了！全てのターンが終了しました。";
}

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
    <audio autoplay loop>
        <source src="/DeepImpact/bgm/PerituneMaterial_Poema.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio>
    <script>
        window.onload = function() {
        // Automatically check if there are already drawn cards
        updateDrawnCards(); // Call function to update drawn cards display
        var bgm = document.getElementById('bgm');

        // 音量調整
        bgm.volume = 0.5; // 音量を50%に設定
    };

    // Function to update drawn cards on load
    function updateDrawnCards() {
        // Fetch drawn cards from the server
        $.ajax({
            url: 'get_drawn_cards.php', // Create this script to retrieve drawn cards for the current user
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#drawed-card-area').empty(); // Clear existing cards
                    response.cards.forEach(function(card) {
                        $('#drawed-card-area').append(
                            '<div class="card" data-room-card-id="' + card.room_card_id + '">' +
                            '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                            '</div>'
                        );
                    });
                } else {
                    console.error('Failed to retrieve drawn cards: ' + response.message);
                }
            },
            error: function() {
                alert("Error retrieving drawn cards.");
            }
        });
    }

    </script>
    <!-- Show player's hand -->
    <div class="container">
        <div class="onhand">
            <div class="draw" id="draw"><button id="draw-cards">Draw Cards</button></div>

            <div id="drawed-card-area" class="drawed-card-area">
                <?php foreach ($cards as $card): ?>
                    <?php if ($card['selected'] == 0): // Only show cards that are not selected 
                    ?>
                        <div class="card" data-room-card-id="<?= $card['room_card_id'] ?>" draggable="true">
                            <img src="../../images/<?= $card['Image_path'] ?>" alt="<?= htmlspecialchars($card['Card_name'], ENT_QUOTES) ?>">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Voting section (for all cards with selected 1) -->
    <div class="vote-area" id="vote-area">
        <!-- Cards with selected 1 will be loaded here -->
    </div>
    <div class="title">
        最高のカードに投票してください
    </div>

    <script type="text/javascript">
        // URLからroom_idを取得する関数
        function getRoomIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('room_id');
        }

        const roomId = getRoomIdFromUrl(); // URLからroom_idを取得

        // Click event for drawing cards
        $(document).ready(function() {
            $("#draw-cards").click(function() {
                $.ajax({
                    url: 'draw_cards.php', // Server-side script to handle card drawing
                    method: 'POST',
                    dataType: 'json', // Expecting JSON response
                    success: function(response) {
                        // Clear the existing cards
                        $('#drawed-card-area').empty();

                        // Loop through the response and display the cards
                        if (response.success) {
                            response.cards.forEach(function(card) {
                                $('#drawed-card-area').append(
                                    '<div class="card" data-room-card-id="' + card.room_card_id + '">' + // Change here
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

        // Click event for selecting cards
        $(document).on("click", ".card", function() {
            var roomCardId = $(this).data("room-card-id");

            if (!roomCardId) {
                alert("No Room Card ID found.");
                return; // Stop execution if the card ID is not set
            }

            console.log("Room ID: " + roomId + ", Room Card ID: " + roomCardId);

            $.ajax({
                url: 'select_card.php', 
                method: 'POST',
                data: {
                    room_id: roomId,
                    room_card_id: roomCardId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $(".card[data-room-card-id='" + roomCardId + "']").addClass('selected');
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert("Error selecting card.");
                }
            });
        });

        // Function to fetch and update the vote area
        function updateVoteArea() {
            $.ajax({
                url: 'get_votes.php',
                method: 'GET',
                data: {
                    room_id: roomId
                },
                dataType: 'html',
                success: function(response) {
                    $('#vote-area').html(response); // Update the vote area with new content
                },
                error: function() {
                    alert('投票エリアの更新に失敗しました。');
                }
            });
        }

        // Voting logic
        $(document).on('click', '.selected-card', function() {
            var roomCardId = $(this).data('room-card-id'); // Capture room_card_id instead of card_id

            if (!roomId) {
                alert('Room ID is missing!');
                return; // Ensure we have a valid roomId
            }

            $.ajax({
                url: 'vote.php',
                method: 'POST',
                data: {
                    room_card_id: roomCardId,  // Send room_card_id
                    room_id: roomId
                },
                dataType: 'json',  // Expect JSON response
                success: function(response) {
                    if (response.success) {
                        alert('投票が完了しました！');
                    } else {
                        alert('投票に失敗しました: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Response received:", xhr.responseText);
                    console.error("エラーが発生しました:", status, error);
                    alert('投票中にエラーが発生しました。再度お試しください。');
                }
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
        <script>
            // ポップアップで確認する関数
            function showPopup() {
                if (confirm("次のターンに進みますか？")) {
                    document.getElementById("nextTurnForm").submit(); // ユーザーが承認したらフォーム送信
                }
            }
        </script>
        <h1>現在のターン: <?php echo $turn; ?> / 6</h1>

        <?php if ($turn < 6): ?>
            <form id="nextTurnForm" method="POST">
                <input type="hidden" name="next_turn" value="1">
                <button class="nextturn" type="button" onclick="showPopup()">次のターンに進む</button>
            </form>
        <?php else: ?>
            <p>ゲーム終了！全てのターンが終了しました。</p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="reset_game" value="1">
            <button class="newgame" type="submit">新しく始める</button>
        </form>
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
        function sendMessage() {
            var message = document.getElementById('message').value;
            ws.send(JSON.stringify({
                type: 'chat_message',
                message: message
            }));
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
            window.location.href = '/DeepImpact/resources/views/index.php';
        });

        $("button").click(function() {
            $(this).toggleClass("toggle");
        });
    </script>

    <?php
    // Define the story text for each turn
    $text1 = "昔々、平和な国があり、その国は緑豊かな土地と、穏やかな人々に恵まれていました。しかし魔王が現れ軍勢を率いて国を支配しました。魔王は強力な魔法が使え、心臓が３つあり、国は恐怖に包まれました。人々は魔王に立ち向かう勇者が現れるのを待ち望んでいました。
    そんな時、小さな町に住む<b>正義感の強い若い戦士</b>が立ち上がりました。";
    $text2 = "正義感の強い若い戦士は魔王を倒しに行こうと決心しました。しかし３つの心臓と軍勢相手に一人で行くのはあまりにも無謀だと思いました。それに３つの心臓はそれぞれ火と水と風の剣でないと効果がないことが分かりその剣の持ち主を探しに行きました。まず火の洞窟へ持ち主に会いに行きました。火の剣の持ち主は<b>すごく協力的で体中に傷があり鋭い目</b>をしていました。";
    $text3 = "次に水の剣の持ち主に会いに行きました。水の剣の持ち主は協力してくれたものの<b>愛想の悪い面倒くさがりの性格</b>でした。";
    $text4 = "最後に風の剣の持ち主に会いに行きました。風の剣の持ち主は<b>警戒心が強く目力も強い背の高い力持ち</b>でした。";
    $text5 = "四人は準備を整えて魔王を倒しにいきました。待ち構えていた軍勢を倒し魔王の部屋につきました。そこにいたのは<b>背の低い威圧感のある強そうな魔王</b>でした。";
    $text6 = "壮絶な戦いの末、勇者たちは魔王を倒し、国に平和を取り戻しました。";

    $storyText = ""; // Variable to hold the current turn's story

    // Switch case to display the story based on the turn
    switch ($turn) {
        case 1:
            $storyText = $text1;
            break;
        case 2:
            $storyText = $text2;
            break;
        case 3:
            $storyText = $text3;
            break;
        case 4:
            $storyText = $text4;
            break;
        case 5:
            $storyText = $text5;
            break;
        case 6:
            $storyText = $text6;
            break;
        default:
            $storyText = "物語が終了しました。";
            break;
    }

    // Display the story for the current turn
    echo "<div class='story-card'>{$storyText}</div>";
    ?>

    <div class="scoreboard">
        <p>スコアボード</p>
    </div>
    
    <?php
    $conn->close();
    ?>

</body>

</html>