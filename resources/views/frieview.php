<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フレンド一覧画面</title>
    <link rel="stylesheet" href="/DeepImpact/resources/css/frieview.css">
</head>
<body>
    <?php
    session_start();
    include 'header.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); // セッションにユーザーIDがない場合はログインページにリダイレクト
        exit;
    }

    // データベース接続
    include 'db_connect.php';

    // ユーザーIDを取得
    $user_id = $_SESSION['user_id'];

    // ユーザー名を取得
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = $user['name'];
    $stmt->close();

    // フレンドリストを取得
    $sql = "SELECT friend_name FROM friends WHERE user_name = ? AND status = 'accepted'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // フレンドリストの配列を作成
    $friends = [];
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row['friend_name'];
    }

    $stmt->close();

    // 保留中のフレンドリクエストを取得
    $sql = "SELECT user_name FROM friends WHERE friend_name = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // 保留中のリクエストの配列を作成
    $pending_requests = [];
    while ($row = $result->fetch_assoc()) {
        $pending_requests[] = $row['user_name'];
    }

    $stmt->close();
    $conn->close();
    ?>
    <div class="container">
        <div class="title">フレンド一覧</div>
        <div class="friend-list">
            <?php if (!empty($friends)): ?>
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-item"><?php echo htmlspecialchars($friend, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-friends">フレンドがいません。</div>
            <?php endif; ?>
        </div>
        
        <div class="pending-requests">
            <div class="title">保留中のフレンドリクエスト</div>
            <?php if (!empty($pending_requests)): ?>
                <?php foreach ($pending_requests as $request): ?>
                    <div class="request-item">
                        <?php echo htmlspecialchars($request, ENT_QUOTES, 'UTF-8'); ?>
                        <form action="handle_friend_request.php" method="post" class="request-form">
                            <input type="hidden" name="request_user" value="<?php echo htmlspecialchars($request, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" name="action" value="accept" class="accept-button">承認</button>
                            <button type="submit" name="action" value="reject" class="reject-button">拒否</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-requests">保留中のフレンドリクエストがありません。</div>
            <?php endif; ?>
        </div>

        <button class="friend-search" onclick="location.href='/DeepImpact/resources/views/friend.php'">フレンド検索</button>
        <button class="logout" onclick="location.href='index.php'">退出</button>
    </div>
</body>
</html>
