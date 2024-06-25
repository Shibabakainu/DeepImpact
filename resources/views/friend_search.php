<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/friend_search.css">
    <?php include 'header.php'; ?>
</head>
<body>
<div class="container">
    <h1>検索結果</h1>
    <div class="search-results">
    <?php
      // キーワードの取得
    if (isset($_POST['keyword'])) {
        $keyword = $_POST['keyword'];

          // 検索処理（ダミーの例として、単純な検索結果を表示）
        $results = array(
            "PHPに関する記事",
            "HTMLとCSSの基礎",
            "JavaScriptの学習方法"
        );

          // 検索結果を表示
        echo "<ul>";
        foreach ($results as $result) {
            echo "<li>$result</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>検索キーワードがありません。</p>";
    }
    ?>
    </div>
    <a href="index.html">もう一度検索する</a>
</div>
</body>
</html>
