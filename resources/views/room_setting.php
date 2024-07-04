<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>設定画面</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/room_setting.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <form action="room_detail.php" method="POST">
            <div class="form-group">
                <label for="setting">合言葉設定</label>
                <input type="text" id="setting" name="setting" required>
            </div>
            <div class="form-group">
                <label for="room">ルーム名</label>
                <input type="text" id="room" name="room" required>
            </div>
            <div class="form-group">
                <label for="people">人数</label>
                <select id="people" name="people">
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </div>
            <button type="submit">設定</button>
            <button type="button" class="create" onclick="location.href='room_create.php'">戻る</button>
        </form>
    </div>
</body>

</html>