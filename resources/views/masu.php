<?php

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ボードゲーム</title>
    <style>
        /* ページ全体のスタイル */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh; /* ビューポートの高さいっぱいに */
            overflow: hidden; /* スクロールバーを非表示に */
            background: url('art1.jpg') no-repeat center center fixed; /* 背景画像の設定 */
            background-size: cover; /* 画像をカバー全体に */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* ボード全体のスタイル */
        .board {
            width: 100vw; /* ビューポートの幅いっぱいに */
            height: 100vh; /* ビューポートの高さいっぱいに */
            background-color: rgba(255, 252, 242, 0.8); /* 背景色と透明度の設定 */
            border: 2px solid #000;
            position: relative;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column; /* 内容を縦に並べる */
            align-items: center; /* 中央に配置 */
        }

        /* スタートとゴールのスタイル */
        .start{
            width: 100px;
            height: 100px;
            background-color: #fff;
            border: 2px solid #f00;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            position: absolute;
        }

        /* スタートの位置 */
        .start {
            top: 20px;
            left: 20px;
        }

        /* サークルコンテナのスタイル */
        .circle-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* 中央に配置 */
            align-items: center; /* 中央に配置 */
            padding: 10px 20px; /* 上部の余白を調整 */
            height: calc(50vh - 60px); /* 上半分の高さに設定 */
            overflow-y: auto; /* 縦スクロールを有効にする */
        }

        /* サークルのスタイル */
        .circle {
            width: 50px;
            height: 50px;
            background-color: #add8e6;
            border: 2px solid #000;
            border-radius: 50%;
            margin: 5px; /* サークルの間隔 */
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        /* ジグザグ配置のスタイル */
        .circle:nth-child(even) {
            transform: translateY(20px); /* 偶数番目のサークルを下に移動 */
        }

        .circle:nth-child(4n+1), .circle:nth-child(4n+2) {
            margin-right: auto; /* 1, 2番目の列を右寄せ */
        }

        .circle:nth-child(4n+3), .circle:nth-child(4n+4) {
            margin-left: auto; /* 3, 4番目の列を左寄せ */
        }

    </style>
</head>
<body>
    <div class="board">
        <div class="start">START</div>
        <div class="circle-container">
            <!-- ジグザグに配置した.circle要素に数字を追加 -->
            <div class="circle">1</div>
            <div class="circle">2</div>
            <div class="circle">3</div>
            <div class="circle">4</div>
            <div class="circle">5</div>
            <div class="circle">6</div>
            <div class="circle">7</div>
            <div class="circle">8</div>
            <div class="circle">9</div>
            <div class="circle">10</div>
            <div class="circle">11</div>
            <div class="circle">12</div>
            <div class="circle">13</div>
            <div class="circle">14</div>
            <div class="circle">15</div>
            <div class="circle">16</div>
            <div class="circle">17</div>
            <div class="circle">18</div>
            <div class="circle">19</div>
            <div class="circle">20</div>
            <div class="circle">21</div>
            <div class="circle">22</div>
            <div class="circle">23</div>
            <div class="circle">24</div>
            <div class="circle">25</div>
            <div class="circle">26</div>
            <div class="circle">27</div>
            <div class="circle">28</div>
            <div class="circle">29</div>
            <div class="circle">30</div>
            <div class="circle">31</div>
            <div class="circle">32</div>
            <div class="circle">33</div>
            <div class="circle">34</div>
            <div class="circle">35</div>
            <div class="circle">36</div>
            <div class="circle">37</div>
            <div class="circle">38</div>
            <div class="circle">39</div>
            <div class="circle">40</div>
        </div>
    </div>
</body>
</html>
