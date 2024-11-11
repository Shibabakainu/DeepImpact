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

// 画像アップロードおよびカード追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 新規カード追加
    if (isset($_POST['add_card'])) {
        $cardName = $_POST['card_name'];
        $imagePath = $_FILES['image_path']['name'];
        $targetDir = "../../images/";
        $targetFile = $targetDir . basename($imagePath);

        // 重複チェック
        $checkQuery = "SELECT COUNT(*) FROM ExtraCard WHERE Card_name = :card_name";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['card_name' => $cardName]);
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            echo "<p>このカードはすでに追加されています。</p>";
        } else {
            // 画像アップロードとDB登録
            if (move_uploaded_file($_FILES['image_path']['tmp_name'], $targetFile)) {
                $sql = "INSERT INTO ExtraCard (Card_name, Image_path) VALUES (:card_name, :image_path)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['card_name' => $cardName, 'image_path' => $imagePath]);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                echo "<p>画像のアップロードに失敗しました。</p>";
            }
        }
    }

    // カード情報の更新
    if (isset($_POST['update_card'])) {
        $cardId = $_POST['card_id'];
        $newCardName = $_POST['new_card_name'];
        $newImagePath = $_FILES['new_image_path']['name'];

        // 画像がアップロードされた場合のみ処理
        if (!empty($newImagePath)) {
            $targetFile = "../../images/" . basename($newImagePath);
            move_uploaded_file($_FILES['new_image_path']['tmp_name'], $targetFile);
        } else {
            $sql = "SELECT Image_path FROM ExtraCard WHERE ExtraCard_id = :card_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['card_id' => $cardId]);
            $newImagePath = $stmt->fetchColumn();
        }

        // データベースを更新
        $updateSql = "UPDATE ExtraCard SET Card_name = :new_card_name, Image_path = :new_image_path WHERE ExtraCard_id = :card_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['new_card_name' => $newCardName, 'new_image_path' => $newImagePath, 'card_id' => $cardId]);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch the cards from the ExtraCard table
try {
    $sql = "SELECT ExtraCard_id, Card_name, Image_path FROM ExtraCard";
    $stmt = $pdo->query($sql);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../css/cardlist.css">
</head>

<body>
    <div class="card-container">
        <?php if (!empty($cards)) : ?>
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <img src="../../images/<?php echo htmlspecialchars($card['Image_path']); ?>" alt="<?php echo htmlspecialchars($card['Card_name']); ?>" width="150" height="200">
                    <h3><?php echo htmlspecialchars($card['Card_name']); ?></h3>
                    <!-- 更新ボタン -->
                    <button onclick="document.getElementById('updateForm-<?php echo $card['ExtraCard_id']; ?>').style.display='block'">更新</button>

                    <!-- 更新フォーム -->
                    <div id="updateForm-<?php echo $card['ExtraCard_id']; ?>" style="display:none;">
                        <form method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="card_id" value="<?php echo $card['ExtraCard_id']; ?>">
                            <label>新しいカード名:</label>
                            <input type="text" name="new_card_name" value="<?php echo htmlspecialchars($card['Card_name']); ?>" required><br>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>カードが見つかりませんでした。</p>
        <?php endif; ?>
    </div>

    <!-- 追加カードフォーム -->
    <div class="add-card-form">
        <h2>新しいカードを追加</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="card_name">カード名:</label>
            <input type="text" id="card_name" name="card_name" required><br><br>

            <label for="image_path">画像ファイルを選択:</label>
            <input type="file" id="image_path" name="image_path" accept="image/*" required><br><br>

            <input type="submit" name="add_card" value="カードを追加">
        </form>
    </div>

    <!-- CSSスタイル -->
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .card {
            width: 150px;
            text-align: center;
        }

        .add-card-form {
            text-align: center;
            margin-top: 20px;
        }

        .update-form {
            display: none;
            margin-top: 10px;
        }
    </style>
</body>

</html>