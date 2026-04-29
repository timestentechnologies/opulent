<?php
/**
 * Email Helper Functions
 * 
 * Functions for sending email notifications for various events
 */

// Include required files
require_once(__DIR__ . '/../connect.php');
require_once(__DIR__ . '/../PHPMailer/PHPMailerAutoload.php');

/**
 * Get email configuration from database
 */
function getEmailConfig() {
    global $conn;
    
    $config = array();
    $query = "SELECT * FROM tbl_email_config WHERE e_id = 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $config = $result->fetch_assoc();
    }
    
    return $config;
}

/**
 * Initialize PHPMailer with configuration settings
 */
function initMailer() {
    $config = getEmailConfig();
    
    // Make sure all required libraries are loaded
    require_once(__DIR__ . '/../PHPMailer/PHPMailerAutoload.php');
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true); // true enables exceptions
    
    try {
        // Configure SMTP settings
        $mail->isSMTP();
        $mail->Host = $config['mail_driver_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['mail_username'];
        $mail->Password = $config['mail_password'];
        $mail->SMTPSecure = $config['mail_encrypt'];
        $mail->Port = $config['mail_port'];
        $mail->setFrom($config['mail_username'], $config['name']);
        
        // Optional debug settings for troubleshooting
        $mail->SMTPDebug = 0; // 0 = no output, 1 = client messages, 2 = client and server messages
        
        return $mail;
    } catch (Exception $e) {
        error_log('PHPMailer initialization error: ' . $e->getMessage());
        throw new Exception('Failed to initialize email system: ' . $e->getMessage());
    }
}

/**
 * Send order confirmation email
 */
function sendOrderNotification($order_id) {
    global $conn;
    
    // Get order details
    $sql = "SELECT o.*, c.fname, c.lname, c.email, s.sname AS service_name 
            FROM `order` o
            JOIN customer c ON o.customer_id = c.id
            JOIN service s ON o.service_id = s.id
            WHERE o.id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Check if customer email exists
        if (empty($order['email'])) {
            error_log("Customer email missing for order #" . $order_id);
            return false;
        }
        
        try {
            // Initialize mailer
            $mail = initMailer();
            
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
            
            try {
                $mail->send();
            } catch (Exception $e) {
                error_log("Failed to send customer email for order #" . $order_id . ": " . $e->getMessage());
                // Continue to try sending admin email
            }
            
            // Reset for admin email
            $mail->clearAddresses();
            
            // Get admin email from config
            $config = getEmailConfig();
            $adminEmail = $config['mail_username']; // Using sender email as admin email
            
            if (empty($adminEmail)) {
                error_log("Admin email configuration missing");
                return false;
            }
            
            // Send to admin
            $mail->addAddress($adminEmail);
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
            
            $mail->isHTML(true);
            $mail->Body = $adminMessage;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error in email notification process: " . $e->getMessage());
            return false;
        }
    } else {
        error_log("Order #" . $order_id . " not found or missing required relationships");
        return false;
    }
}

/**
 * Send inquiry notification email
 */
function sendInquiryNotification($inquiry_id) {
    global $conn;
    
    // Get inquiry details
    $sql = "SELECT * FROM inquiries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inquiry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $inquiry = $result->fetch_assoc();
        
        // Initialize mailer
        $mail = initMailer();
        
        // Send confirmation to customer
        if (!empty($inquiry['email'])) {
            $mail->addAddress($inquiry['email'], $inquiry['name']);
            $mail->Subject = 'We Received Your Inquiry - Opulent Laundry';
            
            $customerMessage = "
            <html>
            <head>
                <title>Inquiry Confirmation</title>
            </head>
            <body>
                <h2>Thank You for Contacting Us!</h2>
                <p>Dear {$inquiry['name']},</p>
                <p>We have received your inquiry. A member of our team will get back to you shortly.</p>
                <p>Reference Number: INQ-{$inquiry_id}</p>
                <p>Here's what you sent us:</p>
                <p><strong>Subject:</strong> {$inquiry['subject']}</p>
                <p><strong>Message:</strong><br>{$inquiry['message']}</p>
                <p>Thank you for choosing Opulent Laundry.</p>
                <p>Best Regards,<br>The Opulent Laundry Team</p>
            </body>
            </html>
            ";
            
            $mail->isHTML(true);
            $mail->Body = $customerMessage;
            $mail->send();
            
            // Reset for admin email
            $mail->clearAddresses();
        }
        
        // Get admin email from config
        $config = getEmailConfig();
        $adminEmail = $config['mail_username']; // Using sender email as admin email
        
        // Send notification to admin
        $mail->addAddress($adminEmail);
        $mail->Subject = 'New Customer Inquiry Received - ' . $inquiry['subject'];
        
        $adminMessage = "
        <html>
        <head>
            <title>New Inquiry Received</title>
        </head>
        <body>
            <h2>New Customer Inquiry</h2>
            <p>A new inquiry has been submitted. Here are the details:</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><strong>Reference ID:</strong></td>
                    <td>INQ-{$inquiry_id}</td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{$inquiry['name']}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{$inquiry['email']}</td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td>{$inquiry['phone']}</td>
                </tr>
                <tr>
                    <td><strong>Subject:</strong></td>
                    <td>{$inquiry['subject']}</td>
                </tr>
                <tr>
                    <td><strong>Message:</strong></td>
                    <td>{$inquiry['message']}</td>
                </tr>
            </table>
            <p>Please log in to the admin panel to respond to this inquiry.</p>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        $mail->Body = $adminMessage;
        return $mail->send();
    }
    
    return false;
}

/**
 * Send subscription confirmation email
 */
function sendSubscriptionNotification($subscription_id) {
    global $conn;
    
    // Get subscription details
    $sql = "SELECT us.*, c.fname, c.lname, c.email, sp.name as plan_name, sp.price, sp.duration 
            FROM user_subscriptions us 
            JOIN customer c ON us.customer_id = c.id 
            JOIN subscription_plans sp ON us.plan_id = sp.id 
            WHERE us.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subscription_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $subscription = $result->fetch_assoc();
        
        // Initialize mailer
        $mail = initMailer();
        
        // Send to customer
        $mail->addAddress($subscription['email'], $subscription['fname'] . ' ' . $subscription['lname']);
        $mail->Subject = 'Your Subscription to ' . $subscription['plan_name'] . ' Plan';
        
        $customerMessage = "
        <html>
        <head>
            <title>Subscription Confirmation</title>
        </head>
        <body>
            <h2>Thank You for Your Subscription!</h2>
            <p>Dear {$subscription['fname']} {$subscription['lname']},</p>
            <p>Thank you for subscribing to our services. Here are your subscription details:</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><strong>Subscription ID:</strong></td>
                    <td>{$subscription['id']}</td>
                </tr>
                <tr>
                    <td><strong>Plan:</strong></td>
                    <td>{$subscription['plan_name']}</td>
                </tr>
                <tr>
                    <td><strong>Price:</strong></td>
                    <td>KES {$subscription['price']}</td>
                </tr>
                <tr>
                    <td><strong>Duration:</strong></td>
                    <td>{$subscription['duration']} days</td>
                </tr>
                <tr>
                    <td><strong>Start Date:</strong></td>
                    <td>{$subscription['start_date']}</td>
                </tr>
                <tr>
                    <td><strong>Expiry Date:</strong></td>
                    <td>{$subscription['end_date']}</td>
                </tr>
            </table>
            <p>Thank you for choosing Opulent Laundry.</p>
            <p>Best Regards,<br>The Opulent Laundry Team</p>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        $mail->Body = $customerMessage;
        $mail->send();
        
        // Reset for admin email
        $mail->clearAddresses();
        
        // Get admin email from config
        $config = getEmailConfig();
        $adminEmail = $config['mail_username']; // Using sender email as admin email
        
        // Send to admin
        $mail->addAddress($adminEmail);
        $mail->Subject = 'New Subscription: ' . $subscription['fname'] . ' ' . $subscription['lname'] . ' - ' . $subscription['plan_name'];
        
        $adminMessage = "
        <html>
        <head>
            <title>New Subscription Alert</title>
        </head>
        <body>
            <h2>New Subscription Purchase!</h2>
            <p>A customer has subscribed to a service plan. Here are the details:</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><strong>Subscription ID:</strong></td>
                    <td>{$subscription['id']}</td>
                </tr>
                <tr>
                    <td><strong>Customer:</strong></td>
                    <td>{$subscription['fname']} {$subscription['lname']}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{$subscription['email']}</td>
                </tr>
                <tr>
                    <td><strong>Plan:</strong></td>
                    <td>{$subscription['plan_name']}</td>
                </tr>
                <tr>
                    <td><strong>Price:</strong></td>
                    <td>KES {$subscription['price']}</td>
                </tr>
                <tr>
                    <td><strong>Duration:</strong></td>
                    <td>{$subscription['duration']} days</td>
                </tr>
                <tr>
                    <td><strong>Start Date:</strong></td>
                    <td>{$subscription['start_date']}</td>
                </tr>
                <tr>
                    <td><strong>Expiry Date:</strong></td>
                    <td>{$subscription['end_date']}</td>
                </tr>
            </table>
            <p>Please log in to the admin panel to view this subscription.</p>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        $mail->Body = $adminMessage;
        return $mail->send();
    }
    
    return false;
}

/**
 * Send newsletter subscription confirmation
 */
function sendNewsletterConfirmation($subscriber_id) {
    global $conn;
    
    // Get subscriber details
    $sql = "SELECT * FROM newsletter_subscribers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subscriber_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        
        // Initialize mailer
        $mail = initMailer();
        
        // Send confirmation to subscriber
        $mail->addAddress($subscriber['email']);
        $mail->Subject = 'Thank You for Subscribing to Our Newsletter';
        
        $subscriberMessage = "
        <html>
        <head>
            <title>Newsletter Subscription</title>
        </head>
        <body>
            <h2>Thank You for Subscribing!</h2>
            <p>You have successfully subscribed to the Opulent Laundry newsletter.</p>
            <p>You'll now receive updates about our services, special offers, and tips for laundry care.</p>
            <p>If you wish to unsubscribe at any time, please click the unsubscribe link in any of our emails.</p>
            <p>Thank you for choosing Opulent Laundry.</p>
            <p>Best Regards,<br>The Opulent Laundry Team</p>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        $mail->Body = $subscriberMessage;
        $mail->send();
        
        // Reset for admin email
        $mail->clearAddresses();
        
        // Get admin email from config
        $config = getEmailConfig();
        $adminEmail = $config['mail_username']; // Using sender email as admin email
        
        // Send notification to admin
        $mail->addAddress($adminEmail);
        $mail->Subject = 'New Newsletter Subscriber';
        
        $adminMessage = "
        <html>
        <head>
            <title>New Newsletter Subscriber</title>
        </head>
        <body>
            <h2>New Newsletter Subscriber</h2>
            <p>A new user has subscribed to your newsletter. Here are the details:</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{$subscriber['email']}</td>
                </tr>
                <tr>
                    <td><strong>Date Subscribed:</strong></td>
                    <td>{$subscriber['date_subscribed']}</td>
                </tr>
            </table>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        $mail->Body = $adminMessage;
        return $mail->send();
    }
    
    return false;
} 