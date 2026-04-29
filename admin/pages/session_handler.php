<?php
// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout to 30 minutes (1800 seconds)
ini_set('session.gc_maxlifetime', 1800);

// Check if user is logged in (if needed)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?> 