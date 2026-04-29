<?php
/**
 * Web interface to manually run the pending email notification processor
 */

// Start the session first
session_start();

// Allow only in development environment or with proper authorization
$allowedIPs = array('127.0.0.1', '::1', $_SERVER['SERVER_ADDR']);

// Skip login requirement if accessed directly or through command line
$isAuthorized = in_array($_SERVER['REMOTE_ADDR'], $allowedIPs);

if (!$isAuthorized) {
    // Only check login if accessed through the web and not from an allowed IP
    if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
        die("Authentication required");
    }
}

// Disable time limit for long-running operations
set_time_limit(0);

// Start output buffering
ob_start();

// Output the beginning of the page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Pending Email Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
        }
        .log-container {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .info {
            color: blue;
        }
    </style>
</head>
<body>
    <h1>Send Pending Email Notifications</h1>
    <p>Processing pending email notifications. Please wait...</p>
    <div class="log-container">
<?php
// Flush the output buffer
ob_flush();
flush();

// Function to output log messages to browser
function outputLog($message, $type = 'info') {
    echo '<span class="' . $type . '">' . date('H:i:s') . ' - ' . htmlspecialchars($message) . '</span>' . PHP_EOL;
    ob_flush();
    flush();
}

// Process pending emails directly instead of including the file
outputLog("Starting to process pending email notifications...");

// Define the base path
$base_path = __DIR__;

try {
    // Include required files directly with absolute paths - Using connect_no_session.php instead
    require_once($base_path . '/connect_no_session.php');
    require_once($base_path . '/PHPMailer/class.phpmailer.php');
    require_once($base_path . '/PHPMailer/class.smtp.php');
    
    // Instead of the email helper, we'll implement the functionality directly
    // to avoid potential session issues in includes/email_helper.php
    
    // Get email configuration from database
    function getEmailConfig($conn) {
        $config = array();
        $query = "SELECT * FROM tbl_email_config WHERE e_id = 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $config = $result->fetch_assoc();
        } else {
            throw new Exception("No email configuration found in the database.");
        }
        
        return $config;
    }
    
    // Set error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Create a log file for tracking
    $logFile = $base_path . '/logs/email_notifications.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Add a column to track email notifications if it doesn't exist
    try {
        $checkColumnQuery = "SHOW COLUMNS FROM `order` LIKE 'email_sent'";
        $result = $conn->query($checkColumnQuery);
        
        if ($result->num_rows === 0) {
            outputLog("Adding email_sent column to order table");
            $alterTableQuery = "ALTER TABLE `order` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0";
            $conn->query($alterTableQuery);
        }
    } catch (Exception $e) {
        outputLog("Error checking/adding email_sent column: " . $e->getMessage(), "error");
    }
    
    // Find orders that need email notifications
    $sql = "SELECT o.id 
            FROM `order` o 
            WHERE (o.email_sent = 0 OR o.email_sent IS NULL) 
            ORDER BY o.id DESC 
            LIMIT 50";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        outputLog("Found " . $result->num_rows . " orders requiring email notifications", "info");
        
        // Get email configuration
        $config = getEmailConfig($conn);
        
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            try {
                outputLog("Processing order #" . $order_id);
                
                // Get order details
                $orderQuery = "SELECT o.*, c.fname, c.lname, c.email, s.name AS service_name 
                              FROM `order` o
                              JOIN customer c ON o.customer_id = c.id
                              JOIN services s ON o.service_id = s.id
                              WHERE o.id = ?";
                              
                $stmt = $conn->prepare($orderQuery);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $conn->error);
                }
                
                $stmt->bind_param("i", $order_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }
                
                $orderResult = $stmt->get_result();
                
                if (!$orderResult || $orderResult->num_rows === 0) {
                    outputLog("Order #" . $order_id . " not found or missing customer/service information", "error");
                    continue;
                }
                
                $order = $orderResult->fetch_assoc();
                
                // Check if customer email exists
                if (empty($order['email'])) {
                    outputLog("Customer email missing for order #" . $order_id, "error");
                    continue;
                }
                
                outputLog("Sending notification for order #" . $order_id . " to " . $order['email']);
                
                // Initialize PHPMailer
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->Host = $config['mail_driver_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['mail_username'];
                $mail->Password = $config['mail_password'];
                $mail->SMTPSecure = $config['mail_encrypt'];
                $mail->Port = $config['mail_port'];
                $mail->setFrom($config['mail_username'], $config['name']);
                
                // Send to customer
                $mail->addAddress($order['email'], $order['fname'] . ' ' . $order['lname']);
                $mail->Subject = 'Your Laundry Order #' . $order['tracking_number'] . ' Has Been Received';
                
                $customerMessage = "
                <html>
                <head>
                    <title>Order Confirmation</title>
                </head>
                <body>
                    <h2>Thank You for Your Order!</h2>
                    <p>Dear {$order['fname']} {$order['lname']},</p>
                    <p>We have received your laundry order. Here are the details:</p>
                    <table border='1' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td><strong>Order ID:</strong></td>
                            <td>{$order['tracking_number']}</td>
                        </tr>
                        <tr>
                            <td><strong>Service:</strong></td>
                            <td>{$order['service_name']}</td>
                        </tr>
                        <tr>
                            <td><strong>Weight:</strong></td>
                            <td>{$order['weight']} kg</td>
                        </tr>
                        <tr>
                            <td><strong>Pickup Date:</strong></td>
                            <td>{$order['pickup_date']}</td>
                        </tr>
                        <tr>
                            <td><strong>Expected Delivery:</strong></td>
                            <td>{$order['delivery_date']}</td>
                        </tr>
                        <tr>
                            <td><strong>Price:</strong></td>
                            <td>KES {$order['price']}</td>
                        </tr>
                    </table>
                    <p>Thank you for choosing Opulent Laundry.</p>
                    <p>Best Regards,<br>The Opulent Laundry Team</p>
                </body>
                </html>
                ";
                
                $mail->isHTML(true);
                $mail->Body = $customerMessage;
                
                // Attempt to send customer email
                if ($mail->send()) {
                    outputLog("Successfully sent email to customer: " . $order['email'], "success");
                    
                    // Reset for admin email
                    $mail->clearAddresses();
                    
                    // Send to admin
                    $mail->addAddress($config['mail_username']);
                    $mail->Subject = 'New Laundry Order #' . $order['tracking_number'];
                    
                    $adminMessage = "
                    <html>
                    <head>
                        <title>New Order Received</title>
                    </head>
                    <body>
                        <h2>New Laundry Order Received!</h2>
                        <p>A new order has been placed. Here are the details:</p>
                        <table border='1' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td><strong>Order ID:</strong></td>
                                <td>{$order['tracking_number']}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer:</strong></td>
                                <td>{$order['fname']} {$order['lname']}</td>
                            </tr>
                            <tr>
                                <td><strong>Service:</strong></td>
                                <td>{$order['service_name']}</td>
                            </tr>
                            <tr>
                                <td><strong>Weight:</strong></td>
                                <td>{$order['weight']} kg</td>
                            </tr>
                            <tr>
                                <td><strong>Pickup Date:</strong></td>
                                <td>{$order['pickup_date']}</td>
                            </tr>
                            <tr>
                                <td><strong>Expected Delivery:</strong></td>
                                <td>{$order['delivery_date']}</td>
                            </tr>
                            <tr>
                                <td><strong>Price:</strong></td>
                                <td>KES {$order['price']}</td>
                            </tr>
                        </table>
                        <p>Please log in to the admin panel to process this order.</p>
                    </body>
                    </html>
                    ";
                    
                    $mail->Body = $adminMessage;
                    
                    if ($mail->send()) {
                        outputLog("Successfully sent email to admin: " . $config['mail_username'], "success");
                        
                        // Update order as processed
                        $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                        $updateStmt = $conn->prepare($updateSql);
                        $updateStmt->bind_param("i", $order_id);
                        $updateStmt->execute();
                        outputLog("Order #" . $order_id . " marked as processed", "success");
                    } else {
                        outputLog("Failed to send admin email: " . $mail->ErrorInfo, "error");
                    }
                } else {
                    outputLog("Failed to send customer email: " . $mail->ErrorInfo, "error");
                }
            } catch (Exception $e) {
                outputLog("Error processing order #" . $order_id . ": " . $e->getMessage(), "error");
            }
        }
    } else {
        outputLog("No pending email notifications found");
    }
    
    outputLog("Email notification processing completed.", "success");
} catch (Exception $e) {
    outputLog("Error: " . $e->getMessage(), "error");
}
?>
    </div>
    <p><a href="index.php">Return to Dashboard</a> | <a href="email_debug.php">Email Debugging Tool</a></p>
</body>
</html>
<?php
ob_end_flush();
?> 