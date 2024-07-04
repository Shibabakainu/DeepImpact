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
            <label class="search_label" for="movie">ルーム名で検索</label>
            <input type="search" id="search" name="q" oninput="searchRooms()" />
            <div class="room-list" id="room-list">
                <?php
                include 'db_connect.php';

                $sql = "SELECT room_name, current_players, status FROM rooms";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // 各行のデータを出力
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="room">';
                        echo '<div class="room-name">' . $row["room_name"] . '</div>';
                        echo '<div class="room-status">プレイヤー: ' . $row["current_players"] . '/6</div>';
                        echo '<div class="room-progress ' . strtolower($row["status"]) . '">' . $row["status"] . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "ルームが見つかりません";
                }
                $conn->close();
                ?>
            </div>
            <div class="buttons">
                <button class="create" onclick="location.href='room_create.php'">戻る</button>
                <button class="create" onclick="location.href='#'">参加</button>
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
                }
            };
            xhr.send();
        }
    </script>
</body>

</html>