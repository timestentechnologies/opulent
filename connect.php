<?php
/* Local Database*/

$servername = "localhost";
$username = "opulentl_laundry";
$password = "Phenomenal@254";
$dbname = "opulentl_laundry";

// Function to check connection and reconnect if needed
if (!function_exists('checkConnection')) {
    function checkConnection($conn) {
        if (!mysqli_ping($conn)) {
            mysqli_close($conn);
            $conn = mysqli_connect($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['dbname']);
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
        }
        return $conn;
    }
}

// Create connection with error handling
try {
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to utf8
    if (!mysqli_set_charset($conn, "utf8")) {
        throw new Exception("Error setting charset: " . mysqli_error($conn));
    }
    
    // Verify database exists
    $result = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception("Database '$dbname' does not exist");
    }
    
    // Check connection before each query
    $conn = checkConnection($conn);
    
} catch (Exception $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // Display a user-friendly error message
    die("Unable to connect to the database. Please check if MySQL server is running and the database exists.");
} 