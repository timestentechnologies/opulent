<?php
// Start session and set error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

// Database connection
$servername = "localhost";
$username = "root"; // Change this to match your production credentials
$password = ""; // Change this to match your production credentials
$dbname = "opulentl_laundry";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process form submission
if (isset($_POST["btn_web"])) {
    // Extract form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $short_title = mysqli_real_escape_string($conn, $_POST['short_title']);
    $footer = mysqli_real_escape_string($conn, $_POST['footer']);
    $currency_code = mysqli_real_escape_string($conn, $_POST['currency_code']);
    $currency_symbol = mysqli_real_escape_string($conn, $_POST['currency_symbol']);
    
    // Get existing image values
    $website_logo = $_POST['old_website_image'];
    $login_logo = $_POST['old_login_image'];
    $invoice_logo = $_POST['old_invoice_image'];
    $background_login_image = $_POST['old_back_login_image'];
    
    // Update database
    $q1 = "UPDATE `manage_website` SET 
        `title` = '$title',
        `short_title` = '$short_title',
        `footer` = '$footer',
        `currency_code` = '$currency_code',
        `currency_symbol` = '$currency_symbol',
        `logo` = '$website_logo',
        `login_logo` = '$login_logo',
        `invoice_logo` = '$invoice_logo',
        `background_login_image` = '$background_login_image'";
        
    if ($conn->query($q1) === TRUE) {
        echo "<p style='color:green;'>Settings updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error updating settings: " . $conn->error . "</p>";
    }
}

// Fetch current settings
$query = $conn->query("SELECT * FROM manage_website LIMIT 1");

// Initialize variables
$title = '';
$short_title = '';
$footer = '';
$currency_code = '';
$currency_symbol = '';
$website_logo = '';
$login_logo = '';
$invoice_logo = '';
$background_login_image = '';

// Populate variables if data exists
if ($row = mysqli_fetch_array($query)) {
    $title = htmlspecialchars($row['title'] ?? '');
    $short_title = htmlspecialchars($row['short_title'] ?? '');
    $footer = htmlspecialchars($row['footer'] ?? '');
    $currency_code = htmlspecialchars($row['currency_code'] ?? '');
    $currency_symbol = htmlspecialchars($row['currency_symbol'] ?? '');
    $website_logo = htmlspecialchars($row['logo'] ?? '');
    $login_logo = htmlspecialchars($row['login_logo'] ?? '');
    $invoice_logo = htmlspecialchars($row['invoice_logo'] ?? '');
    $background_login_image = htmlspecialchars($row['background_login_image'] ?? '');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Website Management - Simple Version</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
        .back-link { margin-top: 20px; display: block; }
    </style>
</head>
<body>
    <h1>Website Management (Simple Version)</h1>
    <p><a href="index.php">Return to Dashboard</a></p>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo $title; ?>">
        </div>
        
        <div class="form-group">
            <label>Short Title:</label>
            <input type="text" name="short_title" value="<?php echo $short_title; ?>">
        </div>
        
        <div class="form-group">
            <label>Footer:</label>
            <textarea name="footer" rows="4"><?php echo $footer; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Currency Code:</label>
            <input type="text" name="currency_code" value="<?php echo $currency_code; ?>">
        </div>
        
        <div class="form-group">
            <label>Currency Symbol:</label>
            <input type="text" name="currency_symbol" value="<?php echo $currency_symbol; ?>">
        </div>
        
        <!-- Hidden fields for existing images -->
        <input type="hidden" name="old_website_image" value="<?php echo $website_logo; ?>">
        <input type="hidden" name="old_login_image" value="<?php echo $login_logo; ?>">
        <input type="hidden" name="old_invoice_image" value="<?php echo $invoice_logo; ?>">
        <input type="hidden" name="old_back_login_image" value="<?php echo $background_login_image; ?>">
        
        <button type="submit" name="btn_web">Update Settings</button>
    </form>
    
    <p class="back-link">Note: This is a simplified version of the website management page. Images cannot be changed in this version.</p>
    <p class="back-link">Try the <a href="manage_website.php">standard version</a> if this page works correctly.</p>
</body>
</html> 