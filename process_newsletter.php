<?php
// Include database connection
include 'admin/connect.php';

// Set header to return JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if email is submitted
if (!isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email address is required'
    ]);
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

try {
    // First, check if the table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'email_subscribers'");
    if ($tableCheck->num_rows == 0) {
        // Create the table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS email_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (email)
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM email_subscribers WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'This email is already subscribed to our newsletter'
        ]);
        exit;
    }
    
    // Close the first statement
    $stmt->close();

    // Insert new subscriber
    $stmt = $conn->prepare("INSERT INTO email_subscribers (email) VALUES (?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Insert failed: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing to our newsletter!'
    ]);

} catch (Exception $e) {
    // Log the error for debugging
    error_log("Newsletter subscription error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// Close connections
if (isset($stmt) && $stmt) {
    $stmt->close();
}
if (isset($conn) && $conn) {
    $conn->close();
}
?>