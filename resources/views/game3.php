<?php
session_start();
include 'db_connect.php';
include 'game_functions.php';

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
    WHERE rc.room_id = ? AND rp.user_id = ? AND rc.player_position = ?";

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

// ポップアップ表示の条件
$shouldShowPopup = true; // 必要に応じて条件を設定してください
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>game</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/game2.css">
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

    <!--こうかおん  てか無理かも～できへん助けてなんで鳴らへんねんおかしいやん
    無理よ～一回だけ鳴るようになったよ-->
    <audio id="hoverSound" src="/DeepImpact/bgm/03_ぷい.mp3"></audio>
    <script type="text/javascript">
        const hoverSound = new Audio('/DeepImpact/bgm/03_ぷい.mp3')
        hoverSound.preload = 'auto';
        $(document).on('mouseenter', '.card', function() {
            hoverSound.currentTime = 0; // 効果音をリセットして最初から再生
            hoverSound.play().catch(error => console.error("ホバーサウンド再生に失敗:", error));
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
    <script>
        window.onload = function() {
            // Automatically check if there are already drawn cards
            updateDrawnCards(); // Call function to update drawn cards display
            var bgm = document.getElementById('bgm');
        };

        // Function to update drawn cards (on-hand) and vote area on load
        function updateDrawnCards() {
            // Fetch drawn cards from the server
            $.ajax({
                url: 'get_drawn_cards.php', // Create this script to retrieve drawn cards for the current user
                method: 'GET',
                data: {
                    room_id: roomId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update on-hand (unselected) cards
                        $('#drawed-card-area').empty(); // Clear existing cards
                        response.cards_unselected.forEach(function(card) {
                            $('#drawed-card-area').append(
                                '<div class="card" data-room-card-id="' + card.room_card_id + '">' +
                                '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                                '</div>'
                            );
                        });

                        // Update vote area with selected cards
                        $('#vote-area').empty(); // Clear existing cards
                        response.cards_selected.forEach(function(card) {
                            $('#vote-area').append(
                                '<div class="selected-card" data-room-card-id="' + card.room_card_id + '">' +
                                '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                                '</div>'
                            );
                        });
                    } else {
                        console.error('Failed to retrieve drawn cards: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error retrieving drawn cards: ' + textStatus + ' ' + errorThrown);
                }
            });
        }
    </script>
    <!-- Show player's hand -->
    <div class="container">
        <div class="onhand">
            <div class="draw" id="draw">
                <button id="draw-cards">カードをドロー</button>
            </div>
            <!-- Popup message element -->
            <div id="popup-message"></div>

            <script>
                document.getElementById("draw-cards").addEventListener("click", function() {
                    this.style.display = "none"; // ボタンを非表示にする
                });
            </script>

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
        投票
    </div>

    <div class="turnPopup" id="turnPopup"></div>

    <script type="text/javascript">
        // URLからroom_idを取得する関数
        function getRoomIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('room_id');
        }

        const roomId = getRoomIdFromUrl(); // URLからroom_idを取得

        // Function to show popup and hide it after 2 seconds
        function showPopup(message) {
            $('#popup-message').text(message).fadeIn();
            setTimeout(function() {
                $('#popup-message').fadeOut();
            }, 2000); // Hide after 2 seconds
        }

        function showTurnPopup(message) {
            $('#turnPopup').text(message).fadeIn();
            setTimeout(function() {
                $('#turnPopup').fadeOut();
            }, 5000); // Hide after 5 seconds
        }

        // Click event for drawing cards
        $(document).ready(function() {
            $("#draw-cards").click(function() {
                $.ajax({
                    url: 'draw_cards.php',
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $('#drawed-card-area').empty(); // 既存のカードをクリア

                        if (response.success) {
                            response.cards.forEach(function(card) {
                                $('#drawed-card-area').append(
                                    '<div class="card" data-room-card-id="' + card.room_card_id + '">' +
                                    '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                                    '</div>'
                                );
                            });
                        } else {
                            // エラーメッセージのポップアップを表示
                            showPopup(response.message);
                        }
                    },
                    error: function() {
                        showPopup("カードを引く際にエラーが発生しました。");
                    }
                });
            });

            // カード選択時のクリックイベント
            $(document).on("click", ".card", function() {
                var roomCardId = $(this).data("room-card-id");

                if (!roomCardId) {
                    showPopup("カードIDが見つかりません。");
                    return;
                }

                // Click event for selecting cards
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
                            showPopup(response.message);

                            // 選択済みクラスを追加
                            $(".card[data-room-card-id='" + roomCardId + "']").addClass('selected');

                            // 手札エリアから選択済みカードを削除
                            $(".card[data-room-card-id='" + roomCardId + "']").remove();

                            // 投票エリアを更新
                            updateVoteArea();
                        } else {
                            showPopup(response.message);
                        }
                    },
                    error: function() {
                        showPopup("カードの選択時にエラーが発生しました。");
                    }
                });
            });
            // 投票エリアを取得して更新する関数
            function updateVoteArea() {
                $.ajax({
                    url: 'get_votes.php',
                    method: 'GET',
                    data: {
                        room_id: roomId
                    },
                    dataType: 'html',
                    success: function(response) {
                        $('#vote-area').empty(); // 以前の内容をクリア
                        $('#vote-area').append(response); // 新しい内容を追加
                    },
                    error: function() {
                        showPopup('投票エリアの更新に失敗しました。');
                    }
                });
            }

            // 投票処理
            $(document).on('click', '.selected-card', function() {
                var roomCardId = $(this).data('room-card-id'); // room_card_idを取得

                if (!roomCardId) {
                    showPopup('Room Card IDが見つかりません！');
                    return; // roomCardIdが無効の場合は実行を停止
                }

                if (!roomId) {
                    showPopup('Room IDが見つかりません！');
                    return; // roomIdが無効の場合は実行を停止
                }

                $.ajax({
                    url: 'vote.php',
                    method: 'POST',
                    data: {
                        room_card_id: roomCardId, // room_card_idを送信
                        room_id: roomId // room_idを送信
                    },
                    dataType: 'json', // JSONレスポンスを期待
                    success: function(response) {
                        if (response.status === 'success') {
                            showPopup('投票が完了しました！');
                        } else {
                            showPopup('投票に失敗しました: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Response received:", xhr.responseText);
                        console.error("エラーが発生しました:", status, error);
                        showPopup('投票中にエラーが発生しました。再度お試しください。');
                    }
                });
            });
        });

        //アップデートしたターンを表示する
        // JavaScript function to display and update the current turn
        function displayTurn() {
            // Send AJAX request to get the current turn from the server
            fetch(`get_turn.php?room_id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("turnDisplay").innerText = "現在のターン： " + data.turn_number;
                    } else {
                        console.error("Failed to fetch turn information.");
                    }
                })
                .catch(error => console.error("Error:", error));
        }

        // Call displayTurn initially to show the current turn
        displayTurn();

        // Function to be called at the end of each turn to update the turn display
        function updateTurn() {
            displayTurn(); // Refresh the turn display
        }

        //投票エリアをクリアする
        function clearVoteArea() {
            $('#vote-area').empty();
        }

        //投票が終わった後の処理
        function pollVotingStatus() {
            setInterval(() => {
                $.ajax({
                    url: 'checkVotingStatus.php',
                    method: 'GET',
                    data: {
                        room_id: roomId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.game_over) {
                            alert(response.message);
                            // Additional logic for game over, like redirecting or disabling actions
                            // Disable voting and other game actions if needed
                        } else {
                            // Update the turn display and score as usual
                            updateTurn();
                            showTurnPopup("");

                            if (response.votingComplete) {
                                // If voting is complete, update the scoreboard
                                $('.scoreboard').html(response.scoreboard);
                                clearVoteArea();
                                alert("次のターンに進みましょう");
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error checking voting status: ", textStatus, errorThrown);
                        console.log("Response text: ", jqXHR.responseText); // Log detailed error response
                    }
                });
            }, 3000); // Poll every 3 seconds
        }

        // Call pollVotingStatus on page load to start polling
        pollVotingStatus();

        //リロードしてもスコアボード表示する
        $(document).ready(function() {
            // Fetch and display the scoreboard on page load
            function loadScoreboard() {
                $.ajax({
                    url: 'getScoreboard.php',
                    method: 'GET',
                    data: {
                        room_id: roomId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.scoreboard').html(response.scoreboard);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error loading scoreboard: ", textStatus, errorThrown);
                        console.log("Response text:", jqXHR.responseText); // Log the full response
                    }
                });
            }

            // Initial load on page refresh
            loadScoreboard();

            // Optional: reload the scoreboard every few seconds if you want it to auto-refresh
            setInterval(loadScoreboard, 5000);
        });
    </script>

    <div id="textbox">
        <div id="chatbox"></div>
        <input type="text" id="message" placeholder="Enter message..." />
        <button onclick="sendMessage()">Send</button>
    </div>

    <div class="player-list">
        <p>プレイヤーリスト:</p>
        <ul>
            <?php foreach ($players as $player): ?>
                <li><?php echo htmlspecialchars($player, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>

        <div id="turnDisplay">現在のターン： 1</div>

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
        document.querySelector('.other-btn').addEventListener('click', function() {

            fetch('leave_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `room_id=${encodeURIComponent(roomId)}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        window.location.href = 'room_search.php'; // Redirect to another page after leaving
                    } else {
                        alert('エラー: ' + data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });


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
    switch (getCurrentTurn($room_id)) {
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