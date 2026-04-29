<?php
require_once('session_handler.php');
?>
<?php
require_once('session_config.php');

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page using PHP header
header("Location: login.php");
exit();
?>