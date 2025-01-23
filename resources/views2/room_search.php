<!DOCTYPE html>
<?php
session_start();
?>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ルーム検索</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/room_search.css">
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
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
            /* ベルアイコンの左側に配置 */
            top: 20px;
            z-index: 1000;
            /* 他の要素より前面に表示 */
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- メッセージボタン -->
    <!--<button onclick="window.location.href='/DeepImpact/resources/views/inbox.php'" class="message-button">メッセージ</button>-->

    <audio autoplay loop>
        <source src="/DeepImpact/bgm/sekiranun.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio>
    <main>
        <div class="container">
            <label class="search_label" for="search">ルーム名で検索</label>
            <input type="search" id="search" name="q" oninput="searchRooms()" />
            <div class="room-list" id="room-list">
                <?php
                include 'db_connect.php';

                $sql = "SELECT rooms.room_id, rooms.room_name, rooms.current_players, rooms.max_players, rooms.status, rooms.host_id, users.name AS host_name
                FROM rooms
                JOIN users ON rooms.host_id = users.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // 各行のデータを出力
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="room" data-room-name="' . $row["room_name"] . '">';
                        echo '<input type="hidden" class="room_id" value="' . $row["room_id"] . '">';
                        echo '<input type="hidden" class="host_id" value="' . $row["host_id"] . '">';
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
        //const socket = io('http://192.168.3.79:8080');
        const socket = io('https://storyteller.help:8080');

        const userId = '<?php echo json_encode($_SESSION['user_id']) ?>';
        const userName = '<?php echo json_encode($_SESSION['user_name']) ?>';
        socket.on('connect', () => {
            console.log(socket.id);
        });
        socket.on('connect_error', (error) => {
            console.log(error);
        });
        document.addEventListener('DOMContentLoaded', (event) => {

            function getQueryParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }

            const roomId = getQueryParam('room_id');
            console.log(roomId);

            let peopleCount = '<?php echo $people; ?>';
        });

        function searchRooms() {
            const query = document.getElementById('search').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'room_search_ajax.php?q=' + encodeURIComponent(query), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('room-list').innerHTML = xhr.responseText;
                    attachJoinEventListeners(); // Reattach event listeners after updating room-list
                }
            };
            xhr.send();
        }

        function attachJoinEventListeners() {
            const joinButtons = document.querySelectorAll('.join-room');
            joinButtons.forEach(button => {
                button.addEventListener('click', function() {
                    console.log('join button clicked');
                    const roomDiv = this.closest('.room');
                    const roomName = roomDiv.getAttribute('data-room-name');
                    const roomId = roomDiv.querySelector('.room_id').value;
                    const hostId = roomDiv.querySelector('.host_id').value;
                    console.log(hostId);
                    console.log(roomId);
                    document.getElementById('password-popup').style.display = 'block';
                    document.getElementById('overlay').style.display = 'block';

                    document.getElementById('submit-password').onclick = function() {
                        const passwordInput = document.getElementById('room-password');
                        const password = passwordInput.value;

                        socket.emit('joinRoom', {
                            roomId,
                            userId: userId,
                            userName: userName,
                            password,
                            hostId,
                        });

                        console.log('password submitted', password);
                        document.getElementById('password-popup').style.display = 'none';
                        document.getElementById('overlay').style.display = 'none';
                        document.getElementById('room-password').value = '';
                    };
                });
            });
        }

        socket.on('updatePlayerList', (data) => {
            console.log('update', data.players);
        });

        socket.on('joinRoomSuccess', (data) => {
            console.log(data);
            window.location.href = `room_detail.php?room=${encodeURIComponent(data.roomId)}`;
        });

        socket.on('joinRoomError', (data) => {
            alert(data.message);
        })

        document.addEventListener('DOMContentLoaded', () => {
            attachJoinEventListeners();
        });
    </script>

</body>

</html>