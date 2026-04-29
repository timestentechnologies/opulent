<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Function to safely print variables
function debug_print($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

echo "<h2>Session Information:</h2>";
debug_print($_SESSION);

echo "<h2>Server Information:</h2>";
debug_print($_SERVER);

echo "<h2>Database Connection Test:</h2>";
include('connect.php');

if(isset($conn)) {
    echo "Database connection established.<br>";
    
    // Test query
    $test_query = "SELECT COUNT(*) as count FROM admin";
    $result = mysqli_query($conn, $test_query);
    
    if($result) {
        $row = mysqli_fetch_assoc($result);
        echo "Number of admin users: " . $row['count'] . "<br>";
    } else {
        echo "Query failed: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Database connection failed.<br>";
}

echo "<h2>PHP Information:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Session ID: " . session_id() . "<br>";
?> 