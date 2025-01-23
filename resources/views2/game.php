<?php
session_start();
include 'db_connect.php';

// セッションからユーザーIDを取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    // ユーザーIDがない場合は、適切なエラーメッセージを表示するかリダイレクト
    die("ログインが必要です。");
}

// URLのクエリパラメータからcurrent_playersを取得
$current_players = isset($_GET['current_players']) ? $_GET['current_players'] : 1; // 設定されていない場合は1をデフォルトとする
$roomId = isset($_GET['room']) ? $_GET['room'] : 115;

// プレイヤー情報を取得
$players = [];
$sql = "SELECT name FROM users";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row['name'];
    }
    $result->free();
} else {
    die("プレイヤーデータの取得に失敗しました: " . $conn->error);
}
$conn->close();

// ポップアップ表示の条件
$shouldShowPopup = true; // 必要に応じて条件を設定してください
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>game</title>
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>


    <style>
        body {
            /* サイトの外枠の余白を無効化 */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('/DeepImpact/images/art3.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            /* 画面全体の高さを指定 */
            overflow: hidden;
            /* スクロールを無効にする */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column-reverse;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed;
            bottom: 10px;
            width: 50%;
            padding: 25px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.3);
            text-align: center;
            max-height: 1000px;
            overflow-y: auto;
        }

        .container .card {
            /* ホバー時アニメーション */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            width: 8rem;
            background-color: white;
            box-shadow: 0 0 6px black;
            border: 0.3rem white solid;
            border-radius: 0.5rem;
        }

        /* ホバー時のカードの発光 */
        .container .card:hover {
            transform: translateY(-0.5rem) scale(1.1);
            box-shadow: 0 0 30px 10px rgba(232, 243, 26, 0.8), 0 0 40px 20px rgba(16, 131, 245, 0.8);
        }

        .container .card img {
            width: 8rem;
        }

        #draw-cards {
            width: 200px;
            height: 40px;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bolder;
            cursor: pointer;
            margin: 10px;
            border: none;
            transition: 0.5s ease;
        }

        #draw-cards:hover {
            background-color: #e77a34;
        }

        .vote-area {
            position: relative;
            width: 60%;
            height: 50%;
            padding: 20px;
            right: 45px;
            margin-bottom: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.3);
            text-align: center;
            max-height: 1000px;
            overflow-y: auto;
            display: flex;
            justify-content: center;
        }



        .vote-area .selected-card {
            width: 8rem;
            margin-right: 30px;
            background-color: white;
            box-shadow: 0 0 6px black;
            border: 0.3rem white solid;
            border-radius: 0.5rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* カードが追加されたときの発光アニメーション */
        .vote-area .selected-card:hover {
            transform: translateY(-0.5rem) scale(1.1);
            box-shadow: 0 0 30px 10px rgba(255, 255, 0, 0.8), 0 0 40px 20px rgba(0, 128, 255, 0.8);
        }

        .vote-area .selected-card .img {
            width: 8rem;
        }

        #scoreboard {
            width: 200px;
            height: 300px;
            right: 20px;
            position: fixed;
            bottom: 5px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0);
            font-family: Arial, sans-serif;
        }

        .scoreboard p {
            margin: 0;
            text-align: center;
        }

        .scoreboard ul {
            list-style: none;
            padding: 0;
            ;
        }

        .scoreboard li {
            margin: 5px 0;
            font-size: 16px;
        }

        @media screen and (max-width: 1500px) {
            body {
                height: 100vh;
                width: 100%;
            }
        }

        .menu- {
            position: fixed;
            top: 10px;
            right: 10px;
            text-align: right;
        }

        /*ポップアップ*/
        #menu-click-btn {
            z-index: 1000;
        }

        #menu-click-btn:hover {
            background-color: #e77a34;
        }

        #menu-popup-wrapper {
            background-color: rgba(0, 0, 0, .5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 994;
            animation: fadeIn 0.7s cubic-bezier(0.33, 1, 0.68, 1) forwards;
            justify-content: center;
            align-items: center;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        #popup-btn {
            text-align: center;
            background: white;
            position: relative;
            z-index: 995;
            border-radius: 5px;
        }

        .popup-btn:hover {
            background-color: #e77a34;
        }

        #back-btn {
            background-color: red;
            position: relative;
            z-index: 995;
            border-radius: 5px;
            cursor: pointer;
        }

        .nextturn {
            border-radius: 5px;
            flex-direction: column;
            gap: 20px;
            justify-content: center;
            align-items: center;
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .nextturn:hover {
            background-color: #e77a34;
        }

        .newgame {
            border-radius: 5px;
            flex-direction: column;
            gap: 20px;
            justify-content: center;
            align-items: center;
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .newgame:hover {
            background-color: lime;
        }

        .button_1 {
            border-radius: 5px;
            flex-direction: column;
            /* 縦方向に配置 */
            gap: 20px;
            /* ボタン間の間隔を広げる */
            justify-content: center;
            align-items: center;
            padding: 20px;
            /* ボタンコンテナの背景色 */
            border-radius: 5px;
            cursor: pointer;
        }


        .button_1 .popup-btn,
        .button_1 .back-btn {
            padding: 15px 30px;
            font-size: 1.5em;
            border-radius: 10px;
            cursor: pointer;
            transition: .3s;
        }


        .button_1 .back-btn:hover {
            background: rgb(250, 45, 45);
        }


        /* 2つ目のポップアップ */
        #second-popup-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 996;
            animation: fadeIn 0.7s cubic-bezier(0.33, 1, 0.68, 1) forwards;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
            /* 背景をぼかす */
        }

        .button_2 {

            flex-direction: column;
            gap: 20px;
            /* ボタン間の間隔を広げる */
            justify-content: center;
            align-items: center;
            padding: 30px;
            /* ボタンコンテナの背景色 */
            cursor: pointer;
        }

        .button_2 .popup-btn {
            position: relative;
            left: -10px;
            font-size: 1.8em;
            border-radius: 10px;
            cursor: pointer;
        }

        .button_2 .other-btn {
            position: relative;
            right: -30px;
            width: 150px;
            /* ボタンを30%大きく */
            font-size: 1.8em;
            background-color: rgb(250, 45, 45);
            border-radius: 10px;
            cursor: pointer;
        }

        /* 警告テキスト */
        .warning-text {
            position: relative;
            top: -50px;
            font-weight: bold;
            color: #000;
            -webkit-text-stroke: 1px #000000;
            text-shadow: 1px #000000;
            font-size: 2em;
            color: rgb(255, 0, 0);
            margin-bottom: 10px;
        }

        .hamburger-close {
            position: relative;
            width: 60px;
            height: 60px;
            margin: 15px;
            border: 2px solid white;
            border-radius: 50%;
            background-color: transparent;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            z-index: 1000;
        }

        .hamburger-close:focus {
            outline: none;
        }

        .hamburger-close:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        button.hamburger-close span {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 2px;
            background-color: white;
            transition: 0.3s ease-in-out;
            top: -4px;
            transition: 0.3s ease-in-out 0.25s;
        }

        button.hamburger-close span:before,
        button.hamburger-close span:after {
            content: "";
            position: absolute;
            background-color: white;
            transition: 0.3s ease-in-out;
            left: 0;
            width: 20px;
            height: 2px;
        }

        button.hamburger-close span:before {
            top: -5px;
        }

        button.hamburger-close span:after {
            bottom: -5px;
        }

        button.hamburger-close.toggle span {
            background-color: transparent;
            transition: 0.3s ease-in-out 0s;
        }

        button.hamburger-close.toggle span:before {
            top: 0;
            transform: rotate(45deg);
        }

        button.hamburger-close.toggle span:after {
            top: 0;
            transform: rotate(-45deg);
        }

        #rule-popup-wrapper {
            background-color: rgba(0, 0, 0, .5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 996;
            animation: fadeIn 0.7s cubic-bezier(0.33, 1, 0.68, 1) forwards;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        #rule-popup-inside {
            text-align: center;
            width: 100%;
            max-width: 60%;
            height: 60%;
            background: white;
            margin: 10% auto;
            padding: 20px;
            position: relative;
            z-index: 997;
            overflow: scroll;
            border-radius: 5px;
        }

        #rule-close {
            position: absolute;
            top: 0;
            right: 5px;
            cursor: pointer;
        }

        /*ルール*/
        ul {
            list-style: none
        }

        p {
            font-size: 24px;
            font-weight: bold;
        }

        .ssory-card {
            width: 20%;
            /* 縦長にするため幅を狭める */
            height: 60%;
            /* 縦長に見えるよう高さを設定 */
            position: fixed;
            bottom: 10px;
            /* 画面の下から10px */
            right: 10px;
            /* 画面の左から10px */
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: larger;
            overflow-y: auto;
            /* 内容が多い場合にスクロール可能に */
        }

        .toggle-button {
            position: fixed;
            bottom: 80px;
            right: 120px;
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: larger;
            cursor: pointer;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 990;
        }

        .toggle-button:hover {
            background-color: #0056b3;
        }

        .title {
            font-family: Arial, sans-serif;
            font-weight: bolder;
            font-size: 2rem;
        }

        .player-list {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }

        @keyframes slide-in {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(-100%);
            }
        }

        #chatbox {
            position: fixed;
            top: 50px;
            right: 10px;
            white-space: nowrap;
        }

        .message {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 5px;
            margin: 5px;
            border-radius: 3px;
            animation: slide-in 10s linear forwards;
        }

        #textbox {
            position: fixed;
            bottom: 10px;
            left: 10px;
            display: flex;
            align-items: center;
        }

        #textbox input[type="text"] {
            padding: 10px;
            border-radius: 5px 0 0 5px;
            border: 1px solid #ccc;
        }

        #textbox button {
            padding: 10px;
            border-radius: 0 5px 5px 0;
            border: 1px solid #ccc;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        #hand {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* スクロールバーの幅や高さを狭くしています */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        /* スクロールバーのつまみの色変更、および角を丸くしています。 */
        ::-webkit-scrollbar-thumb {
            background: rgb(132, 132, 132);
            border-radius: 5px;
        }

        .drawed-card-area {
            display: flex;
            justify-content: center;
        }

        /* vote-area内のカードのスタイル */
        .vote-area .selected-card {
            display: inline-block;
            width: 130px;
            /* カードの幅を固定 */
            height: 200px;
            /* カードの高さを固定（必要に応じて調整） */
            margin: 5px;
            /* カード間のスペース */
            box-sizing: border-box;
            /* パディングやボーダーをサイズに含める */
        }

        .vote-area .selected-card img {
            width: 100%;
            /* 画像をカードの幅に合わせる */
            height: auto;
            /* 画像の高さを自動で調整 */
        }

        .voter-info {
            display: flex;
            align-items: center;
            /*margin-top: 5px;*/
            /* Space between card and voters */
            flex-direction: column;
        }

        .voter-icon {
            width: 30px;
            /* Adjust size as needed */
            height: 30px;
            /* Adjust size as needed */
            border-radius: 50%;
            /* Make it circular */
            margin-right: 5px;
            /* Space between icon and name */
        }

        #volume-textarea-wrapper {
            background-color: rgba(0, 0, 0, .5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 996;
            animation: fadeIn 0.7s cubic-bezier(0.33, 1, 0.68, 1) forwards;

        }



        #volume-textarea-close {
            position: absolute;
            top: 0;
            right: 5px;
            cursor: pointer;
        }


        #volume-textarea-inside {
            background-image: url('/DeepImpact/images/aiueo.png');
            background-size: cover;
            /* 画像をコンテナ全体にフィットさせる */
            background-position: center;
            /* 背景画像を中央に配置 */
            text-align: center;
            width: 150%;
            max-width: 50%;
            height: 45%;
            margin: 10% auto;
            background-color: azure;
            padding: 20px;
            position: relative;
            z-index: 997;
            overflow: scroll;
            border-radius: 5px;
            font-family: 'Arial', sans-serif;
            /* モダンなフォント */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* ソフトな影を追加 */
        }




        /* ラベルのスタイル */
        label {
            display: block;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            color: #090404;
        }

        /* スライダーのスタイル */
        input[type="range"] {
            -webkit-appearance: none;
            /* デフォルトのスタイルをリセット */
            width: 60%;
            /* 幅を全体に広げる */
            height: 10px;
            background: linear-gradient(to right, #4caf50, #8bc34a);
            /* グラデーション背景 */
            border-radius: 5px;
            /* 角を丸くする */
            outline: none;
            transition: background 0.3s;
            /* 背景色の変更をスムーズに */
        }

        /* スライダーのつまみ（thumb）のスタイル */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            background: #4caf50;
            /* つまみの色 */
            border-radius: 50%;
            /* 丸い形状 */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* 軽い影 */
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            /* 色とサイズの変更をスムーズに */
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            background: #45a049;
            /* ホバー時に濃い緑色に */
            transform: scale(1.1);
            /* 少し大きくする */
        }

        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #4caf50;
            border-radius: 50%;
            cursor: pointer;
        }

        /* スライダーの上の値表示 */
        #bgm-volume-value,
        #sfx-volume-value {
            font-size: 12px;
            color: #777;
            margin-left: 10px;
        }



        .voter-name {
            font-size: 20px;
            /* Adjust font size as needed */
            font-weight: bold;
            color: white;
        }

        /* ボタンのデザインを定義します */
        .bgm-btn {
            position: absolute;
            /* 絶対位置を指定 */
            top: 100px;
            /* 上からの距離を設定 */
            right: 30px;
            /* 右からの距離を設定 */
            background-color: #4CAF50;
            /* ボタンの背景色（緑色） */
            color: white;
            /* 文字色（白） */
            border: none;
            /* ボタンの枠線なし */
            border-radius: 50%;
            /* ボタンを円形にする */
            width: 50px;
            /* ボタンの幅を50pxに設定 */
            height: 50px;
            /* ボタンの高さを50pxに設定 */
            font-size: 24px;
            /* ボタン内のアイコンの文字サイズ */
            cursor: pointer;
            /* ホバー時にポインタを指に変える */
            display: flex;
            /* フレックスボックスで配置 */
            align-items: center;
            /* ボタン内のアイコンを垂直方向に中央揃え */
            justify-content: center;
            /* ボタン内のアイコンを水平方向に中央揃え */
        }

        /*メッセージポップアップ*/
        /* Style for the popup message */
        #popup-message {
            display: none;
            /* Initially hidden */
            position: fixed;
            width: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            z-index: 1000;
        }

        #turnDisplay {
            color: #e77a34;
            font-size: 1.75rem;
            font-weight: bolder;
        }

        #story-card {
            width: 60%;
            position: fixed;
            top: 10px;
            left: 370px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: larger;
        }

        .winner-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            z-index: 999;
        }

        .winner-popup h2 {
            font-size: 36px;
            font-weight: bold;
        }

        .winner-popup h3 {
            font-size: 24px;
        }

        .winner-popup p {
            font-size: 18px;
        }

        button {
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 20px;
            cursor: pointer;
        }

        #message-box {
            position: fixed;
            /* 画面全体に固定表示 */
            bottom: 20px;
            /* 画面下から20px */
            left: 50%;
            /* 横中央 */
            transform: translateX(-50%);
            /* 中央揃え */
            background-color: rgba(0, 0, 0, 0.8);
            /* 半透明の背景 */
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            /* 初期は非表示 */
        }

        #message-box.hidden {
            display: none;
        }

        #toggle-story {
            position: relative;
            /* 必要なら absolute でもOK */
            z-index: 10;
            /* story-card より大きい値を指定 */
            background-color: #fff;
            /* 背景色を付けると目視しやすい */
            cursor: pointer;
            /* ボタンらしいカーソル */
            padding: 5px 10px;
            /* ボタンのサイズを適切に調整 */
            margin-top: 10px;
            /* 必要に応じて調整 */
        }

        #volume-btn {
            text-align: center;
            background: white;
            position: relative;
            z-index: 995;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.5em;
            margin: 20px 0px 0px;
        }
    </style>

    <script type="text/javascript">
        // Ensure it's hidden initially
        document.addEventListener("DOMContentLoaded", function() {
            var shouldShowPopup = <?php echo json_encode($shouldShowPopup); ?>;
            if (shouldShowPopup) {
                document.getElementById('menu-popup-wrapper').style.display = 'none';
            } else {
                document.getElementById('menu-popup-wrapper').style.display = 'flex';
            }
        });
    </script>
</head>

<body>

    <audio id="hoverSound" src="/DeepImpact/bgm/03_ぷい.mp3" preload="auto"></audio>
    <script type="text/javascript">
        // 効果音用のAudio要素を取得
        const hoverSound = document.getElementById('hoverSound');
        hoverSound.preload = 'auto';

        // カードにマウスを乗せたときの効果音再生
        $(document).on('mouseenter', '.card', function() {
            hoverSound.currentTime = 0; // 効果音をリセットして最初から再生
            hoverSound.play().catch(error => console.error("ホバーサウンド再生に失敗:", error));
        });

        // 効果音スライダーのイベントリスナーを追加
        document.getElementById('sfx-volume').addEventListener('input', function(event) {
            const volume = event.target.value / 100; // 0-100 の値を 0-1 に変換
            hoverSound.volume = volume; // 効果音の音量を設定
            document.getElementById('sfx-volume-value').innerText = `${event.target.value}%`; // 現在の値を表示
        });
    </script>
    <!-- ボタンを設置、クリックでBGMを再生/停止 -->
    <button id="bgm-toggle-btn" class="bgm-btn">
        <span id="bgm-icon">🔊</span>
    </button>

    <audio id="bgm" src="/DeepImpact/bgm/PerituneMaterial_Poema.mp3" preload="auto" loop autoplay>
        <!-- オーディオ要素：BGMを再生、ループ設定を有効化 -->
        <source src="/DeepImpact/b gm/PerituneMaterial_Poema.mp3" type="audio/mpeg">
    </audio>
    <script>
        // 最初のクリックでミュート解除 (Chrome制限対応)
        document.body.addEventListener('click', () => {
            bgm.muted = false;
            bgm.play().catch(console.error);
        }, {
            once: true
        }); // このイベントは一度だけ実行

        const context = new AudioContext();

        // Setup an audio graph with AudioNodes and schedule playback.

        // Resume AudioContext playback when user clicks a button on the page.
        document.querySelector('button').addEventListener('click', function() {
            context.resume().then(() => {
                console.log('AudioContext playback resumed successfully');
            });
        });

        // DOMの読み込みが完了したときに実行される処理
        document.addEventListener('DOMContentLoaded', function() {
            const bgm = document.getElementById('bgm');
            const bgmToggleBtn = document.getElementById('bgm-toggle-btn');
            const bgmIcon = document.getElementById('bgm-icon');
            let isPlaying = false;

            // ボタンがクリックされたときのイベントハンドラを定義
            bgmToggleBtn.addEventListener('click', function() {
                if (isPlaying) {
                    // 再生中ならBGMを一時停止
                    bgm.pause();
                    bgmIcon.textContent = '🔇'; // アイコンをミュートのものに変更
                } else {
                    // 停止中ならBGMを再生
                    bgm.play();
                    bgmIcon.textContent = '🔊'; // アイコンをスピーカーのものに変更
                }
                isPlaying = !isPlaying; // フラグを反転（再生⇔停止を切り替え）
            });

            // ユーザーがページを離れる前に音楽を停止する処理
            window.addEventListener('beforeunload', () => {
                bgm.pause(); // ページが閉じられる前にBGMを停止
            });

            // 1秒後にボタンを自動的にクリック
            setTimeout(function() {
                bgmToggleBtn.click(); // ここでボタンがクリックされる
            }, 2000); // 1000ミリ秒 = 1秒
        });
    </script>
    <div class="container">
        <div class="onhand">
            <div class="draw" id="draw">
            </div>
            <!-- Popup message element -->
            <div id="popup-message"></div>


            <div id="drawed-card-area" class="drawed-card-area">
            </div>
        </div>
    </div>
    <div class="vote-area" id="vote-area">
        <!-- Cards with selected 1 will be loaded here -->
    </div>

    <div class="title">
        投票
    </div>

    <div class="turnPopup" id="turnPopup"></div>
    <div id="textbox">
        <div id="chatbox"></div>
        <input type="text" id="message" placeholder="Enter message..." />
        <button onclick="sendMessage()">Send</button>
    </div>

    <div class="player-list">
        <p>プレイヤーリスト:</p>
        <div id='list'></div>

        <div id="turnDisplay">現在のターン： 1</div>


        <form method="POST">
            <input type="hidden" name="reset_game" value="1">
            <button class="newgame" type="submit">新しく始める</button>
        </form>
    </div>
    <div id="ResultArea"></div>


    <div id="story-card"></div>

    <div id="message-box" class="hidden">
        <p id="message-text"></p>
    </div>



    <div id="player-hand"></div>
    <div id="hand-section"></div>

    <div id="scoreboard">
        <p>スコアボード</p>
    </div>


    <div class="menu-">
        <div id="menu-popup-wrapper">
            <div class="button_1">
                <button class="back-btn">退出する</button>
                <button class="popup-btn" id="rule-click-btn">ルール</button>
                <button class="volume-btn" id="volume-btn">ボリューム</button>

                <div id="rule-popup-wrapper" style="display: none;">
                    <div id="rule-popup-inside">
                        <div class="text">
                            <div id="rule-close">X</div>
                            <p>※注意事項※</p>
                            <ul>
                                <li>ゲーム推奨プレイ人数は6人となっています。</li><br>
                                <li>あとは適当に追加</li>
                            </ul>
                            <p>ゲーム開始時</p>
                            <ul>
                                <li>各プレイヤーに5枚のカードを配ります。</li>
                            </ul>
                            <p>カードの提出</p>
                            <ul>
                                <li>物語を確認し、自分の手札から物語のフレーズに合うと思うカードを1枚選択し、待機します。</li><br>
                                <li>全てのプレイヤーが選び終えると、画面中央に選ばれたカードが表示されます。</li>
                            </ul>
                            <p>投票</p>
                            <ul>
                                <li>各プレイヤーは、物語のフレーズに1番あっていると思うカードを選び、投票することができます。</li><br>
                                <li>注意として、自身が提出したカードに投票することはできません。</li>
                            </ul>
                            <p>得点</p>
                            <ul>
                                <li>投票が入ったカードを出したプレイヤーは、投票1つにつき、+1点を獲得します。</li><br>
                                <li>1番票を集めたカードに、投票をしていた場合には投票者にも+1点を獲得します。</li>
                            </ul>
                            <p>ラウンド終了</p>
                            <ul>
                                <li>各プレイヤーは新しいカードを1枚手に入れ、手札が5枚に戻ります。</li>
                            </ul>
                            <p>ゲーム終了</p>
                            <ul>
                                <li>物語の決められたチャプター(ターン)が全て終えると、ゲーム終了です。</li><br>
                                <li>最も得点の多いプレイヤーの勝利となります。</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button data-action="Menu-Close" class="hamburger-close" id="menu-click-btn">
            <span></span>
        </button>
    </div>

    <div id="second-popup-wrapper">
        <div class="button_2">
            <p class="warning-text">本当に退出しますか？</p>
            <button class="popup-btn" id="second-popup-close">キャンセル</button>
            <button class="other-btn" id="exit-btn">退出</button>
        </div>
    </div>
    <!-- ボリューム買えるようにするよ -->
    <div id="volume-textarea-wrapper" style="display: none;">
        <div id="volume-textarea-inside">
            <div class="text">
                <div id="volume-textarea-close">X</div>
                <!-- 音量調節スライダー -->
                <label for="bgm-volume">BGM 音量: <span id="bgm-volume-value">100%</span></label>
                <input id="bgm-volume" type="range" min="0" max="100" value="100">
            </div>
            <div>
                <label for="sfx-volume">効果音 音量: <span id="sfx-volume-value">100%</span></label>
                <input id="sfx-volume" type="range" min="0" max="100" value="100">
            </div>
        </div>
    </div>

    <script>
        // 音量調節のスライダーをセットアップ
        document.getElementById('bgm-volume').addEventListener('input', function(event) {
            // スライダーの値を取得し、0-100 の範囲を 0-1 に変換
            const volume = event.target.value / 100;
            // BGMオーディオ要素を取得
            const bgm = document.getElementById('bgm');
            // 取得した値をBGMの音量に設定
            bgm.volume = volume;
            // 現在の音量をパーセンテージ形式で表示
            document.getElementById('bgm-volume-value').innerText = `${event.target.value}%`;
        });

        // 効果音の音量調節スライダーをセットアップ
        document.getElementById('sfx-volume').addEventListener('input', function(event) {
            // スライダーの値を取得し、0-100 の範囲を 0-1 に変換
            const volume = event.target.value / 100;
            // 効果音オーディオ要素を取得
            const hoverSound = document.getElementById('hoverSound');
            // 取得した値を効果音の音量に設定
            hoverSound.volume = volume;
            // 現在の音量をパーセンテージ形式で表示
            document.getElementById('sfx-volume-value').innerText = `${event.target.value}%`;
        });

        document.getElementById("volume-btn").addEventListener("click", function() {
            document.getElementById("volume-textarea-wrapper").style.display = "block";
        });

        document.getElementById("volume-textarea-close").addEventListener("click", function() {
            document.getElementById("volume-textarea-wrapper").style.display = "none";
        });
    </script>



    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        //const socket = io('http://192.168.3.79:8080');
        const socket = io('https://storyteller.help:8080');


        const sendChatBtn = document.getElementById('send-chat');
        const handList = document.getElementById('hand');
        const chatInput = document.getElementById('chat-input');
        const messagesList = document.getElementById('messages');

        let currentRoomId = '';
        let currentHand = [];
        let isFullStoryVisible = false;


        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        const roomId = getQueryParam('room_id');
        console.log(roomId);
        const userId = '<?php echo json_encode($_SESSION['user_id']) ?>';

        console.log(userId);

        socket.emit('startGame', {
            roomId: roomId
        });



        socket.on('connect', () => {
            console.log(socket.id);
            if (userId) {
                socket.emit('reconnectWithUserId', {
                    userId
                });
                console.log('Reconnected with User ID:', userId);
            } else {
                console.warn('user ID is not set');
            }
        });

        socket.on('playerJoined', (data) => {
            console.log(data);
            updatePlayerList(data.players);
        });



        socket.on("storyDisplay", (data) => {

            const {
                story
            } = data;
            console.log(story);
            displayStory(story);
        });

        function displayStory(story) {
            const storyDisplay = document.getElementById('story-card');

            if (storyDisplay) {
                // ストーリーの最初の3行だけを表示
                const storyPreview = story.split('\n').slice(0, 3).join('\n');
                storyDisplay.textContent = story;
            }
        }

        socket.on('disconnect', () => {
            console.log('Disconnected from the server');
        });

        document.addEventListener("DOMContentLoaded", function() {
            const drawCardBtn = document.getElementById('drawCard');
            if (drawCardBtn) {
                drawCardBtn.addEventListener('click', function() {
                    if (roomId) {
                        socket.emit('draw_card', {
                            room_id: roomId
                        });
                    } else {
                        console.error("no current room ID set");
                    }
                });
            }
        })
        socket.on('updatescore', (data) => {
            const players = data;
            const scoreList = document.getElementById('scoreboard');
            scoreList.innerHTML = '';
            players.forEach(player => {
                const li = document.createElement('li');
                li.textContent = `${player.name}:${player.score}`;
                scoreList.appendChild(li);
            })
        })

        socket.on('gameStarted', (data) => {
            console.log("kkkkkkkkkk");
            handList.innerHTML = '';
            let currentHand = data.deck.splice(0, 5); // 5枚の手札を受け取る
            currentHand.forEach(card => {
                const li = document.createElement('li');
                li.textContent = card;
                handList.appendChild(li);
            });
            updatePlayerList(data.roomId);
        });

        socket.on('update_hand', (data) => {
            handList.innerHTML = '';
            data.hand.forEach(card => {
                const li = document.createElement('li');
                li.textContent = card;
                handList.appendChild(li);
            });
        });

        socket.on("votingResults", (data) => {
            const {
                votes,
                roomId
            } = data;
            console.log('投票結果', votes);
            const ResultArea = document.getElementById('ResultArea');
            ResultArea.innerHTML = '';

            for (const cardId in votes) {
                const voteElement = document.createElement("div");
                voteElement.textContent = `カードID: ${cardId} 投票数: ${votes[cardId]}`;
                ResultArea.appendChild(voteElement);
            }

            socket.emit('nextTurn', {
                roomId
            });
        })


        socket.on('player_list', (data) => {
            if (!playersList) {
                console.error('playersList');
                return;
            }

            if (!Array.isArray(data.players)) {
                console.error('Invalid data format for player list:', data);
                return;
            }
            const playersList = document.getElementById('playersList');
            playersList.innerHTML = '';
            data.players.forEach(player => {
                const li = document.createElement('li');
                li.textContent = player;
                playersList.appendChild(li);
            });
            console.log('player list updateed:', data.players);
        });

        socket.on('chat_message', (data) => {
            const messages = document.getElementById('messages');
            messages.innerHTML += `<p>${data.message}</p>`;
        });



        socket.on('played_card', (data) => {
            const li = document.createElement('li');
            li.textContent = `${data.player} が ${data.card} をプレイしました`;
            messagesList.appendChild(li);
        });

        socket.on("updatePlayerList", (players) => {
            const playerListElement = document.getElementById("playerList");
            playerListElement.innerHTML = "";

            players.forEach(player => {
                const listItem = document.createElement("li");
                listItem.textContent = player;
                playerListElement.appendChild(listItem);
            })
        });

        socket.on('dealCards', (data) => {
            console.log('received cards', data);
            if (!data || !Array.isArray(data.cards)) {
                console.log('shine');
                return;
            }
            const cards = data.cards;
            const hand = document.getElementById('drawed-card-area');
            hand.innerHTML = ''; // 既存のカードをクリア

            cards.forEach((cardObj) => {
                const card = cardObj.card; // cardオブジェクトを取得
                const imaggePath = `../../images/${card.image}`;

                // カードに画像パスと名前が含まれている場合
                const cardImage = card.image; // 画像ファイルパス
                const cardName = card.name; // カード名

                if (!cardImage || !cardName) {
                    console.error('Invalid card data:', card);
                    return; // 画像または名前がない場合、カードをスキップ
                }

                const cardElement = document.createElement('div');
                cardElement.className = 'card';
                cardElement.dataset.cardId = cardObj.id;

                // カードの画像を表示
                const imgElement = document.createElement('img');
                imgElement.src = imaggePath;
                imgElement.className = 'card-image';

                // カードに画像と名前を追加
                cardElement.appendChild(imgElement);
                hand.appendChild(cardElement);

                cardElement.addEventListener('click', () => {
                    socket.emit('playCard', {
                        roomId: roomId,
                        cardId: cardObj.id
                    });
                });

            });
            console.log('dealCards complete');
        }); // サーバーからカードがプレイされた際の処理


        socket.on('turnStarted', (data) => {
            const {
                turn,
                players
            } = data;
            const turnDisplay = document.getElementById('turnDisplay');
            if (turnDisplay) {
                turnDisplay.textContent = `現在のターン:${turn}`;
            }

            const playerList = document.getElementById('player-list');
            if (playerList) {
                playerList.innerHTML = '';

                players.forEach((player) => {
                    const playerElement = document.createElement('div');
                    playerElement.classList.add('player-info');
                    playerElement.innerHTML =
                        `<p>User ID : ${player.userId}</p>
                    <p>Score: ${player.score}</p>`;
                    playerList.appendChild(playerElement);
                });
            }

            const currentPlayer = player.find((p) => p.hand !== null);
            const handContainer = document.getElementById('drawed-card-area');
            if (handconatiner && currentPlayer) {
                handconatiner.innerHTML = '';

                currentPlayer.hand.foreach((card) => {
                    const cardElement = document.createElement('div');
                    cardElement.classList.add('card');
                    cardElement.textContent = card;
                    handcontainer.appendChild(cardElement);
                })
            }
        })

        socket.on("nextRound", (data) => {
            const {
                message,
                round
            } = data;
            console.log(message);
            console.log(round);

            const vote_area = document.getElementById('vote-area');
            vote_area.innerHTML = '';
            updateRoundDisplay(round);
        })

        function updateRoundDisplay(round) {
            const roundDisplay = document.getElementById('turnDisplay');
            if (roundDisplay) {
                roundDisplay.textContent = `現在のターン: ${round}`;
            }
        }

        socket.on('updateVotes', (data) => {
            const {
                votes,
                cardId,
                player,
                userName
            } = data;
            for (const cardId in votes) {
                const voterList = votes[cardId];
                console.log(`card ${cardId} has votes from`, voterList);
            }
            console.log(`${userName}が${votes}に投票しました`);
        })

        socket.on('gameEnd', (data) => {
            const {
                message,
                winners,
                highestScore
            } = data;
            alert(message);
            displayWinner(winners, highestScore);
        });

        function displayWinner(winners, highestScore) {
            const winnerList = winners
                .map((winner) => `<p>${winner.name} - ${winner.score}点</p>`)
                .join('');

            const winnerPopup = document.createElement('div');
            winnerPopup.className = 'winner-popup';
            winnerPopup.innerHTML = `
            <div class="popup-content">
            <h2>ゲーム終了！</h2>
            <h3>勝者:</h3>
            ${winnerList}
            <p>最高スコア: ${highestScore}</p>
            <button id="exit-btn">次のゲームへ</button>
            </div>`;

            document.body.appendChild(winnerPopup);

            // ボタンの動作を追加
            document.getElementById('exit-btn').addEventListener('click', function() {
                socket.emit('leaveRoom', {
                    roomId: roomId
                });
                window.location.href = 'index.php';
            });
        }

        socket.on('cardPlayed', (data) => {
            console.log('Card played:', data);
            // プレイヤーIDとカード情報を取得
            const {
                playerId,
                card
            } = data;

            console.log(card);


            // プレイされたカードを表示する場所（例えば、場に出すカードを表示するエリア）
            const playedArea = document.getElementById('vote-area');
            const cardElement = document.createElement('div');
            cardElement.className = 'card played';
            cardElement.dataset.cardId = card.id;
            console.log(card.id);


            const imaggePath = `../../images/${card.card.image}`;

            console.log(imaggePath);
            // カードの画像を表示
            const imgElement = document.createElement('img');
            imgElement.src = imaggePath;
            imgElement.className = 'selected-card';

            // カードに画像と名前を追加
            cardElement.appendChild(imgElement);
            cardElement.addEventListener("click", () => {
                socket.emit('vote', {
                    cardId: card.id,
                    userId: userId,
                    playerId: playerId,
                    roomId: roomId
                });
                hasVoted = true;
                console.log('投票しました');
            })

            // 場に出すカードを追加
            playedArea.appendChild(cardElement);
            console.log(`Card played by player ${playerId}: ${card.card}`);
        });

        socket.on('message', (data) => {
            showMessage(data.message);
        });

        function generateNewUserId() {
            const newId = 'user_' + Math.random().toString(30).substring(2);
            localStorage.setItem('userId', newId);
            return newId;
        }

        function joinRoom(roomId, username) {
            socket.emit('joinRoom', {
                room_id: roomId,
                username: username
            });
        }

        function playCard(card) {
            ws.send(JSON.stringify({
                type: 'play_card',
                card: card,
                room_id: roomId
            }));
        }

        function vote(card) {
            ws.send(JSON.stringify({
                type: 'vote',
                card: card,
                room_id: roomId
            }));
        }

        function updateGameState(state) {
            // ゲーム状態を更新する処理を実装
            console.log('game state updated:', state);
        }

        function displayPlayedCards(playedCards) {
            const votingArea = document.getElementById('voting-area');
            votingArea.innerHTML = ''; // 既存の内容をクリア


            for (let playerId in playedCards) {
                let cardElement = document.createElement('div');
                cardElement.innerText = `Player ${playerId}: ${playedCards[playerId]}`;
                let voteButton = document.createElement('button');
                voteButton.innerText = 'Vote for this card';
                voteButton.onclick = function() {
                    voteForCard(playedCards[playerId]);
                };
                cardElement.appendChild(voteButton);
                votingArea.appendChild(cardElement);
            }
        }

        function updatePlayerList(players) {
            const ListElement = document.getElementById('list');
            ListElement.innerHTML = '';

            players.forEach(player => {
                const playerElement = document.createElement('div');
                playerElement.textContent = player.name;
                ListElement.appendChild(playerElement);
            })
        }

        function sendMessage() {
            var message = document.getElementById('message').value;
            ws.send(JSON.stringify({
                type: 'chat_message',
                message: message
            }));
            document.getElementById('message').value = '';
        }

        function showMessage(message, duration = 3000) {
            const messageBox = document.getElementById('message-box');
            const messageText = document.getElementById('message-text');

            // メッセージをセット
            messageText.textContent = message;

            // メッセージボックスを表示
            messageBox.classList.remove('hidden');
            messageBox.style.display = 'block';

            // 指定時間後に非表示
            setTimeout(() => {
                messageBox.classList.add('hidden');
                messageBox.style.display = 'none';
            }, duration);
        }


        document.getElementById('menu-click-btn').addEventListener('click', function() {
            const menuPopupWrapper = document.getElementById('menu-popup-wrapper');
            if (menuPopupWrapper.style.display === 'flex') {
                menuPopupWrapper.style.display = 'none';
            } else {
                menuPopupWrapper.style.display = 'flex';
            }
        });

        document.getElementById('rule-click-btn').addEventListener('click', function() {
            document.getElementById('rule-popup-wrapper').style.display = 'block';
        });

        document.getElementById('rule-close').addEventListener('click', function() {
            document.getElementById('rule-popup-wrapper').style.display = 'none';
        });

        document.querySelector('.back-btn').addEventListener('click', function() {
            document.getElementById('second-popup-wrapper').style.display = 'flex';
        });

        document.getElementById('second-popup-close').addEventListener('click', function() {
            document.getElementById('second-popup-wrapper').style.display = 'none';
        });

        document.getElementById('exit-btn').addEventListener('click', function() {
            socket.emit('leaveRoom', {
                roomId: roomId
            });
            window.location.href = 'index.php';
        });

        $("button").click(function() {
            $(this).toggleClass("toggle");
        });
    </script>

</body>

</html>