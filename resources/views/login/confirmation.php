<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール確認</title>
</head>

<body>
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
            // Get the last inserted user ID
            $user_id = $stmt->insert_id;
            // Redirect to the profile page with user ID
            header("Location: ../login/profile.php?id=$user_id");
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
