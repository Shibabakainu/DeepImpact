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
    <style>
        /* ポップアップスタイル */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            background-color: white;
            border: 2px solid #000;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            text-align: center;
        }

        /* OKボタンスタイル */
        .popup button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        /* 画面全体のオーバーレイ */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- ポップアップのオーバーレイ -->
        <div class="overlay"></div>

        <!-- ポップアップ本体 -->
        <div class="popup">
            <p>この機能は撤廃しました。</p><br>
            <p>今後とも我々DeepImpactをよろしくお願いします。</p>
            <button id="popup-ok-btn">OK</button>
        </div>

        <!-- サブスクリプション登録フォーム（ポップアップ表示後は使用不可になるため、実際には見えない） -->
        <div class="subscription-box">
            <h2>サブスクリプション登録</h2>
            <form action="process_subscription.php" method="post">
                <div class="input-group">
                    <label for="user_id">ユーザーID</label>
                    <input type="text" id="user_id" name="user_id" value="ユーザーID" readonly>
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
        // ページがロードされたときにポップアップを表示
        window.onload = function() {
            document.querySelector('.popup').style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
        }

        // OKボタンがクリックされたときにindex.phpへ遷移
        document.getElementById('popup-ok-btn').addEventListener('click', function() {
            window.location.href = '/DeepImpact/resources/views/index.php';
        });
    </script>
</body>

</html>