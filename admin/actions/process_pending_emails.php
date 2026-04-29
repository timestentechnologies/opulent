<?php
/**
 * Process Pending Email Notifications
 * 
 * This script checks for orders that haven't had email notifications sent
 * and sends them. It can be run via a cron job or manually.
 */

// Use __DIR__ to get the absolute path regardless of where the script is called from
$base_path = dirname(__DIR__); // Go up one directory from the actions folder

// Include required files using absolute paths
require_once($base_path . '/connect.php');

// Manually include PHPMailer files to avoid autoload issues
require_once($base_path . '/PHPMailer/class.phpmailer.php');
require_once($base_path . '/PHPMailer/class.smtp.php');

// Include our email helpers
require_once($base_path . '/includes/email_helper.php');

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

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

logMessage("Starting email notification process");

// Add a column to track email notifications if it doesn't exist
try {
    $checkColumnQuery = "SHOW COLUMNS FROM `order` LIKE 'email_sent'";
    $result = $conn->query($checkColumnQuery);
    
    if ($result->num_rows === 0) {
        logMessage("Adding email_sent column to order table");
        $alterTableQuery = "ALTER TABLE `order` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0";
        $conn->query($alterTableQuery);
    }
} catch (Exception $e) {
    logMessage("Error checking/adding email_sent column: " . $e->getMessage());
}

// Find orders that need email notifications
$sql = "SELECT o.id 
        FROM `order` o 
        WHERE (o.email_sent = 0 OR o.email_sent IS NULL) 
        ORDER BY o.id DESC 
        LIMIT 50";

try {
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        logMessage("Found " . $result->num_rows . " orders requiring email notifications");
        
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            try {
                logMessage("Sending notification for order #" . $order_id);
                if (sendOrderNotification($order_id)) {
                    // Mark as sent
                    $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                    $stmt = $conn->prepare($updateSql);
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();
                    logMessage("Notification sent and order marked as processed");
                } else {
                    logMessage("Failed to send notification for order #" . $order_id);
                }
            } catch (Exception $e) {
                logMessage("Error sending notification for order #" . $order_id . ": " . $e->getMessage());
            }
        }
    } else {
        logMessage("No pending email notifications found");
    }
} catch (Exception $e) {
    logMessage("Error querying orders: " . $e->getMessage());
}

logMessage("Email notification process completed");
?> 