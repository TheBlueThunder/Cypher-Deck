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

// Validate input
if (!$note_id) {
    http_response_code(400);
    exit();
}

$user_id = $_SESSION['user_id'];

// Delete sticky note after verifying user owns it
$sql = "DELETE FROM StickyNotes WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit();
}

$stmt->bind_param("ii", $note_id, $user_id);

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
