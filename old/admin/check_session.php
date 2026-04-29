<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

include('session_config.php');

// Debug session information
error_log("Session ID: " . session_id());
error_log("Session status: " . session_status());

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    error_log("No session ID found, redirecting to login");
    // Clear any output
    ob_clean();
    header("Location: login.php");
    exit();
}

// Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Session expired
    error_log("Session expired, destroying session");
    // Clear any output
    ob_clean();
    session_unset();
    session_destroy();
    header("Location: login.php?error=session_expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check if session is from the same IP
if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    // IP changed, destroy session
    error_log("IP changed, destroying session");
    // Clear any output
    ob_clean();
    session_unset();
    session_destroy();
    header("Location: login.php?error=session_hijacked");
    exit();
}

// Store current IP if not set
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
}

// Flush the output buffer
ob_end_flush();
?> 