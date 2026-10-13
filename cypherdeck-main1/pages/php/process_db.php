<?php
// Database connection setup that loads credentials from environment variables and creates mysqli connection with UTF-8 support
$servername = getenv('DB_HOST') ?: "localhost";   
$username   = getenv('DB_USER') ?: "root";        
$password   = getenv('DB_PASS') ?: "codio";            
$dbname     = getenv('DB_NAME') ?: "CypherStore";  

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

// Register cleanup function to close connection when PHP script finishes execution
function closeDatabase() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}
register_shutdown_function('closeDatabase');

?>
