<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /*background-color: #CAF4FF;*/
            background-image: url("/images/art6.jpg");
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
        .container input[type="button"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: none;
            border-radius: 3px;
            background-color:#5AB2FF;
            color: #fff;
            cursor: pointer;
        }
        .container input[type="button"]:hover {
            background-color: #0056b3;
        }
        .separator {
        height: 1px;
        width: 90%;
        background-color: #ccc;
        margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="name" placeholder="username" required>
            <input type="password" name="password" placeholder="password" required>
            <input type="submit" value="ログイン">
            <div class="separator"></div>
            <input type="button" class="signup" onclick="location.href='signup.php'" value="新規作成">
        </form>
        <?php
        include '../db_connect.php'; // Include the database connection script

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $password = $_POST['password'];

            // Prepare and execute the SQL statement
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE name = ?");
            if ($stmt === false) {
                die('Prepare failed: ' . $conn->error);
            }

            $stmt->bind_param("s", $name);
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
                    header('Location: ../index.php'); // Redirect to the home page
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
    </div>
</body>
</html>
