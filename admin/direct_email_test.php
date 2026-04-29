<?php
/**
 * Direct Email Test
 * 
 * A simple script to test sending email directly.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure we have database connection
require_once('connect.php');

// Include PHPMailer directly
require_once('PHPMailer/class.phpmailer.php');
require_once('PHPMailer/class.smtp.php');

try {
    // Get email configuration from database
    $query = "SELECT * FROM tbl_email_config WHERE e_id = 1";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Email configuration not found in database.");
    }
    
    $config = $result->fetch_assoc();
    
    // Create PHPMailer instance
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->Debugoutput = 'html';
    $mail->Host = $config['mail_driver_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['mail_username'];
    $mail->Password = $config['mail_password'];
    $mail->SMTPSecure = $config['mail_encrypt'];
    $mail->Port = $config['mail_port'];
    
    // Set sender and recipient
    $mail->setFrom($config['mail_username'], $config['name']);
    $mail->addAddress('mercyshii002@gmail.com'); // REPLACE WITH YOUR EMAIL
    
    // Set email content
    $mail->isHTML(true);
    $mail->Subject = 'Direct Test Email from Opulent Laundry';
    $mail->Body = '
    <html>
    <head>
        <title>Test Email</title>
    </head>
    <body>
        <h2>Direct Test Email</h2>
        <p>This is a test email sent directly using PHPMailer, bypassing all helper functions.</p>
        <p>If you receive this email, direct PHPMailer usage is working.</p>
        <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
    </body>
    </html>
    ';
    
    // Send email
    if ($mail->send()) {
        echo "<h2 style='color:green;'>Email sent successfully!</h2>";
    } else {
        echo "<h2 style='color:red;'>Email could not be sent.</h2>";
        echo "<p>Mailer Error: " . $mail->ErrorInfo . "</p>";
    }
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error: " . $e->getMessage() . "</h2>";
}
?> 