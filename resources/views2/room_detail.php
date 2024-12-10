<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データベースからユーザー名を取得
$loggedInUser = 'ゲスト';
if ($user_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $loggedInUser = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        }
        $stmt->close();
    }
}

//isset($_GET['setting']) ? htmlspecialchars($_GET['setting'], ENT_QUOTES, 'UTF-8') : '';
// クエリパラメータからデータを取得
$setting = 'mjmj2';
echo $setting;
$room = isset($_GET['room']) ? htmlspecialchars($_GET['room'], ENT_QUOTES, 'UTF-8') : '';
echo $room;

// ルーム情報を取得'
$sql_room_info = "SELECT room_name, host_id, current_players FROM rooms WHERE room_id = ?";
$stmt_room_info = $conn->prepare($sql_room_info);
if ($stmt_room_info) {
    $stmt_room_info->bind_param("s", $room);
    $stmt_room_info->execute();
    $stmt_room_info->bind_result($room_name, $host_id, $people);
    $stmt_room_info->fetch();
    $stmt_room_info->close();
} else {
    echo "ルーム情報の取得エラー: " . $conn->error;
}

// ホスト名を取得
$host_name = 'ホスト';
if ($host_id) {
    $stmt_host = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($stmt_host) {
        $stmt_host->bind_param("i", $host_id);
        $stmt_host->execute();
        $stmt_host->bind_result($host_name_result);
        if ($stmt_host->fetch()) {
            $host_name = htmlspecialchars($host_name_result, ENT_QUOTES, 'UTF-8');
        }
        $stmt_host->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/room_detail.css">
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
</head>

<body>
    <main>
        <div class="container">
            <h2>ルーム名: <?php echo $room; ?></h2>
            <p>合言葉: <?php echo $setting; ?></p>
            <ul class="player-list" id="playerList">
                <li class="host"><span class="host-label">ホスト</span> <?php echo $host_name; ?></li>
                <?php
                // プレイヤーリストを動的に生成
                $sql_players = "SELECT u.name FROM users u JOIN room_players rp ON u.id = rp.user_id WHERE rp.room_id = ? AND u.id != ? ORDER BY rp.joined_at";
                $stmt_players = $conn->prepare($sql_players);
                if ($stmt_players) {
                    $stmt_players->bind_param("ii", $room, $host_id);
                    $stmt_players->execute();
                    $stmt_players->bind_result($player_name);

                    while ($stmt_players->fetch()) {
                        echo "<li class='player'>" . htmlspecialchars($player_name, ENT_QUOTES, 'UTF-8') . "</li>";
                    }

                    $stmt_players->close();
                } else {
                    echo "プレイヤーリストの取得エラー: " . $conn->error;
                }

                // 空のプレイヤースロット
                for ($i = $people + 1; $i <= 6; $i++) {
                    echo "<li class='player empty'></li>";
                }
                ?>
            </ul>


            <li id="player"></li>
            <div class="buttons">
                <button class="leave-room" data-room-id="<?php echo $room; ?>">ルームを退出</button>
                <button class="create" data-room-id="<?php echo $room; ?>">物語を作る</button>
            </div>
        </div>
        <ul id='player-list'></ul>
    </main>
    <script>
        const socket = io('http://192.168.3.79:8080');

        function updatePlayerList(players) {
            playerList.innerHTML = '';
            players.forEach(player => {
                const li = document.createElement('li');
                li.textContent = player.name;
                playerList.appendChild(li);
            });
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            let peopleCount = <?php echo $people; ?>;
            const playerList = document.getElementById('player-list');

            const roomId = '<?php echo $room_id; ?>';
            const userName = '<?php echo $loggedInUser; ?>';
            const userId = '<?php echo json_encode($_SESSION['user_id']) ?>';

            console.log('username:', userName);
            console.log('roomId:', roomId);

            socket.on('connect', () => {
                console.log(socket.id);
                if (userId) {
                    socket.emit('reconnectWithUserId', {
                        userId
                    });
                    console.log('Reconnected with User ID:', userId);
                } else {
                    console.warn('user ID is not set');
                }
            });

            socket.on('connect_error', (error) => {
                console.log(error);
            });

            socket.on('playerjoined', (data) => {
                console.log('jusin', data.players);
                updatePlayerList(data.players);
            });

            socket.on('playerleft', (data) => {
                updatePlayerList(data.players);
            });

            playerList.addEventListener('click', (e) => {
                if (e.target && e.target.matches('li.player.empty')) {
                    if (peopleCount < 6) {
                        peopleCount++;
                        e.target.textContent = `プレイヤー${peopleCount}`;
                        e.target.classList.remove('empty');

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                } else if (e.target && e.target.matches('li.player')) {
                    if (peopleCount > 2) {
                        e.target.textContent = '';
                        e.target.classList.add('empty');
                        peopleCount--;

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                }
            });


            document.querySelector('.leave-room').addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                const userName = '<?php echo $loggedInUser; ?>';
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
                window.location.href = 'room_search.php';
            });



            document.querySelector('.create').addEventListener('click', function() {

                const roomId = this.getAttribute('data-room-id');
                fetch('update_room_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `room_id=${encodeURIComponent(roomId)}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('success')) {
                            window.location.href = 'game.php?room_id=' + encodeURIComponent(roomId); // Redirect to game.php with room_id
                        } else {
                            alert('エラー: ' + data);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    </script>
</body>

</html><?php
        session_start();
        include 'db_connect.php';

        // セッションからユーザーIDを取得
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // データベースからユーザー名を取得
        $loggedInUser = 'ゲスト';
        if ($user_id) {
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($name);
                if ($stmt->fetch()) {
                    $loggedInUser = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                }
                $stmt->close();
            }
        }

        // クエリパラメータからデータを取得
        $setting = isset($_GET['setting']) ? htmlspecialchars($_GET['setting'], ENT_QUOTES, 'UTF-8') : '';
        $room = isset($_GET['room']) ? htmlspecialchars($_GET['room'], ENT_QUOTES, 'UTF-8') : '';

        // ルーム情報を取得
        $sql_room_info = "SELECT room_id, host_id, current_players FROM rooms WHERE room_name = ?";
        $stmt_room_info = $conn->prepare($sql_room_info);
        if ($stmt_room_info) {
            $stmt_room_info->bind_param("s", $room);
            $stmt_room_info->execute();
            $stmt_room_info->bind_result($room_id, $host_id, $people);
            $stmt_room_info->fetch();
            $stmt_room_info->close();
        } else {
            echo "ルーム情報の取得エラー: " . $conn->error;
        }

        // ホスト名を取得
        $host_name = 'ホスト';
        if ($host_id) {
            $stmt_host = $conn->prepare("SELECT name FROM users WHERE id = ?");
            if ($stmt_host) {
                $stmt_host->bind_param("i", $host_id);
                $stmt_host->execute();
                $stmt_host->bind_result($host_name_result);
                if ($stmt_host->fetch()) {
                    $host_name = htmlspecialchars($host_name_result, ENT_QUOTES, 'UTF-8');
                }
                $stmt_host->close();
            }
        }
        ?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/room_detail.css">
</head>

<body>
    <main>
        <div class="container">
            <h2>ルーム名: <?php echo $room; ?></h2>
            <p>合言葉: <?php echo $setting; ?></p>
            <ul class="player-list">
                <li class="host"><span class="host-label">ホスト</span> <?php echo $host_name; ?></li>
                <?php
                // プレイヤーリストを動的に生成
                $sql_players = "SELECT u.name FROM users u JOIN room_players rp ON u.id = rp.user_id WHERE rp.room_id = ? AND u.id != ? ORDER BY rp.joined_at";
                $stmt_players = $conn->prepare($sql_players);
                if ($stmt_players) {
                    $stmt_players->bind_param("ii", $room_id, $host_id);
                    $stmt_players->execute();
                    $stmt_players->bind_result($player_name);

                    while ($stmt_players->fetch()) {
                        echo "<li class='player'>" . htmlspecialchars($player_name, ENT_QUOTES, 'UTF-8') . "</li>";
                    }

                    $stmt_players->close();
                } else {
                    echo "プレイヤーリストの取得エラー: " . $conn->error;
                }

                // 空のプレイヤースロット
                for ($i = $people + 1; $i <= 6; $i++) {
                    echo "<li class='player empty'></li>";
                }
                ?>
            </ul>
            <div class="buttons">
                <button class="leave-room" data-room-id="<?php echo $room_id; ?>">ルームを退出</button>
                <button class="game2" data-room-id="<?php echo $room_id; ?>">物語を作る</button>
                <button class="game3" data-room-id="<?php echo $room_id; ?>">物語を作る3</button>
            </div>
        </div>
    </main>
    <script>
        /*document.addEventListener('DOMContentLoaded', (event) => {
            let peopleCount = <?php echo $people; ?>;
            const playerList = document.querySelector('.player-list');

            playerList.addEventListener('click', (e) => {
                if (e.target && e.target.matches('li.player.empty')) {
                    if (peopleCount < 6) {
                        peopleCount++;
                        e.target.textContent = `プレイヤー${peopleCount}`;
                        e.target.classList.remove('empty');

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                } else if (e.target && e.target.matches('li.player')) {
                    if (peopleCount > 2) {
                        e.target.textContent = '';
                        e.target.classList.add('empty');
                        peopleCount--;

                        // データベースのプレイヤー数を更新
                        fetch('update_player_count.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `room_name=<?php echo $room; ?>&current_players=${peopleCount}`
                        });
                    }
                }
            });
        });*/

        document.querySelector('.leave-room').addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');

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

        document.querySelector('.game2').addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');

            fetch('update_room_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `room_id=${encodeURIComponent(roomId)}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        window.location.href = 'game2.php?room_id=' + encodeURIComponent(roomId); // Redirect to game2.php with room_id
                    } else {
                        alert('エラー: ' + data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        document.querySelector('.game3').addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');

            fetch('update_room_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `room_id=${encodeURIComponent(roomId)}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        window.location.href = 'game3.php?room_id=' + encodeURIComponent(roomId); // Redirect to game2.php with room_id
                    } else {
                        alert('エラー: ' + data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>

</body>

</html>