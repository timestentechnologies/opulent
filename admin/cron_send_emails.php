<?php
/**
 * Email Notification Cron Job
 * 
 * This script is designed to be run by a cron job to send email notifications
 * for orders that haven't received them yet.
 * 
 * Example cron entry (run every 15 minutes):
 * */15 * * * * php /path/to/cron_send_emails.php > /dev/null 2>&1
 */

// Prevent direct access through the web
if (isset($_SERVER['REMOTE_ADDR'])) {
    die("This script can only be run from the command line");
}

// Set error reporting and disable time limit
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

// Define the base path
$base_path = __DIR__;

// Include required files
require_once($base_path . '/connect.php');
require_once($base_path . '/includes/email_helper.php');

// Create a log file for tracking
$logFile = $base_path . '/logs/cron_emails.log';
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

logMessage("Starting cron job for email notifications");

// Find orders that need email notifications
$sql = "SELECT o.id 
        FROM `order` o 
        WHERE (o.email_sent = 0 OR o.email_sent IS NULL) 
        ORDER BY o.id DESC 
        LIMIT 50";

try {
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
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
                // Try again next time
            }
            
            // Sleep briefly to prevent email server throttling
            sleep(2);
        }
    } else {
        logMessage("No pending email notifications found");
    }
} catch (Exception $e) {
    logMessage("Error querying orders: " . $e->getMessage());
}

logMessage("Cron job completed");
?> 