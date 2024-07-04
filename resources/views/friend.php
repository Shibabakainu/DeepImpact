<?php
session_start();
include 'db_connect.php'; // Include the database connection
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フレンド検索</title>
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
    <?php include 'header.php'; ?>
    <div class="container">
        <?php
        // Ensure the user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../login.php");
            exit;
        }

        // Get the user ID from the session
        $user_id = $_SESSION['user_id'];

        // Fetch the user's information
        $sql = "SELECT name FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            echo "<div class='friend-search'>";
            echo "<h2>フレンド検索</h2>";
            echo "<p><b>貴方の名前</b>: " . htmlspecialchars($user['name']) . "</p>";
            echo "<div class='search-box'>";
            echo "<form action='' method='post'>";
            echo "<label for='friend-name'><b>名前検索</b></label>";
            echo "<input type='text' id='friend-name' name='friend_name' placeholder='相手の名前を入力してください'>";
            echo "<button type='submit' class='search_button'>検索</button>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<p>ユーザーが見つかりません。</p>";
        }

        // Handle the search request and display results
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_name'])) {
            $friend_name = $_POST['friend_name'];

            // Search for the friend in the database
            $sql = "SELECT * FROM users WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $friend_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Display the search results
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
                // Display a message if no results are found
                echo "<p>指定された名前のユーザーは見つかりませんでした。</p>";
            }

            $stmt->close();
        }

        $conn->close();
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
