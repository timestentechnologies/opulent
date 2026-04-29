<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();


// Include session configuration
require_once('session_config.php');

// Include database connection
require_once('connect.php');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"] === true;
}

// Function to verify session against database
function verifySession() {
    global $conn;
    
    if(!isset($_SESSION["email"])) {
        return false;
    }
    
    $email = $_SESSION["email"];
    $sql = "SELECT * FROM admin WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 1;
}

// Function to enforce authentication
function enforceAuthentication() {
    if(!isLoggedIn() || !verifySession()) {
        // Clear any existing output
        if(ob_get_length()) ob_clean();
        
        // Clear session
        session_destroy();
        
        // Redirect to login
        header("Location: login.php");
        exit();
    }
}

// Check if this is the login page
$current_page = basename($_SERVER['PHP_SELF']);
if($current_page !== 'login.php' && $current_page !== 'forgot_password.php') {
    enforceAuthentication();
}
?> 