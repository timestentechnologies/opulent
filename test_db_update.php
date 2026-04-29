<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
$log_file = 'subscription_debug.log';
file_put_contents($log_file, "=== Database Update Test: " . date('Y-m-d H:i:s') . " ===\n");

function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
    echo $message . "<br>";
}

log_message("Starting database update test");

// Get test values
$subscription_id = 4; // The ID from the screenshot
$phone = '254722123456';
$code = 'TEST123CODE';

log_message("Test values - ID: $subscription_id, Phone: $phone, Code: $code");

try {
    // Connect to database
    log_message("Attempting to connect to database...");
    
    // Try both potential connection files
    if (file_exists('admin/connect.php')) {
        include 'admin/connect.php';
        log_message("Using admin/connect.php");
    } elseif (file_exists('includes/db_connection.php')) {
        include 'includes/db_connection.php';
        log_message("Using includes/db_connection.php");
    } else {
        log_message("ERROR: No connection file found!");
        
        // Try direct connection
        log_message("Attempting direct connection...");
        $conn = new mysqli('localhost', 'root', '', 'timesten_laundry');
        
        if ($conn->connect_error) {
            log_message("ERROR: Direct connection failed - " . $conn->connect_error);
            exit;
        } else {
            log_message("Direct connection successful");
        }
    }
    
    // Check if connection is successful
    if (!isset($conn) || $conn->connect_error) {
        log_message("ERROR: Database connection failed - " . ($conn->connect_error ?? 'Unknown error'));
        exit;
    }
    
    log_message("Database connection successful");
    
    // First, check if the table user_subscriptions exists
    $table_check = $conn->query("SHOW TABLES LIKE 'user_subscriptions'");
    if (!$table_check || $table_check->num_rows === 0) {
        log_message("ERROR: Table user_subscriptions does not exist!");
        exit;
    }
    
    log_message("Table user_subscriptions exists");
    
    // Second, check table structure to confirm mobile and code columns exist
    $structure_query = "DESCRIBE user_subscriptions";
    $structure_result = $conn->query($structure_query);
    
    if (!$structure_result) {
        log_message("ERROR: Failed to check table structure - " . $conn->error);
        exit;
    }
    
    log_message("Successfully retrieved table structure");
    
    $has_mobile_column = false;
    $has_code_column = false;
    $columns = [];
    
    while ($row = $structure_result->fetch_assoc()) {
        $columns[] = $row['Field'];
        if ($row['Field'] === 'mobile') {
            $has_mobile_column = true;
        }
        if ($row['Field'] === 'code') {
            $has_code_column = true;
        }
    }
    
    log_message("All columns: " . implode(', ', $columns));
    
    if (!$has_mobile_column || !$has_code_column) {
        log_message("ERROR: Missing required columns - Mobile: " . ($has_mobile_column ? 'YES' : 'NO') . ", Code: " . ($has_code_column ? 'YES' : 'NO'));
        
        // Try to add missing columns
        log_message("Attempting to add missing columns...");
        
        if (!$has_mobile_column) {
            $add_mobile = $conn->query("ALTER TABLE user_subscriptions ADD COLUMN mobile VARCHAR(50) NULL AFTER status");
            if ($add_mobile) {
                log_message("Successfully added mobile column");
                $has_mobile_column = true;
            } else {
                log_message("ERROR: Failed to add mobile column - " . $conn->error);
            }
        }
        
        if (!$has_code_column) {
            $add_code = $conn->query("ALTER TABLE user_subscriptions ADD COLUMN code VARCHAR(50) NULL AFTER mobile");
            if ($add_code) {
                log_message("Successfully added code column");
                $has_code_column = true;
            } else {
                log_message("ERROR: Failed to add code column - " . $conn->error);
            }
        }
    }
    
    // Check if the required columns now exist
    if (!$has_mobile_column || !$has_code_column) {
        log_message("Cannot proceed as required columns are missing");
        exit;
    }
    
    // Now check if the subscription exists
    log_message("Checking if subscription with ID $subscription_id exists...");
    
    $check_query = "SELECT * FROM user_subscriptions WHERE id = $subscription_id";
    $check_result = $conn->query($check_query);
    
    if (!$check_result) {
        log_message("ERROR: Failed to query subscription - " . $conn->error);
        exit;
    }
    
    if ($check_result->num_rows === 0) {
        log_message("ERROR: No subscription found with ID $subscription_id");
        exit;
    }
    
    $subscription = $check_result->fetch_assoc();
    log_message("Found subscription - Current mobile: " . ($subscription['mobile'] ?? 'NULL') . ", code: " . ($subscription['code'] ?? 'NULL'));
    
    // Now perform the update
    log_message("Attempting to update subscription...");
    
    // Method 1: Direct SQL (basic)
    $escaped_phone = $conn->real_escape_string($phone);
    $escaped_code = $conn->real_escape_string($code);
    
    $sql = "UPDATE user_subscriptions SET mobile = '$escaped_phone', code = '$escaped_code' WHERE id = $subscription_id";
    
    log_message("SQL Query: $sql");
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("ERROR: Update failed - " . $conn->error);
    } else {
        $affected_rows = $conn->affected_rows;
        log_message("Update successful! Affected rows: $affected_rows");
        
        // Check final result
        $final_check = $conn->query("SELECT mobile, code FROM user_subscriptions WHERE id = $subscription_id");
        if ($final_check && $row = $final_check->fetch_assoc()) {
            log_message("FINAL VALUES - Mobile: " . ($row['mobile'] ?? 'NULL') . ", Code: " . ($row['code'] ?? 'NULL'));
        } else {
            log_message("ERROR: Could not verify final values - " . $conn->error);
        }
    }
    
    // Close connection
    $conn->close();
    log_message("Database connection closed");
    
} catch (Exception $e) {
    log_message("EXCEPTION: " . $e->getMessage());
}

log_message("Test completed");
echo "<a href='dashboard.php'>Return to Dashboard</a>";
?> 