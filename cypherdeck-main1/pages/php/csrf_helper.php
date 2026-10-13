<?php
// CSRF protection that prevents cross-site request forgery by validating tokens on form submissions

// Generate and store CSRF token in session using cryptographically secure random_bytes(32) for unpredictability
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        // Create cryptographically secure random token (32 bytes = 256 bits entropy)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token from form submission using timing-attack-safe comparison
function validateCSRFToken($token) {
    // hash_equals() compares tokens in constant time to prevent attackers timing response to guess token
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Retrieve CSRF token from session or call generateCSRFToken if not yet created
function getCSRFToken() {
    return generateCSRFToken();
}
?>
