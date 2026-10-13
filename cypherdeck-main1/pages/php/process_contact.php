<?php
// Contact form handler that validates input fields, prevents XSS with htmlspecialchars, and stores message in database
session_start();
require_once("process_db.php");
require_once("csrf_helper.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
$csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Security token validation failed']);
    exit;
}

// Validate all fields exist and email has correct format using FILTER_VALIDATE_EMAIL
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Prevent XSS attacks by escaping HTML characters by converting <, > and quotes to HTML entities
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

$userId = $_SESSION['user_id'] ?? null;

// Insert contact message into database
$sql = "INSERT INTO ContactMessages (user_id, name, email, subject, message, created_at, status) 
        VALUES (?, ?, ?, ?, ?, NOW(), 'new')";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("issss", $userId, $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully! We will review it shortly.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
