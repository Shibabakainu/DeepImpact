<?php

$host = "49.212.166.241";
$dbname = "storyteller";
$username = "thread";  // Replace with your actual database username
$password = "PassWord1412%";  // Replace with your actual database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// CardテーブルのカードをExtraCardに移動する処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_extracard'])) {
    $cardId = $_POST['card_id'];

    $sql = "SELECT Card_name, Image_path FROM Card WHERE Card_id = :card_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['card_id' => $cardId]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($card) {
        $checkSql = "SELECT COUNT(*) FROM ExtraCard WHERE Card_name = :card_name";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['card_name' => $card['Card_name']]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo "<p>同じ名前のカードがExtraCardテーブルに既に存在します。</p>";
        } else {
            $insertSql = "INSERT INTO ExtraCard (Card_name, Image_path) VALUES (:card_name, :image_path)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute(['card_name' => $card['Card_name'], 'image_path' => $card['Image_path']]);

            if ($insertStmt->rowCount() > 0) {
                $deleteSql = "DELETE FROM Card WHERE Card_id = :card_id";
                $deleteStmt = $pdo->prepare($deleteSql);
                $deleteStmt->execute(['card_id' => $cardId]);
                echo "<p>カードがExtraCardテーブルに移動され、Cardテーブルから削除されました。</p>";
            }
        }
    }
}

// ExtraCardテーブルのカードを削除する処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_from_extracard'])) {
    $extraCardId = $_POST['extracard_id'];

    $deleteSql = "DELETE FROM ExtraCard WHERE ExtraCard_id = :extracard_id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute(['extracard_id' => $extraCardId]);

    if ($deleteStmt->rowCount() > 0) {
        echo "<p>ExtraCardテーブルからカードが削除されました。</p>";
    }
}

// Cardテーブルのカード一覧を取得
try {
    $sql = "SELECT Card_id, Card_name, Image_path FROM Card";
    $stmt = $pdo->query($sql);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    die();
}

// ExtraCardテーブルのカード一覧を取得
try {
    $sql = "SELECT ExtraCard_id, Card_name, Image_path FROM ExtraCard";
    $stmt = $pdo->query($sql);
    $extraCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    die();
}

?>
<?php

// ExtraCardテーブルからCardテーブルに移動する処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_card'])) {
    $extraCardId = $_POST['extracard_id'];

    $sql = "SELECT Card_name, Image_path FROM ExtraCard WHERE ExtraCard_id = :extracard_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['extracard_id' => $extraCardId]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($card) {


        // 重複確認
        $checkSql = "SELECT COUNT(*) FROM Card WHERE Card_name = :card_name";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['card_name' => $card['Card_name']]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo "<p>同じ名前のカードがCardテーブルに既に存在します。</p>";
        } else {
            $insertSql = "INSERT INTO Card (Card_name, Image_path) VALUES (:card_name, :image_path)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute(['card_name' => $card['Card_name'], 'image_path' => $card['Image_path']]);

            if ($insertStmt->rowCount() > 0) {
                $deleteSql = "DELETE FROM ExtraCard WHERE ExtraCard_id = :extracard_id";
                $deleteStmt = $pdo->prepare($deleteSql);
                $deleteStmt->execute(['extracard_id' => $extraCardId]);
                echo "<p>カードがCardテーブルに移動され、ExtraCardテーブルから削除されました。</p>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カードリスト</title>
    <link rel="stylesheet" href="../css/cardlist.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <h2>カードリスト</h2>

        <div class="card-layout">
            <!-- 左側にCardテーブルのカードを表示 -->
            <div class="card-list left">
                <?php foreach ($cards as $card): ?>
                    <div class="card">
                        <h4><?php echo htmlspecialchars($card['Card_name']); ?></h4>
                        <img src="../../images/<?php echo htmlspecialchars($card['Image_path']); ?>" alt="<?php echo htmlspecialchars($card['Card_name']); ?>" width="100" height="130">
                        <!-- ExtraCardテーブルに移動するフォーム -->
                        <form method="post" action="">
                            <input type="hidden" name="card_id" value="<?php echo $card['Card_id']; ?>">
                            <input type="submit" name="move_to_extracard" value="ExtraCardに移動">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 右側にExtraCardテーブルのカードを表示 -->
            <div class="card-list right">
                <?php foreach ($extraCards as $extraCard): ?>
                    <div class="card">
                        <h4><?php echo htmlspecialchars($extraCard['Card_name']); ?></h4>
                        <img src="../../images/<?php echo htmlspecialchars($extraCard['Image_path']); ?>" alt="<?php echo htmlspecialchars($extraCard['Card_name']); ?>" width="100" height="130">
                        <!-- ExtraCardテーブルのカードを削除するフォーム -->
                        <form method="post" action="">
                            <input type="hidden" name="extracard_id" value="<?php echo $extraCard['ExtraCard_id']; ?>">
                            <input type="submit" name="move_to_card" value="Cardテーブルに移動">
                            <input type="submit" name="delete_from_extracard" value="削除">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- スタイル -->
    <style>
        .top-bar {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-button {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }

        .container {
            width: 90%;
            margin: auto;
            text-align: center;
        }

        .card-layout {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .card-list {
            width: 48%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .card {
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .card h4 {
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .card img {
            border-radius: 4px;
        }
    </style>
</body>

</html>