<?php
// Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Header for proper display
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Subscription Update Debugging</h2>";

// Get the subscription ID from URL or use a default test ID
$subscription_id = isset($_GET['id']) ? (int)$_GET['id'] : 4; // Default to ID 4 for testing
$user_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 3; // Default to ID 3 for testing

// Get test values or use defaults
$phone = isset($_GET['phone']) ? $_GET['phone'] : '254722123456'; // Test phone number
$code = isset($_GET['code']) ? $_GET['code'] : 'TESTCODE123'; // Test code

echo "<p>Working with: Subscription ID: $subscription_id, User ID: $user_id</p>";
echo "<p>Test Values: Phone: $phone, Code: $code</p>";

// Connect to database
try {
    // Include database connection
    include 'admin/connect.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }
    
    echo "<p style='color:green'>Database connection successful</p>";
    
    // Check table structure
    echo "<h3>Table Structure Check</h3>";
    $structure_query = "DESCRIBE user_subscriptions";
    $structure_result = mysqli_query($conn, $structure_query);
    
    if (!$structure_result) {
        echo "<p style='color:red'>Error checking table structure: " . mysqli_error($conn) . "</p>";
    } else {
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($structure_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check current values
    echo "<h3>Current Subscription Data</h3>";
    $current_query = "SELECT * FROM user_subscriptions WHERE id = $subscription_id";
    $current_result = mysqli_query($conn, $current_query);
    
    if (!$current_result) {
        echo "<p style='color:red'>Error retrieving current data: " . mysqli_error($conn) . "</p>";
    } else {
        if (mysqli_num_rows($current_result) > 0) {
            $row = mysqli_fetch_assoc($current_result);
            echo "<table border='1'>";
            foreach ($row as $field => $value) {
                echo "<tr><td>" . htmlspecialchars($field) . "</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange'>No subscription found with ID $subscription_id</p>";
        }
    }
    
    // Perform direct update
    echo "<h3>Performing Direct Update</h3>";
    
    // Create a complete, explicit update statement with no conditions
    $update_sql = "UPDATE user_subscriptions SET 
                  mobile = '$phone',
                  code = '$code' 
                  WHERE id = $subscription_id";
    
    echo "<p>SQL Query: " . htmlspecialchars($update_sql) . "</p>";
    
    $update_result = mysqli_query($conn, $update_sql);
    
    if (!$update_result) {
        echo "<p style='color:red'>Update failed: " . mysqli_error($conn) . "</p>";
    } else {
        $affected_rows = mysqli_affected_rows($conn);
        if ($affected_rows > 0) {
            echo "<p style='color:green'>Update successful! Affected rows: $affected_rows</p>";
        } else {
            echo "<p style='color:orange'>Query executed but no rows were affected. This might indicate that:</p>";
            echo "<ul>";
            echo "<li>The record with ID $subscription_id doesn't exist</li>";
            echo "<li>The new values are identical to the existing values</li>";
            echo "<li>A trigger or constraint prevented the update</li>";
            echo "</ul>";
        }
    }
    
    // Check updated values
    echo "<h3>Updated Subscription Data</h3>";
    $updated_query = "SELECT * FROM user_subscriptions WHERE id = $subscription_id";
    $updated_result = mysqli_query($conn, $updated_query);
    
    if (!$updated_result) {
        echo "<p style='color:red'>Error retrieving updated data: " . mysqli_error($conn) . "</p>";
    } else {
        if (mysqli_num_rows($updated_result) > 0) {
            $row = mysqli_fetch_assoc($updated_result);
            echo "<table border='1'>";
            foreach ($row as $field => $value) {
                echo "<tr><td>" . htmlspecialchars($field) . "</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange'>No subscription found with ID $subscription_id after update</p>";
        }
    }
    
    // Close connection
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='dashboard.php'>Return to Dashboard</a></p>";
?> 