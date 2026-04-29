<?php
/**
 * Standalone email test script 
 * This doesn't rely on any other includes
 */

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the base path
$base_path = __DIR__;
echo "Starting direct email test...\n\n";

try {
    // Database connection
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
    
    echo "Database connection successful\n";
    
    // Include PHPMailer directly - don't use any autoloaders
    require_once($base_path . '/PHPMailer/class.phpmailer.php');
    require_once($base_path . '/PHPMailer/class.smtp.php');
    
    echo "PHPMailer classes loaded\n";
    
    // Get email config from database
    $query = "SELECT * FROM tbl_email_config WHERE e_id = 1";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("No email configuration found in the database.");
    }
    
    $config = $result->fetch_assoc();
    echo "Email configuration loaded\n";
    echo "SMTP Host: " . $config['mail_driver_host'] . "\n";
    echo "SMTP Port: " . $config['mail_port'] . "\n";
    echo "SMTP Username: " . $config['mail_username'] . "\n";
    
    // Create PHPMailer instance
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
    $mail->SMTPDebug = 2; // Enable verbose debug output
    echo "PHPMailer initialized with SMTP settings\n";
    
    // Test recipient
    $mail->addAddress($config['mail_username'], 'Test Recipient');
    $mail->Subject = 'Test Email from Opulent Laundry System';
    
    // Create message
    $message = "
    <html>
    <head>
        <title>Test Email</title>
    </head>
    <body>
        <h2>Test Email</h2>
        <p>This is a test email from the Opulent Laundry System.</p>
        <p>If you're receiving this, the email system is working correctly.</p>
        <p>Time sent: " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    $mail->isHTML(true);
    $mail->Body = $message;
    
    echo "Attempting to send test email...\n";
    
    // Send the email
    if ($mail->send()) {
        echo "Test email sent successfully!\n";
    } else {
        throw new Exception("Failed to send test email: " . $mail->ErrorInfo);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nEmail test completed.\n";
?> 