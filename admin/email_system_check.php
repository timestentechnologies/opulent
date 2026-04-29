<?php
/**
 * Email System Check
 * 
 * This script checks if all requirements are met for the automated email notification system
 */

// Start the session first
session_start();

// Basic access control
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    // Allow local access regardless of login
    $allowedIPs = array('127.0.0.1', '::1', $_SERVER['SERVER_ADDR']);
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        die("Authentication required");
    }
}

// Will store all check results
$checkResults = [];
$allChecksPassed = true;

// Function to add a check result
function addResult($name, $status, $message = '') {
    global $checkResults, $allChecksPassed;
    $checkResults[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message
    ];
    
    if (!$status) {
        $allChecksPassed = false;
    }
}

// Check PHP version
$phpVersionOk = version_compare(PHP_VERSION, '5.6.0', '>=');
addResult(
    'PHP Version', 
    $phpVersionOk, 
    'Current: ' . PHP_VERSION . ' (Minimum required: 5.6.0)'
);

// Check database connection
try {
    require_once('connect.php');
    $dbConnectionOk = ($conn instanceof mysqli) && !$conn->connect_error;
    addResult(
        'Database Connection', 
        $dbConnectionOk, 
        $dbConnectionOk ? 'Connected to MySQL server' : 'Failed to connect: ' . $conn->connect_error
    );
    
    // Check if tables exist
    $requiredTables = ['customer', 'order', 'service', 'tbl_email_config'];
    foreach ($requiredTables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($query);
        $tableExists = ($result && $result->num_rows > 0);
        
        addResult(
            "Table: $table", 
            $tableExists, 
            $tableExists ? 'Exists' : 'Missing'
        );
    }
    
    // Check if service table is properly formatted
    $serviceCheckQuery = "SHOW COLUMNS FROM `service` LIKE 'sname'";
    $serviceCheckResult = $conn->query($serviceCheckQuery);
    $snameExists = ($serviceCheckResult && $serviceCheckResult->num_rows > 0);
    
    addResult(
        "Service table structure", 
        $snameExists, 
        $snameExists ? 'Column "sname" exists' : 'Column "sname" missing'
    );
    
    // Check if order table has email_sent column
    $orderCheckQuery = "SHOW COLUMNS FROM `order` LIKE 'email_sent'";
    $orderCheckResult = $conn->query($orderCheckQuery);
    $emailSentExists = ($orderCheckResult && $orderCheckResult->num_rows > 0);
    
    if (!$emailSentExists) {
        // Try to add the column
        $alterQuery = "ALTER TABLE `order` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0";
        $alterResult = $conn->query($alterQuery);
        
        addResult(
            "Order table email_sent column", 
            $alterResult, 
            $alterResult ? 'Added successfully' : 'Failed to add: ' . $conn->error
        );
    } else {
        addResult(
            "Order table email_sent column", 
            true, 
            'Column exists'
        );
    }
    
    // Check email configuration
    $emailConfigQuery = "SELECT * FROM tbl_email_config WHERE e_id = 1";
    $emailConfigResult = $conn->query($emailConfigQuery);
    $emailConfigExists = ($emailConfigResult && $emailConfigResult->num_rows > 0);
    
    if ($emailConfigExists) {
        $emailConfig = $emailConfigResult->fetch_assoc();
        $configComplete = 
            !empty($emailConfig['mail_driver_host']) && 
            !empty($emailConfig['mail_username']) && 
            !empty($emailConfig['mail_password']) && 
            !empty($emailConfig['mail_port']);
        
        addResult(
            "Email Configuration", 
            $configComplete, 
            $configComplete ? 'Complete' : 'Incomplete - missing required fields'
        );
        
        if ($configComplete) {
            addResult(
                "Email Configuration Details", 
                true, 
                "Host: {$emailConfig['mail_driver_host']}, Port: {$emailConfig['mail_port']}, Encryption: {$emailConfig['mail_encrypt']}"
            );
        }
    } else {
        addResult(
            "Email Configuration", 
            false, 
            'No configuration found in database'
        );
    }
    
} catch (Exception $e) {
    addResult(
        'Database Operations', 
        false, 
        'Error: ' . $e->getMessage()
    );
}

// Check PHPMailer files
$requiredFiles = [
    'PHPMailer/PHPMailerAutoload.php',
    'PHPMailer/class.phpmailer.php',
    'PHPMailer/class.smtp.php'
];

foreach ($requiredFiles as $file) {
    $fileExists = file_exists($file);
    addResult(
        "Required file: $file", 
        $fileExists, 
        $fileExists ? 'Exists' : 'Missing'
    );
}

// Check if email helper functions exist
try {
    require_once('includes/email_helper.php');
    
    $helperFunctionsExist = 
        function_exists('getEmailConfig') && 
        function_exists('initMailer') && 
        function_exists('sendOrderNotification');
    
    addResult(
        'Email Helper Functions', 
        $helperFunctionsExist, 
        $helperFunctionsExist ? 'All required functions found' : 'Some functions are missing'
    );
    
} catch (Exception $e) {
    addResult(
        'Email Helper Include', 
        false, 
        'Error: ' . $e->getMessage()
    );
}

// Test SMTP connection if all basic checks passed
if ($allChecksPassed && isset($emailConfig) && $configComplete) {
    try {
        require_once('PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // No output
        $mail->Host = $emailConfig['mail_driver_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $emailConfig['mail_username'];
        $mail->Password = $emailConfig['mail_password'];
        $mail->SMTPSecure = $emailConfig['mail_encrypt'];
        $mail->Port = $emailConfig['mail_port'];
        
        $smtpConnected = $mail->smtpConnect();
        
        addResult(
            'SMTP Connection Test', 
            $smtpConnected, 
            $smtpConnected ? 'Connected successfully' : 'Failed to connect'
        );
        
        if ($smtpConnected) {
            $mail->smtpClose();
        }
    } catch (Exception $e) {
        addResult(
            'SMTP Connection Test', 
            false, 
            'Error: ' . $e->getMessage()
        );
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email System Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .summary {
            background-color: <?php echo $allChecksPassed ? '#d4edda' : '#f8d7da'; ?>;
            color: <?php echo $allChecksPassed ? '#155724' : '#721c24'; ?>;
            border: 1px solid <?php echo $allChecksPassed ? '#c3e6cb' : '#f5c6cb'; ?>;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
        }
        .actions {
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .button.blue {
            background-color: #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email System Check</h1>
        
        <div class="summary">
            <strong>System Status:</strong> <?php echo $allChecksPassed ? 'All checks passed! The email notification system should work correctly.' : 'Some checks failed. Please fix the issues below.'; ?>
        </div>
        
        <h2>Check Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkResults as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['name']); ?></td>
                    <td class="<?php echo $result['status'] ? 'success' : 'error'; ?>">
                        <?php echo $result['status'] ? 'PASS' : 'FAIL'; ?>
                    </td>
                    <td><?php echo htmlspecialchars($result['message']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="actions">
            <a href="simplified_order_notification.php" class="button">Test Order Notification</a>
            <a href="email_config.php" class="button blue">Email Configuration</a>
            <a href="index.php" class="button blue">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 