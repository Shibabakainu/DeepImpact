<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集確認</title>
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
    session_start();
    include '../db_connect.php'; // Include the database connection script

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_id = $_SESSION['user_id']; // Assuming you have stored the user's ID in session
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
        $profile_image = $_FILES['profile_image']['name'];
        $name = $_POST['name'];

        // Check if email already exists and is not the current user's email
        $checkEmailSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($checkEmailSql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            echo "<div class='notice'>このメールはすでに登録されています。別のメールアドレスを使用してください。</div>";
            echo "<button onclick=\"location.href='profile_edit.php'\">戻る</button>";
        } else {
            // Check if name already exists and is not the current user's name
            $checkNameSql = "SELECT id FROM users WHERE name = ? AND id != ?";
            $stmt = $conn->prepare($checkNameSql);
            $stmt->bind_param("si", $name, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Name already exists
                echo "<div class='notice'>この名前はすでに使われています。別の名前を使用してください。</div>";
                echo "<button onclick=\"location.href='profile_edit.php'\">戻る</button>";
            } else {
                // Move the uploaded profile image to the server if a new image is uploaded
                if (!empty($profile_image)) {
                    $target_dir = "profileicon/";
                    $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
                    move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
                } else {
                    // If no new image is uploaded, retain the old image
                    $profile_image = $_SESSION['profile_image'];
                }

                // Update user in the database
                $updateSql = "UPDATE users SET email = ?, password = ?, profile_image = ?, name = ? WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("ssssi", $email, $password, $profile_image, $name, $user_id);

                if ($stmt->execute()) {
                    // Update session variables
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $name;
                    $_SESSION['profile_image'] = $profile_image;

                    // Redirect to the profile page
                    header("Location: ../login/profile.php?id=$user_id");
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
