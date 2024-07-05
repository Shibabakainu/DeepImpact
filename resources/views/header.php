<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Teller</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/header.css">
    <link rel="stylesheet" href="/DeepImpact/resources/css/sidebar.css">
    <script src="/DeepImpact/resources/js/script.js" defer></script>
</head>

<body>
    <div class="header-container">
        <header>
            <a href="/DeepImpact/resources/views/index.php"><img src="/DeepImpact/images/sttera.png" alt="Story Teller" class="title-image"></a>
            <button class="menu-button" onclick="toggleSidebar(this)">メニュー</button>
        </header>
    </div>
    <?php include 'sidebar.php'; ?>
</body>

</html>