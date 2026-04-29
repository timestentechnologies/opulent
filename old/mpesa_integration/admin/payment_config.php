<?php
/**
 * Payment Configuration
 * Admin interface for configuring payment settings including M-Pesa
 */

session_start();
include('connect.php');

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    header('Location: login.php');
    exit();
}

// Create payment_settings table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (setting_name)
)";

if (!$conn->query($create_table_sql)) {
    die("Error creating table: " . $conn->error);
}

// Insert default payment settings if they don't exist
$default_settings = [
    'mpesa_consumer_key' => '',
    'mpesa_consumer_secret' => '',
    'mpesa_passkey' => '',
    'mpesa_shortcode' => '',
    'mpesa_mode' => 'sandbox'
];

foreach ($default_settings as $name => $value) {
    $sql = "INSERT IGNORE INTO payment_settings (setting_name, value) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $name, $value);
    $stmt->execute();
}

// Update payment configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // M-Pesa Configuration
    $mpesa_consumer_key = $_POST['mpesa_consumer_key'];
    $mpesa_consumer_secret = $_POST['mpesa_consumer_secret'];
    $mpesa_passkey = $_POST['mpesa_passkey'];
    $mpesa_shortcode = $_POST['mpesa_shortcode'];
    $mpesa_mode = $_POST['mpesa_mode'];

    // Update M-Pesa settings
    $sql = "UPDATE payment_settings SET 
            value = CASE setting_name
                WHEN 'mpesa_consumer_key' THEN ?
                WHEN 'mpesa_consumer_secret' THEN ?
                WHEN 'mpesa_passkey' THEN ?
                WHEN 'mpesa_shortcode' THEN ?
                WHEN 'mpesa_mode' THEN ?
            END
            WHERE setting_name IN ('mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_passkey', 'mpesa_shortcode', 'mpesa_mode')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $mpesa_consumer_key, $mpesa_consumer_secret, $mpesa_passkey, $mpesa_shortcode, $mpesa_mode);
    $stmt->execute();

    $_SESSION['success_message'] = "Payment settings updated successfully!";
    header('Location: payment_config.php');
    exit();
}

// Fetch current settings
$sql = "SELECT setting_name, value FROM payment_settings";
$result = $conn->query($sql);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Configuration - Admin Panel</title>
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <?php include('head.php'); ?>
    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <h2>M-Pesa Configuration</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="config-section">
                <h3>M-Pesa Settings</h3>
                <div class="form-group">
                    <label for="mpesa_consumer_key">Consumer Key:</label>
                    <input type="text" id="mpesa_consumer_key" name="mpesa_consumer_key" 
                           value="<?php echo htmlspecialchars($settings['mpesa_consumer_key'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mpesa_consumer_secret">Consumer Secret:</label>
                    <input type="password" id="mpesa_consumer_secret" name="mpesa_consumer_secret" 
                           value="<?php echo htmlspecialchars($settings['mpesa_consumer_secret'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mpesa_passkey">Pass Key:</label>
                    <input type="password" id="mpesa_passkey" name="mpesa_passkey" 
                           value="<?php echo htmlspecialchars($settings['mpesa_passkey'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mpesa_shortcode">Business Short Code:</label>
                    <input type="text" id="mpesa_shortcode" name="mpesa_shortcode" 
                           value="<?php echo htmlspecialchars($settings['mpesa_shortcode'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mpesa_mode">Mode:</label>
                    <select id="mpesa_mode" name="mpesa_mode" required>
                        <option value="sandbox" <?php echo ($settings['mpesa_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                        <option value="live" <?php echo ($settings['mpesa_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update M-Pesa Settings</button>
            </div>
        </form>
    </div>

    <?php include('footer.php'); ?>
    <script src="js/script.js"></script>
</body>
</html> 