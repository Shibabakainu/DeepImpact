<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>サインアップ</title>
    <style>
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
        .container label {
            font-weight: bolder;
        }
        .container input[type="text"],
        .container input[type="email"],
        .container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 3px;
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
        #profile_image_preview {
            max-width: 200px;
            max-height: 200px;
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            display: none;
        }

        #passwordMismatch {
            color: red;
            margin-top: 5px;
            display: none;
        }

        #customAlert {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #000;
            z-index: 1000;
            text-align: center;
        }

        #customAlert button {
            margin: 10px;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 500;
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>プロフィール設定</h1>
        <form id="profileForm" action="confirmation.php" method="post" enctype="multipart/form-data">
            <label for="profile_image">プロフィール画像:</label><br>
            <input type="file" accept=".jpg,.jpeg,.png,.gif" id="profile_image" name="profile_image" onchange="previewImage()"><br><br>
            <img id="profile_image_preview" src="#" alt="プロフィール画像プレビュー"><br>

            <label for="name">User Name:</label><br>
            <input type="text" id="name" name="name"><br><br>

            <label for="email">メールアドレス:</label><br>
            <input type="email" id="email" name="email"><br><br>

            <label for="password">パスワード:</label><br>
            <input type="password" id="password" name="password"><br><br>

            <label for="confirm_password">パスワード再入力:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" oninput="checkPasswordMatch()"><br>
            <div id="passwordMismatch">パスワードが一致しません</div><br>

            <input type="button" value="作成" onclick="showCustomAlert()">
        </form>
    </div>

    <div id="overlay"></div>
    <div id="customAlert">
        <p>この内容で作成しますか？</p>
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
