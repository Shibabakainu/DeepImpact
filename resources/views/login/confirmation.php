<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール確認</title>
    <style>
        img.profile_image {
            max-width: 200px; /* 最大幅を設定 */
            max-height: 200px; /* 最大高さを設定 */
            width: 200px; /* 幅を自動調整:auto */
            height: 200px; /* 高さを自動調整:auto */
            object-fit: cover; /* 画像を保持し、アスペクト比を維持しつつ要素全体にスケーリング */
            border-radius: 50%; /* 円形にする */
        }
    </style>
</head>

<body>
    <h1>プロフィール確認</h1>
    <?php
    include '../db_connect.php'; // Include the database connection script

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
        $profile_image = $_FILES['profile_image']['name'];
        $name = $_POST['name'];

        // Check if email already exists
        $checkEmailSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmailSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            echo "This email is already registered. Please use a different email.";
            echo "<button onclick=\"location.href='signup.php'\">戻る</button>";
        } else {
            // Move the uploaded profile image to the server
            $target_dir = "profileicon/";
            $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
            move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);

            // Insert user into the database
            $insertSql = "INSERT INTO users (email, password, profile_image, name)
                        VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ssss", $email, $password, $profile_image, $name);

            if ($stmt->execute()) {
            // Display user information
            echo "<!DOCTYPE html>";
            echo "<html lang='ja'>";
            echo "<head>";
            echo "<meta charset='UTF-8'>";
            echo "<title>Registration Successful</title>";
            echo "<style>
                    .profile_image { max-width: 150px; height: auto; }
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #CAF4FF;
                    }
                    .container {
                        width: 300px;
                        margin: 100px auto;
                        padding: 20px;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                        background-color: rgba(0, 0, 0, 0.3);
                    }
                    .container h1 {
                        text-align: center;
                    }
                  </style>";
            echo "</head>";
            echo "<body>";
            echo "<div class='container'>";
            echo "<h1>Registration successful!</h1>";
            echo "<p><strong>名前:</strong> " . htmlspecialchars($name) . "</p>";
            echo "<p><strong>メールアドレス:</strong> " . htmlspecialchars($email) . "</p>";
            echo "<p><strong>プロフィール画像:</strong><br><img src='" . htmlspecialchars($target_file) . "' class='profile_image'></p>";
            echo "<button onclick=\"location.href='../profile.php'\">新規作成</button>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
            // Get the last inserted user ID
            $user_id = $stmt->insert_id;
            // Redirect to the profile page with user ID
            header("Location: ../login/login.php?id=$user_id");
            exit;
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }

    $conn->close();
    ?>

</body>

</html>
