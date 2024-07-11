<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ルーム検索</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/room_search.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <label class="search_label" for="search">ルーム名で検索</label>
            <input type="search" id="search" name="q" oninput="searchRooms()" />
            <div class="room-list" id="room-list">
                <?php
                include 'db_connect.php';

                $sql = "SELECT room_name, current_players, max_players, status FROM rooms";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // 各行のデータを出力
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="room" data-room-name="' . $row["room_name"] . '">';
                        echo '<div class="room-name">' . $row["room_name"] . '</div>';
                        echo '<div class="room-status">プレイヤー: ' . $row["current_players"] . '/' . $row["max_players"] . '</div>';
                        echo '<div class="room-progress ' . strtolower($row["status"]) . '">' . $row["status"] . '</div>';
                        echo '<button class="join-room">参加</button>';
                        echo '</div>';
                    }
                } else {
                    echo "ルームが見つかりません";
                }
                $conn->close();
                ?>
            </div>
            <div class="buttons">
                <button class="return" onclick="location.href='index.php'">戻る</button>
            </div>
        </div>
    </main>
    <script>
        function searchRooms() {
            const query = document.getElementById('search').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'room_search_ajax.php?q=' + encodeURIComponent(query), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('room-list').innerHTML = xhr.responseText;
                    attachJoinEventListeners();  // Reattach event listeners after updating room-list
                }
            };
            xhr.send();
        }

        function attachJoinEventListeners() {
            const joinButtons = document.querySelectorAll('.join-room');
            joinButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const roomDiv = this.closest('.room');
                    const roomName = roomDiv.getAttribute('data-room-name');
                    
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
                            // Redirect to the room detail page
                            window.location.href = `room_detail.php?room=${encodeURIComponent(roomName)}`;
                        } else {
                            alert('ルームに参加できません: ' + data);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            attachJoinEventListeners();
        });
    </script>
</body>

</html>