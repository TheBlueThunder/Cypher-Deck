<?php
// Login handler that verifies username/password against database and creates secure session if credentials are valid
session_start();
require_once("process_db.php");
require_once("csrf_helper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        $_SESSION['login_error'] = "Security token validation failed. Please try again.";
        header("Location: /cypherdeck-main1/pages/auth/login.php");
        exit;
    }
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required.";
        header("Location: /cypherdeck-main1/pages/auth/login.php");
        exit;
    }

    // Prepared statement prevents SQL injection
    $sql = "SELECT * FROM Users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['login_error'] = "Database error. Please try again.";
        header("Location: /cypherdeck-main1/pages/auth/login.php");
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verify password using bcrypt comparison (one-way hashing prevents password exposure)
        if (password_verify($password, $row['password'])) {
            // Regenerate session ID to prevent session fixation attacks after successful login
            session_regenerate_id(true);
            
            // Store user data in session
            $_SESSION['username'] = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $_SESSION['user_id'] = (int)$row['id'];  
            $_SESSION['role'] = htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8');
            $_SESSION['login_time'] = time();
            $_SESSION['session_timeout'] = time() + (30 * 60); // Session expires in 30 minutes
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT']; // Store user agent for hijacking detection

            $stmt->close();
            header("Location: /cypherdeck-main1/index.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid password!";
            header("Location: /cypherdeck-main1/pages/auth/login.php");
            exit;
        }
    } else {
        $_SESSION['login_error'] = "User not found!";
        header("Location: /cypherdeck-main1/pages/auth/login.php");
        exit;
    }
}
$conn->close();
?>
