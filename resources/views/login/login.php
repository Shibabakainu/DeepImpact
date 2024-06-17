<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/login.css">
</head>
<body>
    <div class="container">
        <h1>LOGIN</h1>
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
