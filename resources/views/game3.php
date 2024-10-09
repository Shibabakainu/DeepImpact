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
    SELECT c.Card_id, c.Card_name, c.Image_path, rc.selected
    FROM room_cards rc
    JOIN Card c ON rc.card_id = c.Card_id
    WHERE rc.room_id = ? AND rc.status = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $room_id, $player_position);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
    <script type="text/javascript">
        // Ensure it's hidden initially
        document.addEventListener("DOMContentLoaded", function() {
            var shouldShowPopup = <?php echo json_encode($shouldShowPopup); ?>;
            if (!shouldShowPopup) {
                document.getElementById('menu-popup-wrapper').style.display = 'flex';
            } else {
                document.getElementById('menu-popup-wrapper').style.display = 'none';
            }
        });
    </script>
</head>

<body>

    <!-- Show player's hand -->
    <div class="container">
        <div class="onhand">
            <div class="draw" id="draw"><button id="draw-cards">Draw Cards</button></div>

            <div id="drawed-card-area" class="drawed-card-area">
                <?php foreach ($cards as $card): ?>
                    <?php if ($card['selected'] == 0): // Only show cards that are not selected 
                    ?>
                        <div class="card" data-card-id="<?= $card['Card_id'] ?>" draggable="true">
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
    <h2>Vote for the Best Card:</h2>

    <script type="text/javascript">
        // URLからroom_idを取得する関数
        function getRoomIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('room_id');
        }

        const roomId = getRoomIdFromUrl(); // URLからroom_idを取得

        $(document).on('click', '.card', function() {
            var cardElement = $(this);
            var cardId = cardElement.data('card-id');

            $.ajax({
                url: 'select_card.php',
                method: 'POST',
                data: {
                    card_id: cardId,
                    room_id: roomId
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert('カードが選ばれました！');
                        $('#drawed-card-area').find('.card[data-card-id="' + cardId + '"]').remove();
                        updateVoteArea();
                    } else {
                        alert('カードの選択に失敗しました: ' + result.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("エラーが発生しました:", status, error);
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
            var cardId = $(this).data('card-id');
            $.ajax({
                url: 'vote.php',
                method: 'POST',
                data: {
                    room_card_id: cardId,
                    room_id: roomId
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert('投票が完了しました！');
                    } else {
                        alert('投票に失敗しました: ' + result.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("エラーが発生しました:", status, error);
                }
            });
        });
    </script>

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
                        $('#drawed-card-area').empty();

                        // Loop through the response and display the cards
                        if (response.success) {
                            response.cards.forEach(function(card) {
                                $('#drawed-card-area').append(
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

    <div class="map">

    </div>

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
                <button type="button" onclick="showPopup()">次のターンに進む</button>
            </form>
        <?php else: ?>
            <p>ゲーム終了！全てのターンが終了しました。</p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="reset_game" value="1">
            <button type="submit">新しく始める</button>
        </form>
    </div>

    <div class="menu-">
        <div id="menu-popup-wrapper">
            <div class="button_1">
                <button class="back-btn">退出する</button>
                <button class="popup-btn" id="rule-click-btn">ヘルプ</button>
                <div id="rule-popup-wrapper" style="display: none;">
                    <div id="rule-popup-inside">
                        <div id="rule-close">X</div>
                        <div id="popup-content-game">
                            <!-- ここにチュートリアルコンテンツが読み込まれます -->
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

        <!-- 画像を拡大表示するためのモーダル -->
        <div id="imageModalgame" class="modalgame" style="display: none;">
            <span id="closeModalgame" class="close">&times;</span>
            <img class="modal-content-game" id="modalImagegame">
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
                window.location.href = '/DeepImpact/exit.php';
            });

            $("button").click(function() {
                $(this).toggleClass("toggle");
            });

            const popupContentgame = document.getElementById('popup-content-game');

            function loadTutorialgame() {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '/DeepImpact/resources/views/tutorial.php', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        popupContentgame.innerHTML = xhr.responseText;

                        // tutorial.php内の画像クリック処理
                        const clickableImagegame = document.getElementById('clickableImage');
                        if (clickableImagegame) {
                            clickableImagegame.addEventListener('click', function() {
                                const modalgame = document.getElementById('imageModal');
                                const modalImagegame = document.getElementById('modalImage');
                                modalgame.style.display = 'flex'; // モーダルを表示
                                modalImagegame.src = this.src; // クリックした画像のsrcをモーダルに設定
                            });
                        }

                        // モーダルを閉じる処理
                        const closeModalgame = document.getElementById('closeModalgame');
                        const modalgame = document.getElementById('imageModalgame');
                        closeModalgame.addEventListener('click', function() {
                            modalgame.style.display = 'none'; // バツマークをクリックしてモーダルを閉じる
                        });

                        // モーダルの外側をクリックして閉じる
                        modalgame.addEventListener('click', function(e) {
                            if (e.target === modalSidebar) {
                                modalgame.style.display = 'none'; // 外側をクリックしてモーダルを閉じる
                            }
                        });

                    } else {
                        console.error("Error loading tutorial: " + xhr.status + " " + xhr.statusText);
                    }
                };
                xhr.onerror = function() {
                    console.error("Request failed.");
                };
                xhr.send();
            }


            // ルールボタンをクリックしたときにポップアップを表示し、チュートリアルを読み込む
            sidebarClickBtn.addEventListener('click', () => {
                sidebarPopupWrapper.style.display = "block";
                loadTutorialSidebar(); // コンテンツを動的に読み込む
            });
        </script>

        <?php
        // 表示するテキストをPHPで定義
        $text = "昔々、平和な国があり、その国は緑豊かな土地と、穏やかな人々に恵まれていました。しかし魔王が現れ軍勢を率いて国を支配しまし。魔王は強力な魔法が使え、心臓が３つあり、国は恐怖に包まれました。人々は魔王に立ち向かう勇者が現れるのを待ち望んでいました。
    そんな時、小さな町に住む<b>正義感の強い若い戦士</b>が立ち上がりました。";
        echo "<div class='story-card'>{$text}</div>";
        ?>

        <?php
        $conn->close();
        ?>

</body>

</html>