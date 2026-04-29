<?php

// Enable error reporting but don't display errors directly
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header to return JSON response
header('Content-Type: application/json');

try {
    // Include database connection
    require_once 'admin/connect.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Debug: Log session data
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("POST data: " . print_r($_POST, true));

    // Check if required fields are present
    if (!isset($_POST['plan_id'], $_POST['start_date'], $_POST['end_date'])) {
        throw new Exception("Missing required fields: " . 
            (!isset($_POST['plan_id']) ? 'plan_id ' : '') .
            (!isset($_POST['start_date']) ? 'start_date ' : '') .
            (!isset($_POST['end_date']) ? 'end_date ' : ''));
    }

    // Validate and sanitize input
    $planId = filter_var($_POST['plan_id'], FILTER_SANITIZE_NUMBER_INT);
    $startDate = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);
    $endDate = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);
    $customerId = $_SESSION['customer_id'] ?? null;

    // Debug: Log processed data
    error_log("Processed data - Plan ID: $planId, Start Date: $startDate, End Date: $endDate, Customer ID: $customerId");

    // Check if session is active
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception("Please log in to subscribe to a plan. Session may have expired.");
    }
    $customerId = $_SESSION['customer_id'];

    // First, create subscription_plans table if it doesn't exist
    $createPlansTable = "CREATE TABLE IF NOT EXISTS subscription_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        duration INT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($createPlansTable)) {
        error_log("Error creating plans table: " . $conn->error);
        throw new Exception("Error creating plans table: " . $conn->error);
    }

    // Check if plans exist
    $plansCheck = $conn->query("SELECT COUNT(*) as count FROM subscription_plans");
    $plansCount = $plansCheck->fetch_object()->count;
    
    if ($plansCount == 0) {
        // Insert default plans
        $insertPlans = "INSERT INTO subscription_plans (name, price, duration, description) VALUES 
            ('Student Saver', 2000.00, 1, 'Perfect for students'),
            ('Bachelor''s Bundle', 4000.00, 1, 'Ideal for working professionals'),
            ('Family Comfort', 7000.00, 1, 'Best for families')";
        
        if (!$conn->query($insertPlans)) {
            error_log("Error inserting default plans: " . $conn->error);
            throw new Exception("Error inserting default plans: " . $conn->error);
        }
    }

    // Create user_subscriptions table if it doesn't exist
    $createSubsTable = "CREATE TABLE IF NOT EXISTS user_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        plan_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('pending', 'active', 'expired', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($createSubsTable)) {
        error_log("Error creating subscriptions table: " . $conn->error . ". SQL: " . $createSubsTable);
        throw new Exception("Error creating subscriptions table: " . $conn->error . ". SQL: " . $createSubsTable);
    }

    // Validate customer exists
    $stmt = $conn->prepare("SELECT id FROM customer WHERE id = ?");
    if (!$stmt) {
        error_log("Error preparing customer check: " . $conn->error);
        throw new Exception("Error preparing customer check: " . $conn->error);
    }
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Customer ID not found in database");
        throw new Exception("Customer ID not found in database");
    }
    $stmt->close();

    // Validate plan exists
    $stmt = $conn->prepare("SELECT id FROM subscription_plans WHERE id = ?");
    if (!$stmt) {
        error_log("Error preparing plan check: " . $conn->error);
        throw new Exception("Error preparing plan check: " . $conn->error);
    }
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Selected plan does not exist");
        throw new Exception("Selected plan does not exist");
    }
    $stmt->close();

    // Check for existing active subscription
    $stmt = $conn->prepare("SELECT id FROM user_subscriptions WHERE customer_id = ? AND status = 'active' AND end_date >= CURRENT_DATE()");
    if (!$stmt) {
        error_log("Error preparing subscription check: " . $conn->error);
        throw new Exception("Error preparing subscription check: " . $conn->error);
    }
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        error_log("You already have an active subscription");
        throw new Exception("You already have an active subscription");
    }
    $stmt->close();

    // Insert new subscription
    $stmt = $conn->prepare("INSERT INTO user_subscriptions (customer_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')");
    if (!$stmt) {
        error_log("Error preparing subscription insert: " . $conn->error);
        throw new Exception("Error preparing subscription insert: " . $conn->error);
    }
    $stmt->bind_param("iiss", $customerId, $planId, $startDate, $endDate);
    if (!$stmt->execute()) {
        error_log("Error creating subscription: " . $stmt->error);
        throw new Exception("Error creating subscription: " . $stmt->error);
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Subscription created successfully'
    ]);

} catch (Exception $e) {
    error_log("Subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>