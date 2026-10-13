<?php
// Registration handler that validates input, checks duplicates, hashes password with bcrypt, and creates user account
session_start();
require_once("process_db.php");
require_once("csrf_helper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        $_SESSION['register_error'] = "Security token validation failed. Please try again.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = "user";

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['register_error'] = "All fields are required.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['register_error'] = "Password must be at least 8 characters.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }

    // Validate password strength: uppercase, lowercase, number
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $_SESSION['register_error'] = "Password must contain uppercase letters, lowercase letters, and numbers.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email format.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }

    // Prepared statement checks if username or email already exists in database
    $checkSql = "SELECT id FROM Users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $_SESSION['register_error'] = "Username or email already exists.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }
    $checkStmt->close();

    // Hash password with bcrypt to produce one-way hash that cannot be reversed and prevents plain-text storage
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $newUserId = $conn->insert_id;

        // Initialize empty progress tracker for new user
        $progressSql = "INSERT INTO UserProgress (user_id, puzzles_solved, attempts, hints_used, last_attempt)
                        VALUES (?, 0, 0, 0, NULL)";
        $progressStmt = $conn->prepare($progressSql);
        $progressStmt->bind_param("i", $newUserId);
        $progressStmt->execute();
        $progressStmt->close();

        $_SESSION['register_success'] = "Registration successful! You can now login.";
        header("Location: /cypherdeck-main1/pages/auth/login.php");
        exit;
    } else {
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        header("Location: /cypherdeck-main1/pages/auth/register.php");
        exit;
    }

    $stmt->close();
}
$conn->close();
?>
