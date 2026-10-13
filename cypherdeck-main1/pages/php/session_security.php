<?php
// Session security handler that checks timeout, prevents hijacking, and validates integrity

// Check if user has an active session
if (isset($_SESSION['user_id'])) {
    // Check session timeout (30 minutes of inactivity)
    if (isset($_SESSION['session_timeout'])) {
        if (time() > $_SESSION['session_timeout']) {
            // Destroy session and redirect to login when expired
            session_destroy();
            header("Location: /cypherdeck-main1/pages/auth/login.php?expired=1");
            exit;
        } else {
            // Update session timeout to extend it
            $_SESSION['session_timeout'] = time() + (30 * 60);
        }
    }
    
    // Verify session integrity by checking User-Agent for basic hijacking prevention
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    } elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        // User-Agent changed, indicating possible session hijacking attempt
        session_destroy();
        header("Location: /cypherdeck-main1/pages/auth/login.php?hijack=1");
        exit;
    }
}
?>
