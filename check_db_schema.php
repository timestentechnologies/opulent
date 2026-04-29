<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Database schema check beginning...\n";

// Include database connection with error checking
try {
    require_once 'includes/db_connection.php';
    echo "Database connection successful\n";
} catch (Exception $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if $conn is defined and is a valid mysqli connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "Error: Database connection is not valid\n";
    exit(1);
}

// Check if the user_subscriptions table exists
try {
    $result = $conn->query('SHOW TABLES LIKE "user_subscriptions"');
    if (!$result) {
        echo "Error checking tables: " . $conn->error . "\n";
        exit(1);
    }
    
    if ($result->num_rows == 0) {
        echo "user_subscriptions table does not exist!\n";
        exit(1);
    } else {
        echo "user_subscriptions table exists\n";
    }
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if columns exist
$columns = ['id', 'customer_id', 'plan_id', 'status', 'payment_method', 'mobile', 'code'];
$missing = [];

try {
    $result = $conn->query('DESCRIBE user_subscriptions');
    if (!$result) {
        echo "Error describing table: " . $conn->error . "\n";
        exit(1);
    }
    
    $existingColumns = [];
    while ($row = $result->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
        echo "Found column: " . $row['Field'] . "\n";
    }

    foreach ($columns as $column) {
        if (!in_array($column, $existingColumns)) {
            $missing[] = $column;
        }
    }
} catch (Exception $e) {
    echo "Error describing table: " . $e->getMessage() . "\n";
    exit(1);
}

if (count($missing) > 0) {
    echo "Missing columns in user_subscriptions table: " . implode(', ', $missing) . "\n";
    
    // Add missing columns
    foreach ($missing as $column) {
        $sql = '';
        switch ($column) {
            case 'payment_method':
                $sql = 'ALTER TABLE user_subscriptions ADD COLUMN payment_method VARCHAR(50) NULL';
                break;
            case 'mobile':
                $sql = 'ALTER TABLE user_subscriptions ADD COLUMN mobile VARCHAR(20) NULL';
                break;
            case 'code':
                $sql = 'ALTER TABLE user_subscriptions ADD COLUMN code VARCHAR(20) NULL';
                break;
            default:
                echo "Skipping unknown column: $column\n";
                continue;
        }
        
        if (!empty($sql)) {
            echo "Executing: $sql\n";
            try {
                if ($conn->query($sql)) {
                    echo "Added column $column successfully\n";
                } else {
                    echo "Failed to add column $column: {$conn->error}\n";
                }
            } catch (Exception $e) {
                echo "Error adding column $column: " . $e->getMessage() . "\n";
            }
        }
    }
} else {
    echo "All required columns exist in user_subscriptions table.\n";
}

$conn->close();
echo "Database schema check completed.\n";
?> 