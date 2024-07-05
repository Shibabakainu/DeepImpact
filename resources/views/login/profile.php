<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>プロフィール</title>
        <link rel="stylesheet" href="/DeepImpact/resources/css/profile.css">
    </head>
    <body>
        <?php include '../header.php'; ?>
        <div class="container">
        <?php
            include '../db_connect.php'; // Include the database connection script

             // Determine the user ID to display
            if (isset($_GET['id'])) {
                $user_id = $_GET['id'];
            } elseif (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            } else {
                echo "No user ID specified.";
                exit; // Stop further execution if no user ID is available
            }

            // Fetch user data
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Display user data
                echo "<h1><font size=7>" . htmlspecialchars($user['name']) . "</font></h1>";
                echo "<img src='/DeepImpact/resources/views/login/profileicon/" . htmlspecialchars($user['profile_image']) . "' alt='Profile Picture' class='profile_image'>";
                echo "<p><font size=5><b>メール</b>: " . htmlspecialchars($user['email']) . "</font></p>";
                echo "<p><font size=5><b>参加時点</b>: <font color=grey>" . htmlspecialchars($user['created_at']) . "</font></font></p>";
            } else {
                echo "User not found.";
            }
            $stmt->close();
            $conn->close();
            

            //編集ボタンをユーザーのデータの下に配置
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            echo "<a href='profile_edit.php' class='edit_button'>編集</a>";
            }
            ?>
        </div>
    </body>
</html>