<?php
/**
 * Web interface to run the standalone email processing script
 */

// Start the session first (but standalone script doesn't need it)
session_start();

// Basic access control
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    // Allow local access regardless of login
    $allowedIPs = array('127.0.0.1', '::1', $_SERVER['SERVER_ADDR']);
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        die("Authentication required");
    }
}

// Check if we should run the processor
$run_processor = isset($_GET['run']) && $_GET['run'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Processor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            margin-right: 10px;
        }
        .button.blue {
            background-color: #2196F3;
        }
        .output {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Notification Processor</h1>
        
        <?php if (!$run_processor): ?>
            <p>Click the button below to process pending email notifications. This will send emails for any pending orders that haven't been notified yet.</p>
            <a href="?run=1" class="button">Process Pending Email Notifications</a>
            <a href="index.php" class="button blue">Return to Dashboard</a>
        <?php else: ?>
            <h2>Processing Results</h2>
            <div class="output">
<?php
// Run the standalone processor and capture output
ob_start();
include 'process_pending_emails_standalone.php';
$output = ob_get_clean();
echo htmlspecialchars($output);
?>
            </div>
            <a href="process_emails.php" class="button">Run Again</a>
            <a href="index.php" class="button blue">Return to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html> 