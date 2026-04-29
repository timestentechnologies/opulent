<?php
// Start output buffering
ob_start();

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set execution time limit (5 minutes)
ini_set('max_execution_time', 300);

// Debug logging function
function debug_log($message) {
    error_log("[WEBSITE_DEBUG] " . $message);
    // Uncomment to see debug messages on screen
    // echo $message . "<br>";
    // flush();
}

debug_log("Script started");

// Include files one by one to identify potential issues
debug_log("Loading session_handler.php");
require_once('session_handler.php');
debug_log("Loading head.php");
require_once('head.php');
debug_log("Loading header.php");
require_once('header.php');
debug_log("Loading sidebar.php");
require_once('sidebar.php');
debug_log("Loading connect.php");
require_once('connect.php');
debug_log("All includes loaded successfully");

// Skip form processing for debugging
debug_log("Querying database");
$que = "select * from manage_website";
$query = $conn->query($que);

if (!$query) {
    debug_log("Database query failed: " . $conn->error);
    die("Error fetching website settings: " . $conn->error);
}

debug_log("Query executed successfully");

// Only extract minimal data needed
while($row = mysqli_fetch_array($query)) {
    $title = $row['title'] ?? 'Default Title';
    $short_title = $row['short_title'] ?? 'Default Short Title';
    // Skip other fields for debugging
}

debug_log("Data extracted from database");

// Display minimal HTML structure
echo '<!DOCTYPE html>
<html>
<head>
    <title>Debug Page</title>
</head>
<body>
    <h1>Debug Mode</h1>
    <p>Title: ' . $title . '</p>
    <p>Short Title: ' . $short_title . '</p>
</body>
</html>';

debug_log("Debug page rendered successfully");
?> 