<?php
include 'db_connect.php';

if (isset($_POST['room_name']) && isset($_POST['password'])) {
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $password = $conn->real_escape_string($_POST['password']);

    // 部屋名からroom_idを取得
    $sql = "SELECT room_id FROM rooms WHERE room_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $room_name);
    $stmt->execute();
    $stmt->bind_result($room_id);

    if ($stmt->fetch()) {
        $stmt->close();

        // room_idを使ってパスワードハッシュを取得
        $sql = "SELECT password_hash FROM room_passwords WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->bind_result($password_hash);

        if ($stmt->fetch()) {
            if (password_verify($password, $password_hash)) {
                echo 'success';
            } else {
                echo 'invalid';
            }
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
    $stmt->close();
} else {
    echo 'invalid';
}

$conn->close();
