<?php

$host = "localhost";
$dbname = "storyteller";
$username = "username";  // Replace with your actual database username
$password = "password";  // Replace with your actual database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ExtraCardのカードをCardに追加する処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_card'])) {
    $extraCardId = $_POST['extra_card_id'];

    // ExtraCardの情報を取得
    $sql = "SELECT Card_name, Image_path FROM ExtraCard WHERE ExtraCard_id = :extra_card_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['extra_card_id' => $extraCardId]);
    $extraCard = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($extraCard) {
        // 重複確認
        $checkSql = "SELECT COUNT(*) FROM Card WHERE Card_name = :card_name";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['card_name' => $extraCard['Card_name']]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo "<p>同じ名前のカードが既に存在します。</p>";
        } else {
            // Cardテーブルに挿入
            $sql = "INSERT INTO Card (Card_name, Image_path) VALUES (:card_name, :image_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['card_name' => $extraCard['Card_name'], 'image_path' => $extraCard['Image_path']]);
            echo "<p>カードがCardテーブルに追加されました。</p>";
        }
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

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カードリスト</title>
    <link rel="stylesheet">
</head>

<body>
    <div class="container">
        <h2>カードリスト</h2>

        <div class="card-layout">
            <!-- 左側にCardテーブルのカードを表示 -->
            <div class="card-list left">
                <?php foreach ($cards as $card): ?>
                    <div class="card">
                        <h4><?php echo htmlspecialchars($card['Card_name']); ?></h4>
                        <img src="../../images/<?php echo htmlspecialchars($card['Image_path']); ?>" alt="<?php echo htmlspecialchars($card['Card_name']); ?>" width="100" height="130">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 右側にExtraCardテーブルのカードを表示 -->
            <div class="card-list right">
                <?php foreach ($extraCards as $extraCard): ?>
                    <div class="card">
                        <h4><?php echo htmlspecialchars($extraCard['Card_name']); ?></h4>
                        <img src="../../images/<?php echo htmlspecialchars($extraCard['Image_path']); ?>" alt="<?php echo htmlspecialchars($extraCard['Card_name']); ?>" width="100" height="130">
                        <!-- Cardテーブルに挿入するフォーム -->
                        <form method="post" action="">
                            <input type="hidden" name="extra_card_id" value="<?php echo $extraCard['ExtraCard_id']; ?>">
                            <input type="submit" name="add_to_card" value="Cardに追加">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- スタイル -->
    <style>
        .container {
            width: 90%;
            margin: auto;
            text-align: center;
        }

        /* CardとExtraCardを左右に配置 */
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