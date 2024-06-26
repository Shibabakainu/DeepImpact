<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール確認</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('/deepimpact/images/art2.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            /* 画面全体の高さを指定 */
            overflow: hidden;
            /* スクロールを無効にする */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            width: 80%;
            padding: 50px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .notice {
            color: #fff;
            font-size: 5rem;
            font-weight: bolder;
        }           
        .container button {
            width: 50%;
            padding: 10px;
            box-sizing: border-box;
            border: none;
            border-radius: 3px;
            background-color:blue;
            color: #fff;
            cursor: pointer;
            font-size: 2rem;
            font-weight: bolder;
        }
        .container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
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
            echo "<div class='notice'>このメールはすでに登録されています。別のメールアドレスを使用してください。</div>";
            echo "<button onclick=\"location.href='signup.php'\">戻る</button>";
        } else {
            // Check if name already exists
            $checkNameSql = "SELECT id FROM users WHERE name = ?";
            $stmt = $conn->prepare($checkNameSql);
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Name already exists
                echo "<div class='notice'>この名前はすでに使われています。別の名前を使用してください。</div>";
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
                    // Get the last inserted user ID
                    $user_id = $stmt->insert_id;
                    // Redirect to the profile page with user ID
                    header("Location: ../login/login.php?id=$user_id");
                    exit;
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        }

        $stmt->close();
    }

    $conn->close();
    ?>
    </div>
</body>

</html>
