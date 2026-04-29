<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include session configuration
require_once('session_config.php');

// Check session status
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";

// Check if session variables are set
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check database connection
include('connect.php');
if($conn) {
    echo "Database connection successful<br>";
} else {
    echo "Database connection failed<br>";
}

// Check if headers were already sent
if (headers_sent($filename, $linenum)) {
    echo "Headers already sent in $filename on line $linenum<br>";
}
?> 