<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/signup.css">
</head>
<body>
    <div class="header-container">
        <header>
            <img src="/deepimpact/images/sttera.png" alt="Story Teller" class="title-image">
        </header>
    </div>
    <div class="container">
        <h3>プロフィール編集</h3>
        <?php
        include '../db_connect.php'; // データベース接続スクリプトをインクルード

        // ユーザーIDを取得
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        } else {
            echo "<div class='notice'>ユーザーIDが指定されていません。</div>";
            exit;
        }

        // データベースからユーザーの情報を取得
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $currentName = htmlspecialchars($user['name']);
            $currentEmail = htmlspecialchars($user['email']);
            $currentProfileImage = !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '';

            $stmt->close();
            $conn->close();
        } else {
            echo "ユーザーが見つかりません。";
            exit;
        }
        ?>
        <form id="profileForm" action="edit_confirmation.php" method="post" enctype="multipart/form-data">
            <label for="profile_image">プロフィール画像:</label><br>
            <input type="file" accept=".jpg,.jpeg,.png,.gif" id="profile_image" name="profile_image" onchange="previewImage()"><br><br>
            <img id="profile_image_preview" src="<?php echo !empty($currentProfileImage) ? '/deepimpact/resources/views/login/profileicon/' . $currentProfileImage : '#' ?>" alt="プロフィール画像プレビュー" style="<?php echo !empty($currentProfileImage) ? 'display:block;' : 'display:none;' ?>"><br>

            <label for="name">User Name:</label><br>
            <input type="text" id="name" name="name" placeholder="<?php echo $currentName; ?>" value="<?php echo $currentName; ?>" required><br><br>

            <label for="email">メールアドレス:</label><br>
            <input type="email" id="email" name="email" placeholder="<?php echo $currentEmail; ?>" value="<?php echo $currentEmail; ?>" required><br><br>

            <label for="password">パスワード:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">パスワード再入力:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" oninput="checkPasswordMatch()" required><br>
            <div id="passwordMismatch">パスワードが一致しません</div><br>

            <input type="button" value="編集" onclick="showCustomAlert()">
        </form>
        <div class="separator"></div>
        <button class="return" onclick="location.href='/deepimpact/resources/views/login/profile.php'">戻る</button>
    </div>

    <div id="overlay"></div>
    <div id="customAlert">
        <p>この内容で編集しますか？</p>
        <button id="confirmButton">はい</button>
        <button id="cancelButton">いいえ</button>
    </div>

    <script>
        function previewImage() {
            var fileInput = document.getElementById('profile_image');
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profile_image_preview');
                output.src = reader.result;
                output.style.display = 'block';
            }
            reader.readAsDataURL(fileInput.files[0]);
        }

        function checkPasswordMatch() {
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;
            var passwordMismatch = document.getElementById("passwordMismatch");

            if (password !== confirm_password) {
                passwordMismatch.style.display = 'block';
            } else {
                passwordMismatch.style.display = 'none';
            }
        }

        function showCustomAlert() {
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;

            if (password !== confirm_password) {
                document.getElementById("passwordMismatch").style.display = 'block';
                return;
            }

            document.getElementById('overlay').style.display = 'block';
            document.getElementById('customAlert').style.display = 'block';
        }

        function closeCustomAlert() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('customAlert').style.display = 'none';
        }

        function confirmSubmission() {
            document.getElementById('profileForm').submit();
        }

        document.getElementById("confirmButton").addEventListener("click", confirmSubmission);
        document.getElementById("cancelButton").addEventListener("click", closeCustomAlert);
    </script>
</body>
</html>
