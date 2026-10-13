<?php
session_start();
require_once("../../pages/php/process_db.php");

if (!isset($_SESSION['user_id']) || !isset($_POST['room_id']) || !isset($_POST['message'])) {
    http_response_code(400);
    exit();
}

$room_id = (int)$_POST['room_id'];
$message = trim($_POST['message']);
$user_id = $_SESSION['user_id'];

if (empty($message)) {
    http_response_code(400);
    exit();
}

$sql = "INSERT INTO ChatMessages (room_id, user_id, message, created_at) 
        VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $room_id, $user_id, $message);

if ($stmt->execute()) {
    http_response_code(200);
} else {
    error_log("Chat insert failed: " . $stmt->error);
    http_response_code(500);
}

?>
