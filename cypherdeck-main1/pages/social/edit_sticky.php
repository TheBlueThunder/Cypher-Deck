<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

// Get POST data
$note_id = (int)($_POST['note_id'] ?? 0);
$title = trim($_POST['title'] ?? 'Untitled Note');
$content = trim($_POST['content'] ?? '');

// Validate input
if (!$note_id || empty($content)) {
    http_response_code(400);
    exit();
}

// Sanitize inputs
$title = htmlspecialchars($title);
$content = htmlspecialchars($content);
$user_id = $_SESSION['user_id'];

// Update sticky note after verifying user owns it
$sql = "UPDATE StickyNotes SET title = ?, content = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit();
}

$stmt->bind_param("ssii", $title, $content, $note_id, $user_id);

if ($stmt->execute()) {
    // Success
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    // Error
    http_response_code(500);
    exit();
}

$stmt->close();
$conn->close();
?>
