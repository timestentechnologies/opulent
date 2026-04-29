<?php
/**
 * Email Debug Tool - Shows detailed error messages
 */

// Require authentication
require_once('session_handler.php');

// Check if user is logged in
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Set unlimited execution time and memory
set_time_limit(0);
ini_set('memory_limit', '256M');

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once('connect.php');

// We'll manually include the PHPMailer files to bypass autoloader issues
require_once('PHPMailer/class.phpmailer.php');
require_once('PHPMailer/class.smtp.php');

$message = '';
$messageType = '';

// Function to get email config directly
function getDebugEmailConfig() {
    global $conn;
    
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

// Handle test email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['recipient_email'] ?? '';
    $debug_level = isset($_POST['debug_level']) ? intval($_POST['debug_level']) : 2;
    
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
        $messageType = "error";
    } else {
        try {
            // Get email configuration
            $config = getDebugEmailConfig();
            
            // Buffer output for debug info
            ob_start();
            
            // Create manual PHPMailer instance
            $mail = new PHPMailer(true); // Enable exceptions
            $mail->isSMTP();
            $mail->Host = $config['mail_driver_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail_username'];
            $mail->Password = $config['mail_password'];
            $mail->SMTPSecure = $config['mail_encrypt'];
            $mail->Port = $config['mail_port'];
            $mail->setFrom($config['mail_username'], $config['name']);
            $mail->SMTPDebug = $debug_level; // Debug level from form
            
            // Add recipient
            $mail->addAddress($recipient);
            
            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Debug Test Email from Opulent Laundry';
            $mail->Body = '<h2>This is a debug test email</h2><p>If you receive this, email is working correctly.</p>';
            $mail->AltBody = 'This is a debug test email. If you receive this, email is working correctly.';
            
            // Send email
            if ($mail->send()) {
                $message = "Email sent successfully to $recipient!";
                $messageType = "success";
            } else {
                throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
            }
            
            // Get debug output
            $debug_output = ob_get_clean();
        } catch (Exception $e) {
            $debug_output = ob_get_clean();
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get current email config
try {
    $emailConfig = getDebugEmailConfig();
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
    $emailConfig = array(
        'name' => 'Not found',
        'mail_driver_host' => 'Not found',
        'mail_port' => 'Not found',
        'mail_username' => 'Not found',
        'mail_password' => 'Not found',
        'mail_encrypt' => 'Not found'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Debug Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .debug-output {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            font-family: monospace;
            white-space: pre-wrap;
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Debug Tool</h1>
        
        <?php if (!empty($message)): ?>
            <div class="card">
                <h3 class="<?= $messageType ?>"><?= $message ?></h3>
                
                <?php if (!empty($debug_output)): ?>
                    <h3>SMTP Debug Output:</h3>
                    <div class="debug-output"><?= nl2br(htmlspecialchars($debug_output)) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Email Configuration</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Sender Name</td>
                    <td><?= htmlspecialchars($emailConfig['name']) ?></td>
                </tr>
                <tr>
                    <td>SMTP Host</td>
                    <td><?= htmlspecialchars($emailConfig['mail_driver_host']) ?></td>
                </tr>
                <tr>
                    <td>SMTP Port</td>
                    <td><?= htmlspecialchars($emailConfig['mail_port']) ?></td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><?= htmlspecialchars($emailConfig['mail_username']) ?></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><?= str_repeat('*', strlen($emailConfig['mail_password'])) ?></td>
                </tr>
                <tr>
                    <td>Encryption</td>
                    <td><?= htmlspecialchars($emailConfig['mail_encrypt']) ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Send Debug Test Email</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="recipient_email">Recipient Email:</label>
                    <input type="email" id="recipient_email" name="recipient_email" required>
                </div>
                
                <div class="form-group">
                    <label for="debug_level">Debug Level:</label>
                    <select id="debug_level" name="debug_level">
                        <option value="1">Level 1: Client messages only</option>
                        <option value="2" selected>Level 2: Client and server messages</option>
                        <option value="3">Level 3: Verbose client and server messages</option>
                        <option value="4">Level 4: Low-level data output</option>
                    </select>
                </div>
                
                <button type="submit">Send Debug Test Email</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Email Troubleshooting Tips</h2>
            <ol>
                <li>Make sure the SMTP host and port are correct (gmail: smtp.gmail.com, port 587)</li>
                <li>For Gmail accounts, you need to use an "App Password" rather than your regular password</li>
                <li>Check that TLS encryption is selected for Gmail</li>
                <li>Verify that your hosting provider allows outgoing SMTP connections</li>
                <li>Try temporarily disabling any firewall that might block SMTP</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>PHP Mail Function Test</h2>
            <p>If SMTP is not working, let's try PHP's built-in mail function:</p>
            
            <?php
            if (isset($_POST['test_mail_function'])) {
                $to = $_POST['mail_function_recipient'] ?? '';
                $subject = 'Test from PHP mail() function';
                $message = 'This is a test email sent using PHP built-in mail() function.';
                $headers = 'From: ' . $emailConfig['mail_username'] . "\r\n" .
                    'Reply-To: ' . $emailConfig['mail_username'] . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                
                if (mail($to, $subject, $message, $headers)) {
                    echo '<p class="success">Mail sent successfully using mail() function.</p>';
                } else {
                    echo '<p class="error">Failed to send mail using mail() function.</p>';
                }
            }
            ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="mail_function_recipient">Recipient Email:</label>
                    <input type="email" id="mail_function_recipient" name="mail_function_recipient" required>
                </div>
                <input type="hidden" name="test_mail_function" value="1">
                <button type="submit">Test PHP mail() function</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Server Information</h2>
            <table>
                <tr>
                    <td>PHP Version</td>
                    <td><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td>OpenSSL Version</td>
                    <td><?= OPENSSL_VERSION_TEXT ?? 'Not available' ?></td>
                </tr>
                <tr>
                    <td>Server Software</td>
                    <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                </tr>
                <tr>
                    <td>Server Name</td>
                    <td><?= $_SERVER['SERVER_NAME'] ?? 'Unknown' ?></td>
                </tr>
            </table>
        </div>
        
        <a href="email_test.php" class="back-link">← Back to Email Test Page</a>
    </div>
</body>
</html> 