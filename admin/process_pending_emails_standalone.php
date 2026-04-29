<?php
/**
 * Standalone script to process pending email notifications
 * This script does not rely on any session handling
 */

// Disable time limit for long-running operations
set_time_limit(0);

// Define the base path
$base_path = __DIR__;

// Function to log messages
function logMessage($message, $type = 'INFO') {
    echo date('Y-m-d H:i:s') . ' [' . $type . '] ' . $message . PHP_EOL;
}

logMessage("Starting to process pending email notifications...");

try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "opulentl_laundry";
    
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Include PHPMailer classes directly
    require_once($base_path . '/PHPMailer/class.phpmailer.php');
    require_once($base_path . '/PHPMailer/class.smtp.php');
    
    // Get email configuration from database
    function getEmailConfig($conn) {
        $config = array();
        $query = "SELECT * FROM tbl_email_config WHERE e_id = 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $config = $result->fetch_assoc();
            logMessage("Email config loaded: " . $config['mail_driver_host']);
        } else {
            throw new Exception("No email configuration found in the database.");
        }
        
        return $config;
    }
    
    // Create a logs directory if it doesn't exist
    $logDir = $base_path . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Add email_sent column if it doesn't exist
    try {
        $checkColumnQuery = "SHOW COLUMNS FROM `order` LIKE 'email_sent'";
        $result = $conn->query($checkColumnQuery);
        
        if ($result->num_rows === 0) {
            logMessage("Adding email_sent column to order table");
            $alterTableQuery = "ALTER TABLE `order` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0";
            $conn->query($alterTableQuery);
        }
    } catch (Exception $e) {
        logMessage("Error checking/adding email_sent column: " . $e->getMessage(), "ERROR");
    }
    
    // Find orders that need email notifications
    $sql = "SELECT o.id 
            FROM `order` o 
            WHERE (o.email_sent = 0 OR o.email_sent IS NULL) 
            ORDER BY o.id DESC 
            LIMIT 50";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        logMessage("Found " . $result->num_rows . " orders requiring email notifications");
        
        // Get email configuration
        $config = getEmailConfig($conn);
        
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            try {
                logMessage("Processing order #" . $order_id);
                
                // Get order details - Fixed JOIN using service table instead of services
                $orderQuery = "SELECT o.*, c.fname, c.lname, c.email, s.sname AS service_name 
                              FROM `order` o
                              JOIN customer c ON o.customer_id = c.id
                              JOIN service s ON o.service_id = s.id
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
                    logMessage("Order #" . $order_id . " not found or missing customer/service information", "ERROR");
                    continue;
                }
                
                $order = $orderResult->fetch_assoc();
                
                // Check if customer email exists
                if (empty($order['email'])) {
                    logMessage("Customer email missing for order #" . $order_id, "ERROR");
                    continue;
                }
                
                logMessage("Sending notification for order #" . $order_id . " to " . $order['email']);
                
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
                
                // Debug mode
                $mail->SMTPDebug = 2; // Set to 2 for verbose debug output
                
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
                    logMessage("Successfully sent email to customer: " . $order['email'], "SUCCESS");
                    
                    // Reset for admin email
                    $mail->clearAddresses();
                    $mail->SMTPDebug = 0; // Turn off debug for admin email
                    
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
                        logMessage("Successfully sent email to admin: " . $config['mail_username'], "SUCCESS");
                        
                        // Update order as processed
                        $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                        $updateStmt = $conn->prepare($updateSql);
                        $updateStmt->bind_param("i", $order_id);
                        $updateStmt->execute();
                        logMessage("Order #" . $order_id . " marked as processed", "SUCCESS");
                    } else {
                        logMessage("Failed to send admin email: " . $mail->ErrorInfo, "ERROR");
                    }
                } else {
                    logMessage("Failed to send customer email: " . $mail->ErrorInfo, "ERROR");
                }
            } catch (Exception $e) {
                logMessage("Error processing order #" . $order_id . ": " . $e->getMessage(), "ERROR");
            }
        }
    } else {
        logMessage("No pending email notifications found");
    }
    
    logMessage("Email notification processing completed successfully.");
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), "ERROR");
}
?> 