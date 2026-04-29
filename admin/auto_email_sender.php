<?php
/**
 * Auto Email Sender
 * 
 * This script checks for new orders and sends email notifications automatically.
 * It can be called via AJAX, manually, or included in other pages.
 */

// Start the session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for AJAX response
header('Content-Type: application/json');

// Disable time limit for long-running operations
set_time_limit(0);

// Include required files
require_once('connect.php');
require_once('includes/email_helper.php');

// Initialize response array
$response = [
    'success' => false,
    'sent_emails' => 0,
    'messages' => [],
    'errors' => []
];

// Function to log message to response and optionally to log file
function logMessage($message, $type = 'info') {
    global $response;
    
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message;
    
    if ($type == 'error') {
        $response['errors'][] = $logEntry;
        error_log($logEntry);
    } else {
        $response['messages'][] = $logEntry;
    }
    
    // Also log to file
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/email_auto_sender.log';
    file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND);
}

try {
    // Find orders that need email notifications
    $sql = "SELECT o.id 
            FROM `order` o 
            WHERE (o.email_sent = 0 OR o.email_sent IS NULL) 
            ORDER BY o.id DESC";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        logMessage("Found " . $result->num_rows . " orders requiring email notifications");
        
        $sentCount = 0;
        
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            try {
                logMessage("Processing order #" . $order_id);
                
                // Use the sendOrderNotification function from email_helper.php
                if (sendOrderNotification($order_id)) {
                    // Update the email_sent flag
                    $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $order_id);
                        $updateStmt->execute();
                        logMessage("Email sent and order #" . $order_id . " marked as processed", "success");
                        $sentCount++;
                    } else {
                        logMessage("Failed to prepare update statement: " . $conn->error, "error");
                    }
                } else {
                    logMessage("Failed to send notification for order #" . $order_id, "error");
                }
            } catch (Exception $e) {
                logMessage("Error processing order #" . $order_id . ": " . $e->getMessage(), "error");
            }
        }
        
        $response['success'] = true;
        $response['sent_emails'] = $sentCount;
        
    } else {
        logMessage("No pending email notifications found");
        $response['success'] = true;
        $response['sent_emails'] = 0;
    }
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), "error");
    $response['success'] = false;
}

// Return JSON response if called via AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo json_encode($response);
    exit;
}

// If not AJAX, and we're accessed directly, show a basic HTML output
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Auto Email Sender</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                line-height: 1.6;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
            }
            h1 {
                color: #333;
            }
            .logs {
                background-color: #f5f5f5;
                border: 1px solid #ddd;
                padding: 15px;
                margin-top: 20px;
                font-family: monospace;
                white-space: pre-wrap;
                max-height: 400px;
                overflow-y: auto;
            }
            .success { color: green; }
            .error { color: red; }
            .refresh-button {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
            .auto-refresh {
                margin-top: 10px;
                display: flex;
                align-items: center;
            }
            .auto-refresh label {
                margin-left: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Auto Email Sender</h1>
            
            <div>
                <p>Status: <strong><?php echo $response['success'] ? 'Success' : 'Error'; ?></strong></p>
                <p>Emails Sent: <strong><?php echo $response['sent_emails']; ?></strong></p>
            </div>
            
            <div class="auto-refresh">
                <input type="checkbox" id="auto-refresh" checked>
                <label for="auto-refresh">Auto-refresh every 30 seconds</label>
            </div>
            
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="refresh-button">Check for New Orders Now</a>
            
            <h2>Log Messages</h2>
            <div class="logs">
                <?php foreach ($response['messages'] as $message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
                <?php endforeach; ?>
                
                <?php foreach ($response['errors'] as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
            // Auto-refresh functionality
            let autoRefreshCheckbox = document.getElementById('auto-refresh');
            let autoRefreshTimer = null;
            
            function toggleAutoRefresh() {
                if (autoRefreshCheckbox.checked) {
                    autoRefreshTimer = setInterval(function() {
                        window.location.reload();
                    }, 30000); // 30 seconds
                } else {
                    if (autoRefreshTimer) {
                        clearInterval(autoRefreshTimer);
                    }
                }
            }
            
            autoRefreshCheckbox.addEventListener('change', toggleAutoRefresh);
            
            // Start auto-refresh if checked
            toggleAutoRefresh();
        </script>
    </body>
    </html>
    <?php
}
?> 