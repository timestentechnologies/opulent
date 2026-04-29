<?php
// Simple database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

$servername = "localhost";
$username = "opulentl_laundry";
$password = "Phenomenal@254";
$dbname = "opulentl_laundry";

echo "Testing connection to: $servername<br>";
echo "Database: $dbname<br>";
echo "User: $username<br><br>";

// Test 1: Basic connection
echo "Test 1: Basic mysqli connection<br>";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo "❌ Connection failed: " . mysqli_connect_error() . "<br>";
    echo "Error code: " . mysqli_connect_errno() . "<br>";
} else {
    echo "✅ Connection successful<br>";
    
    // Test 2: Simple query
    echo "<br>Test 2: Simple query test<br>";
    $result = mysqli_query($conn, "SELECT 1");
    if ($result) {
        echo "✅ Query successful<br>";
    } else {
        echo "❌ Query failed: " . mysqli_error($conn) . "<br>";
    }
    
    // Test 3: Check if tables exist
    echo "<br>Test 3: Check tables<br>";
    $tables = ['customer', 'service', 'order'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
    
    mysqli_close($conn);
}

// Test 4: PDO connection (alternative)
echo "<br>Test 4: PDO connection test<br>";
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ PDO connection successful<br>";
} catch (PDOException $e) {
    echo "❌ PDO connection failed: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Recommendations:</h3>";
echo "1. If you see 'No such file or directory' errors, MySQL server may not be running<br>";
echo "2. Check if MySQL service is started: systemctl status mysql or service mysql status<br>";
echo "3. If MySQL is running, check the socket file location<br>";
echo "4. Verify database credentials and database existence<br>";
?>
