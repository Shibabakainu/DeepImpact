<?php
session_start();
include 'db_connect.php'; // Include the database connection
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サブスクリプション登録</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/subscription.css">
</head>

<body>
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
        ?>

        <div class="subscription-box">
            <h2>サブスクリプション登録</h2>
            <form action="/DeepImpact/resources/views/index.php" method="post">
                <div class="input-group">
                    <label for="user_id">ユーザーID</label>
                    <!-- ユーザーIDは表示のみで編集不可 -->
                    <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                </div>
                <div class="input-group">
                    <label for="amount">金額</label>
                    <div class="amount-box">
                        <button type="button" id="minus-btn">-</button>
                        <input type="number" id="amount" name="amount" value="10000" min="0">
                        <button type="button" id="plus-btn">+</button>
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" class="back-btn" onclick="window.location.href='/DeepImpact/resources/views/index.php'">戻る</button>
                    <button type="submit" class="submit-btn">登録</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const minusBtn = document.getElementById('minus-btn');
        const plusBtn = document.getElementById('plus-btn');
        const amountInput = document.getElementById('amount');

        minusBtn.addEventListener('click', function() {
            let currentAmount = parseInt(amountInput.value);
            if (currentAmount >= 10000) {
                amountInput.value = currentAmount - 10000;
            } else {
                amountInput.value = 0;
            }
        });

        plusBtn.addEventListener('click', function() {
            let currentAmount = parseInt(amountInput.value);
            amountInput.value = currentAmount + 10000;
        });
    </script>
</body>

</html>