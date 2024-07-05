<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プレイヤーリスト</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/friend_search.css">
    <style>
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php
        session_start();
        include 'header.php'; 
    ?>

    <div class="container">
        <?php
            if (!isset($_SESSION['user_id'])) {
                header("Location: ../login.php"); // セッションにユーザーIDがない場合はログインページにリダイレクト
                exit;
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_name'])) {
                $friend_name = $_POST['friend_name'];

                // データベース接続
                include 'db_connect.php';

                // フレンドを検索するクエリ
                $sql = "SELECT * FROM users WHERE name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $friend_name);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // フレンドが見つかった場合の処理
                    $friend = $result->fetch_assoc();
                    echo "<div class='friend-info'>";
                    if (!empty($friend['profile_image'])) {
                        echo "<img src='/deepimpact/resources/views/login/profileicon/" . htmlspecialchars($friend['profile_image']) . "' alt='Profile Icon' class='profile-icon'>";
                    } else {
                        echo "<img src='/deepimpact/resources/views/login/profileicon/icon.png' alt='Default Icon' class='profile-icon'>";
                    }
                    echo "<p>" . htmlspecialchars($friend['name']) . "</p>";
                    echo "<form id='friend-request-form'>";
                    echo "<input type='hidden' name='friend_name' value='" . htmlspecialchars($friend['name']) . "'>";
                    echo "<button type='submit' class='add_friend_button'>フレンド申請</button>";
                    echo "</form>";
                    echo "</div>";
                } else {
                    // フレンドが見つからなかった場合の処理
                    echo "<p>指定された名前のユーザーは見つかりませんでした。</p>";
                }

                $stmt->close();
                $conn->close();
            } else {
                // POST リクエストでない場合や friend_name がセットされていない場合のエラーハンドリング
                echo "<p>検索に失敗しました。</p>";
            }
        ?>
        <div class="container2">
            <button class="return" onclick="location.href='friend.php'">戻る</button>
        </div>
    </div>
    <div id="popup" class="popup"></div>

    <script>
        document.addEventListener('submit', function(event) {
            if (event.target && event.target.id === 'friend-request-form') {
                event.preventDefault();

                const formData = new FormData(event.target);

                fetch('send_friend_request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const popup = document.getElementById('popup');
                    popup.textContent = data.message;
                    popup.style.display = 'block';

                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 3000);
                });
            }
        });
    </script>
</body>
</html>
