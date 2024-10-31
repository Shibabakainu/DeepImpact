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

// 画像アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_card'])) {
    $cardName = $_POST['card_name'];
    $imagePath = $_FILES['image_path']['name']; // 画像ファイル名を取得
    $targetDir = "../../images/"; // 画像保存先ディレクトリ
    $targetFile = $targetDir . basename($imagePath);

    // 画像を指定したディレクトリに移動
    if (move_uploaded_file($_FILES['image_path']['tmp_name'], $targetFile)) {
        // データベースにカードを追加
        $sql = "INSERT INTO ExtraCard (Card_name, Image_path) VALUES (:card_name, :image_path)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['card_name' => $cardName, 'image_path' => $imagePath]);
        echo "<p>新しいカードが追加されました。</p>";
    } else {
        echo "<p>画像のアップロードに失敗しました。</p>";
    }
}

try {
    // Fetch the cards from the ExtraCard table
    $sql = "SELECT ExtraCard_id, Card_name, Image_path FROM ExtraCard";
    $stmt = $pdo->query($sql);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カードリスト</title>
    <link rel="stylesheet" href="../css/cardlist.css"> <!-- Verify this path -->
</head>

<body>
    <div class="card-container">
        <?php if (!empty($cards)) : ?>
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <img src="../../images/<?php echo htmlspecialchars($card['Image_path']); ?>" alt="<?php echo htmlspecialchars($card['Card_name']); ?>" width="150" height="200">
                    <h3><?php echo htmlspecialchars($card['Card_name']); ?></h3>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>カードが見つかりませんでした。</p>
        <?php endif; ?>
    </div>

    <!-- 画像の下に追加カードフォームを配置 -->
    <div class="add-card-form">
        <h2>新しいカードを追加</h2>
        <form method="post" action="" enctype="multipart/form-data"> <!-- enctypeを追加 -->
            <label for="card_name">カード名:</label>
            <input type="text" id="card_name" name="card_name" required><br><br>

            <label for="image_path">画像ファイルを選択:</label>
            <input type="file" id="image_path" name="image_path" accept="image/*" required><br><br>

            <input type="submit" name="add_card" value="カードを追加">
        </form>
    </div>

    <!-- スタイル調整 -->
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            /* 画像間のスペース */
            justify-content: center;
            /* 中央揃え */
            margin-bottom: 30px;
            /* フォームとの余白 */
        }

        .card {
            width: 150px;
            /* カードの幅 */
            text-align: center;
            /* テキスト中央揃え */
        }

        .add-card-form {
            text-align: center;
            /* フォーム中央揃え */
            margin-top: 20px;
            /* フォームの上に余白 */
        }
    </style>
</body>

</html>