<?php include 'header.php'; ?>
<div class="container">
    <?php
    include 'db_connect.php'; // データベース接続スクリプトをインクルード

    // ランダムに6枚のカードを選択するSQLクエリ
    $sql = "SELECT Card_id, Card_name, Image_path, IsVisible FROM Card WHERE IsVisible = 1 ORDER BY RAND() LIMIT 6";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $selectedCardIds = [];
        echo '<div class="button-container" id="button-container"><button id="toggle-cards">カードの表示/非表示</button></div>';
        echo '<div id="selected-card-area" class="selected-card-area"></div>';
        echo '<div id="card-container" class="card-container">';
        while ($row = $result->fetch_assoc()) {
            $selectedCardIds[] = $row["Card_id"];
            $visibilityClass = $row['IsVisible'] == 3 ? 'selected-card' : '';
            echo '<div class="card ' . $visibilityClass . '" data-value="' . $row["Card_id"] . '">';
            echo '<img src="../../images/' . $row["Image_path"] . '" alt="' . $row["Card_name"] . '">';
            echo '</div>';
        }
        echo '</div>';

        // 選択されたカードの IsVisible を 2 に更新
        if (!empty($selectedCardIds)) {
            $idsToUpdate = implode(",", $selectedCardIds);
            $updateSql = "UPDATE Card SET IsVisible = 2 WHERE Card_id IN ($idsToUpdate)";
            $conn->query($updateSql);

            // 選択されていないカードの IsVisible を 1 に更新
            $updateOthersSql = "UPDATE Card SET IsVisible = 1 WHERE Card_id NOT IN ($idsToUpdate) AND IsVisible != 1";
            $conn->query($updateOthersSql);
        }
    } else {
        echo "0 results";
    }

    $conn->close();
    ?>
</div>
<script src="../js/card_motion.js"></script>