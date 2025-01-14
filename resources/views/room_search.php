<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ルーム検索</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/room_search.css">
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
            right: 130px; /* ベルアイコンの左側に配置 */
            top: 20px;
            z-index: 1000; /* 他の要素より前面に表示 */
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- メッセージボタン -->
    <!--<button onclick="window.location.href='/DeepImpact/resources/views/inbox.php'" class="message-button">メッセージ</button>-->

    <main>
        <div class="container">
            <label class="search_label" for="search">ルーム名で検索</label>
            <input type="search" id="search" name="q" oninput="searchRooms()" />
            <div class="room-list" id="room-list">
                <?php
                include 'db_connect.php';

                $sql = "SELECT rooms.room_name, rooms.current_players, rooms.max_players, rooms.status, users.name AS host_name
                        FROM rooms
                        JOIN users ON rooms.host_id = users.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // 各行のデータを出力
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="room" data-room-name="' . $row["room_name"] . '">';
                        echo '<div class="room-name">' . $row["room_name"] . '</div>';
                        echo '<div class="room-host">ホスト: ' . $row["host_name"] . '</div>';
                        echo '<div class="room-status">プレイヤー: ' . $row["current_players"] . '/' . $row["max_players"] . '</div>';
                        echo '<div class="room-progress ' . strtolower($row["status"]) . '">' . $row["status"] . '</div>';
                        echo '<button class="join-room">参加</button>';
                        echo '</div>';
                    }
                } else {
                    echo "<p style='color:#fff;'>ルームが見つかりません</p>";
                }
                $conn->close();
                ?>
            </div>
            <div class="buttons">
                <button class="return" onclick="location.href='index.php'">戻る</button>
            </div>
        </div>
    </main>

    <div id="password-popup" class="popup">
        <div class="popup-content">
            <h3>合言葉</h3>
            <input type="password" id="room-password" />
            <button id="submit-password">参加</button>
        </div>
    </div>

    <div id="game-in-progress-popup" class="popup">
        <h3>ゲーム中のため参加できません。</h3>
        <button id="close-game-popup">閉じる</button>
    </div>
    <div id="overlay-game-popup" class="overlay"></div>

    <div id="overlay" class="overlay"></div>

    <script>
        function searchRooms() {
            const query = document.getElementById('search').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'room_search_ajax.php?q=' + encodeURIComponent(query), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('room-list').innerHTML = xhr.responseText;
                    attachJoinEventListeners(); // room-list の更新後にイベントリスナーを再度アタッチする
                }
            };
            xhr.send();
        }

        function attachJoinEventListeners() {
            const joinButtons = document.querySelectorAll('.join-room');
            joinButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const roomDiv = this.closest('.room');
                    const roomStatus = roomDiv.querySelector('.room-progress').textContent.trim().toLowerCase();
                    const roomName = roomDiv.getAttribute('data-room-name');

                    if (roomStatus === 'in_game') {
                        // ゲーム中の場合、アラートの代わりにポップアップを表示
                        document.getElementById('game-in-progress-popup').style.display = 'block';
                        document.getElementById('overlay-game-popup').style.display = 'block';
                    } else {
                        // ゲーム中でない場合、パスワードポップアップを表示
                        document.getElementById('password-popup').style.display = 'block';
                        document.getElementById('overlay').style.display = 'block';

                        document.getElementById('submit-password').onclick = function() {
                            const password = document.getElementById('room-password').value;
                            fetch('password_room.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `room_name=${encodeURIComponent(roomName)}&password=${encodeURIComponent(password)}`
                                })
                                .then(response => response.text())
                                .then(data => {
                                    if (data === 'success') {
                                        joinRoom(roomName);
                                    } else {
                                        alert('パスワードが間違っています');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                        };
                    }
                });
            });

            document.getElementById('overlay').onclick = function() {
                document.getElementById('password-popup').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('room-password').value = ''; // パスワードフィールドをクリア
            };

            // ゲーム中のポップアップを閉じるためのイベントリスナー
            document.getElementById('close-game-popup').onclick = function() {
                document.getElementById('game-in-progress-popup').style.display = 'none';
                document.getElementById('overlay-game-popup').style.display = 'none';
            };

            document.getElementById('overlay-game-popup').onclick = function() {
                document.getElementById('game-in-progress-popup').style.display = 'none';
                document.getElementById('overlay-game-popup').style.display = 'none';
            };
        }

        function joinRoom(roomName) {
            fetch('join_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `room_name=${encodeURIComponent(roomName)}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        // ルーム詳細ページにリダイレクト
                        window.location.href = `room_detail.php?room=${encodeURIComponent(roomName)}`;
                    } else {
                        alert('ルームに参加できません: ' + data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            attachJoinEventListeners();
        });
    </script>

</body>

</html>
