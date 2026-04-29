<?php
/**
 * Email Configuration Testing Tool
 * 
 * This script allows admin users to test the email configuration and sending functionality.
 */

// Require authentication
require_once('session_handler.php');

// Check if user is logged in
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

require_once('connect.php');
require_once('includes/email_helper.php');

$message = '';
$messageType = '';

// Handle test email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'test_email') {
        $recipient = $_POST['recipient_email'] ?? '';
        
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address";
            $messageType = "error";
        } else {
            try {
                // Initialize the mailer
                $mail = initMailer();
                
                // Set recipient
                $mail->addAddress($recipient);
                
                // Set email content
                $mail->Subject = 'Test Email from Opulent Laundry';
                $mail->isHTML(true);
                $mail->Body = '
                <html>
                <head>
                    <title>Test Email</title>
                </head>
                <body>
                    <h2>Opulent Laundry Email Test</h2>
                    <p>This is a test email sent from the Opulent Laundry system.</p>
                    <p>If you are receiving this email, it means that the email configuration is working correctly.</p>
                    <p>Email configuration details:</p>
                    <ul>
                        <li>SMTP Host: ' . htmlspecialchars($mail->Host) . '</li>
                        <li>SMTP Port: ' . htmlspecialchars($mail->Port) . '</li>
                        <li>SMTP Username: ' . htmlspecialchars($mail->Username) . '</li>
                        <li>Encryption: ' . htmlspecialchars($mail->SMTPSecure) . '</li>
                    </ul>
                    <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
                </body>
                </html>';
                
                // Send email
                if ($mail->send()) {
                    $message = "Test email sent successfully to $recipient. Please check your inbox (and spam folder)";
                    $messageType = "success";
                } else {
                    $message = "Failed to send test email: " . $mail->ErrorInfo;
                    $messageType = "error";
                }
            } catch (Exception $e) {
                $message = "Error sending test email: " . $e->getMessage();
                $messageType = "error";
            }
        }
    } else if ($_POST['action'] === 'test_order') {
        // Test order notification
        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        
        if ($orderId <= 0) {
            $message = "Please enter a valid order ID";
            $messageType = "error";
        } else {
            try {
                // Buffer output for debug info
                ob_start();
                
                // Directly send the email without using helper functions
                // Check if order exists and get details
                $sql = "SELECT o.*, c.fname, c.lname, c.email, s.sname AS service_name 
                        FROM `order` o
                        JOIN customer c ON o.customer_id = c.id
                        JOIN service s ON o.service_id = s.id
                        WHERE o.id = ?";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $conn->error);
                }
                
                $stmt->bind_param("i", $orderId);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Order #$orderId not found or missing related information");
                }
                
                $order = $result->fetch_assoc();
                
                // Check if customer email exists
                if (empty($order['email'])) {
                    throw new Exception("Customer email missing for order #" . $orderId);
                }
                
                // Get email config
                $config = getEmailConfig();
                
                // Create PHPMailer instance
                $mail = new PHPMailer(true); // Enable exceptions
                $mail->isSMTP();
                $mail->Host = $config['mail_driver_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['mail_username'];
                $mail->Password = $config['mail_password'];
                $mail->SMTPSecure = $config['mail_encrypt'];
                $mail->Port = $config['mail_port'];
                $mail->setFrom($config['mail_username'], $config['name']);
                $mail->SMTPDebug = 2; // Debug level
                
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
                
                if ($mail->send()) {
                    $mail->clearAddresses();
                    
                    // Now send admin notification
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
                        // Update the email_sent flag
                        try {
                            // First check if email_sent column exists
                            $checkColumnSql = "SHOW COLUMNS FROM `order` LIKE 'email_sent'";
                            $columnResult = $conn->query($checkColumnSql);
                            
                            if ($columnResult && $columnResult->num_rows === 0) {
                                // Column doesn't exist, add it
                                $alterTableSql = "ALTER TABLE `order` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0";
                                if (!$conn->query($alterTableSql)) {
                                    throw new Exception("Failed to add email_sent column: " . $conn->error);
                                }
                            }
                            
                            // Now update the flag
                            $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                            $updateStmt = $conn->prepare($updateSql);
                            
                            if ($updateStmt === false) {
                                throw new Exception("Failed to prepare update statement: " . $conn->error);
                            }
                            
                            $updateStmt->bind_param("i", $orderId);
                            $updateStmt->execute();
                        } catch (Exception $updateError) {
                            // If the update fails, just log the error but still show success for email sending
                            error_log("Failed to update email_sent flag: " . $updateError->getMessage());
                        }
                        
                        $message = "Order notification for order #$orderId sent successfully to both customer and admin";
                        $messageType = "success";
                    } else {
                        throw new Exception("Failed to send admin email: " . $mail->ErrorInfo);
                    }
                } else {
                    throw new Exception("Failed to send customer email: " . $mail->ErrorInfo);
                }
                
                $debug_output = ob_get_clean();
                
            } catch (Exception $e) {
                $debug_output = ob_get_clean();
                $message = "Error processing order notification: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

// Get email configuration
$emailConfig = getEmailConfig();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test</title>
    <?php include('head.php'); ?>
    <style>
        .test-section {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="fix-header fix-sidebar">
    <?php include('header.php'); ?>
    <?php include('sidebar.php'); ?>
    
    <!-- Page wrapper  -->
    <div class="page-wrapper">
        <!-- Bread crumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Email Configuration Test</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Email Test</li>
                </ol>
            </div>
        </div>
        <!-- End Bread crumb -->
        
        <!-- Container fluid  -->
        <div class="container-fluid">
            <!-- Start Page Content -->
            <div class="row">
                <div class="col-lg-12">
                    <?php if (!empty($message)): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Current Email Configuration</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Name</th>
                                        <td><?php echo htmlspecialchars($emailConfig['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>SMTP Host</th>
                                        <td><?php echo htmlspecialchars($emailConfig['mail_driver_host']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>SMTP Port</th>
                                        <td><?php echo htmlspecialchars($emailConfig['mail_port']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?php echo htmlspecialchars($emailConfig['mail_username']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Encryption</th>
                                        <td><?php echo htmlspecialchars($emailConfig['mail_encrypt']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <a href="email_config.php" class="btn btn-primary mt-3">Edit Configuration</a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Send Test Email</h4>
                            <div class="test-section">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="recipient_email">Recipient Email Address</label>
                                        <input type="email" name="recipient_email" id="recipient_email" class="form-control" required>
                                    </div>
                                    <input type="hidden" name="action" value="test_email">
                                    <button type="submit" class="btn btn-info">Send Test Email</button>
                                </form>
                            </div>
                            
                            <h4 class="card-title mt-4">Test Order Notification</h4>
                            <div class="test-section">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="order_id">Order ID</label>
                                        <input type="number" name="order_id" id="order_id" class="form-control" required min="1">
                                    </div>
                                    <input type="hidden" name="action" value="test_order">
                                    <button type="submit" class="btn btn-info">Test Order Notification</button>
                                </form>
                            </div>
                            
                            <h4 class="card-title mt-4">Process All Pending Notifications</h4>
                            <div class="test-section">
                                <p>Run the script to process all pending email notifications.</p>
                                <a href="send_pending_notifications.php" class="btn btn-warning" target="_blank">Process Pending Notifications</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Page Content -->
        </div>
        <!-- End Container fluid  -->
        
        <?php include('footer.php'); ?>
    </div>
    <!-- End Page wrapper  -->
</body>
</html> 