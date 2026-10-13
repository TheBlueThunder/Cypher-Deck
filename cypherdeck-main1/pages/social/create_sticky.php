<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

// Get POST data
$room_id = (int)($_POST['room_id'] ?? 0);
$title = trim($_POST['title'] ?? 'Untitled Note');
$content = trim($_POST['content'] ?? '');

// Validate input
if (!$room_id || empty($content)) {
    http_response_code(400);
    exit();
}

// Sanitize inputs
$title = htmlspecialchars($title);
$content = htmlspecialchars($content);
$user_id = $_SESSION['user_id'];

// Insert sticky note into database
$sql = "INSERT INTO StickyNotes (room_id, user_id, title, content, x_position, y_position, color, pinned, created_at) 
        VALUES (?, ?, ?, ?, 100, 100, '#ffc107', 0, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit();
}

$stmt->bind_param("iiss", $room_id, $user_id, $title, $content);

if ($stmt->execute()) {
    // Success
    http_response_code(200);
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    // Error
    http_response_code(500);
    exit();
}

$stmt->close();
$conn->close();
?>
