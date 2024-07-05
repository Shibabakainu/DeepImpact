<?php include 'header.php'; ?>
<div class="container">
    <?php
    include 'db_connect.php'; // データベース接続スクリプトをインクルード

    // ランダムに6枚のカードを選択するSQLクエリ
    $sql = "SELECT Card_id, Card_name, Image_path FROM Card ORDER BY RAND() LIMIT 6";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="button-container" id="button-container"><button id="toggle-cards">カードの表示/非表示</button></div>';
        echo '<div id="card-container" class="card-container">'; // IDを追加
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card" id="card-' . $row["Card_id"] . '">';
            echo '<img src="../../images/' . $row["Image_path"] . '" alt="' . $row["Card_name"] . '">';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo "0 results";
    }

    $conn->close();
    ?>
</div>
<script src="../js/card_motion.js"></script>