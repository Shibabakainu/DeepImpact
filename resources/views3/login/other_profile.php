<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

if (!isset($_GET['user_name'])) {
    echo "Invalid request.";
    exit;
}

// Database connection
include '../db_connect.php';

$user_name = $_GET['user_name'];

// Fetch user information
$sql = "SELECT email, profile_image, name FROM users WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_name);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザープロフィール</title>
    <link rel="stylesheet" href="/deepimpact/resources/css/other_profile.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="container">
        <h3><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>のプロフィール</h3>
        <?php if (!empty($user['profile_image'])): ?>
            <img src="/deepimpact/resources/views2/login/profileicon/<?php echo htmlspecialchars($user['profile_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Icon" class="profile_image">
        <?php else: ?>
            <img src="/deepimpact/resources/views2/login/profileicon/icon.png" alt="Default Icon" class="profile_image">
        <?php endif; ?>
        <p><b>ユーザーの名前</b>: <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><b>ユーザーのメール</b>: <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
        <button class="return" onclick="location.href='/deepimpact/resources/views2/frieview.php'">戻る</button>
    </div>
</body>

</html>