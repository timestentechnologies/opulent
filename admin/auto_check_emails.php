<?php
/**
 * Auto Check Emails
 * 
 * This script can be included in other pages to automatically check for new orders
 * and send emails on every page load.
 * 
 * Usage: include('auto_check_emails.php'); at the beginning of your PHP files
 */

// Skip if this is an AJAX request to avoid triggering multiple times
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    return;
}

// Skip during form submissions to avoid slowing down the user experience
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return;
}

// We'll use a simple file-based lock to prevent multiple concurrent processes
$lockFile = __DIR__ . '/email_process.lock';
$logFile = __DIR__ . '/logs/auto_check_emails.log';

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Function to write to log file
function autoLogMessage($message) {
    global $logFile;
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Check if another process is already running (with a timeout)
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    // If lock is older than 5 minutes, it's probably stale
    if (time() - $lockTime < 300) {
        // Process is already running, exit
        return;
    }
    // Otherwise, remove the stale lock
    unlink($lockFile);
}

// Create lock file
file_put_contents($lockFile, date('Y-m-d H:i:s'));

try {
    // Include necessary files
    require_once(__DIR__ . '/connect.php');
    require_once(__DIR__ . '/includes/email_helper.php');

    // Find orders that need email notifications
    $sql = "SELECT o.id FROM `order` o WHERE (o.email_sent = 0 OR o.email_sent IS NULL) ORDER BY o.id DESC LIMIT 5";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        autoLogMessage("Found " . $result->num_rows . " orders requiring email notifications");
        
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            try {
                autoLogMessage("Processing order #" . $order_id);
                
                // Use the sendOrderNotification function from email_helper.php
                if (sendOrderNotification($order_id)) {
                    // Update the email_sent flag
                    $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $order_id);
                        $updateStmt->execute();
                        autoLogMessage("Email sent and order #" . $order_id . " marked as processed");
                    } else {
                        autoLogMessage("Failed to prepare update statement: " . $conn->error);
                    }
                } else {
                    autoLogMessage("Failed to send notification for order #" . $order_id);
                }
            } catch (Exception $e) {
                autoLogMessage("Error processing order #" . $order_id . ": " . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    autoLogMessage("Error: " . $e->getMessage());
} finally {
    // Remove lock file when done
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}
?> 