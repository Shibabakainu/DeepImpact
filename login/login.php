
<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #BFF6C3;
        }
        .container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.3);
        }
        .container h2 {
            text-align: center;
        }
        .container input[type="text"],
        .container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .container input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            box-sizing: border-box;
            border: none;
            border-radius: 3px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .container input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="email" placeholder="メールアドレス" required>
            <input type="password" name="password" placeholder="パスワード" required>
            <input type="submit" value="ログイン">
        </form>
        <?php
        include '../db_connect.php'; // Include the database connection script

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Prepare and execute the SQL statement
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            if ($stmt === false) {
                die('Prepare failed: ' . $conn->error);
            }

            $stmt->bind_param("s", $email);
            if ($stmt->execute() === false) {
                die('Execute failed: ' . $stmt->error);
            }

            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashed_password);
                $stmt->fetch();

                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    // Password is correct, start a session
                    $_SESSION['user_id'] = $user_id;
                    header('Location: ../profile.php'); // Redirect to the profile page
                    exit;
                } else {
                    echo "Invalid email or password.";
                }
            } else {
                echo "Invalid email or password.";
            }

            $stmt->close();
        }
        $conn->close();
        ?>          
        <a href="signup.php">新規作成</a>
    </div>
</body>
</html>
