<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set session name
session_name('opulent_laundry_session');

// Set session cookie parameters
$secure = false; // Set to true only if you're using HTTPS
$httponly = true;
$samesite = 'Lax';
$path = '/';

// Set cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => $path,
    'domain' => '',
    'secure' => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite
]);

// Set session garbage collection
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Set session save path if needed (uncomment and modify if required)
// ini_set('session.save_path', '/path/to/session/storage');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    error_log("Session started with ID: " . session_id());
}

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
    error_log("New session generated with ID: " . session_id());
} elseif (time() - $_SESSION['last_regeneration'] > 3600) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
    error_log("Session regenerated with ID: " . session_id());
}

// Log current session state
error_log("Current session state - ID: " . session_id() . ", Data: " . print_r($_SESSION, true));
?> 