<?php
require_once('session_handler.php');
?>
<?php

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
    'paypal_client_id' => '',
    'paypal_secret' => '',
    'paypal_mode' => 'sandbox',
    'stripe_publishable_key' => '',
    'stripe_secret_key' => '',
    'stripe_mode' => 'test',
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
    // PayPal Configuration
    $paypal_client_id = $_POST['paypal_client_id'];
    $paypal_secret = $_POST['paypal_secret'];
    $paypal_mode = $_POST['paypal_mode'];

    // Stripe Configuration
    $stripe_publishable_key = $_POST['stripe_publishable_key'];
    $stripe_secret_key = $_POST['stripe_secret_key'];
    $stripe_mode = $_POST['stripe_mode'];

    // M-Pesa Configuration
    $mpesa_consumer_key = $_POST['mpesa_consumer_key'];
    $mpesa_consumer_secret = $_POST['mpesa_consumer_secret'];
    $mpesa_passkey = $_POST['mpesa_passkey'];
    $mpesa_shortcode = $_POST['mpesa_shortcode'];
    $mpesa_mode = $_POST['mpesa_mode'];

    // Update PayPal settings
    $sql = "UPDATE payment_settings SET 
            value = CASE setting_name
                WHEN 'paypal_client_id' THEN ?
                WHEN 'paypal_secret' THEN ?
                WHEN 'paypal_mode' THEN ?
            END
            WHERE setting_name IN ('paypal_client_id', 'paypal_secret', 'paypal_mode')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $paypal_client_id, $paypal_secret, $paypal_mode);
    $stmt->execute();

    // Update Stripe settings
    $sql = "UPDATE payment_settings SET 
            value = CASE setting_name
                WHEN 'stripe_publishable_key' THEN ?
                WHEN 'stripe_secret_key' THEN ?
                WHEN 'stripe_mode' THEN ?
            END
            WHERE setting_name IN ('stripe_publishable_key', 'stripe_secret_key', 'stripe_mode')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $stripe_publishable_key, $stripe_secret_key, $stripe_mode);
    $stmt->execute();

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

<?php include('head.php');?>
<?php include('header.php');?>
<?php include('sidebar.php');?>

<!-- Page wrapper  -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Payment Gateway Configuration</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Payment Configuration</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
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
                                <h4 class="card-title">PayPal Configuration</h4>
                                <div class="form-group">
                                    <label for="paypal_client_id">Client ID:</label>
                                    <input type="text" class="form-control" id="paypal_client_id" name="paypal_client_id" 
                                           value="<?php echo htmlspecialchars($settings['paypal_client_id'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="paypal_secret">Secret Key:</label>
                                    <input type="password" class="form-control" id="paypal_secret" name="paypal_secret" 
                                           value="<?php echo htmlspecialchars($settings['paypal_secret'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="paypal_mode">Mode:</label>
                                    <select class="form-control" id="paypal_mode" name="paypal_mode" required>
                                        <option value="sandbox" <?php echo ($settings['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                                        <option value="live" <?php echo ($settings['paypal_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                                    </select>
                                </div>
                            </div>

                            <div class="config-section mt-4">
                                <h4 class="card-title">Stripe Configuration</h4>
                                <div class="form-group">
                                    <label for="stripe_publishable_key">Publishable Key:</label>
                                    <input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" 
                                           value="<?php echo htmlspecialchars($settings['stripe_publishable_key'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="stripe_secret_key">Secret Key:</label>
                                    <input type="password" class="form-control" id="stripe_secret_key" name="stripe_secret_key" 
                                           value="<?php echo htmlspecialchars($settings['stripe_secret_key'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="stripe_mode">Mode:</label>
                                    <select class="form-control" id="stripe_mode" name="stripe_mode" required>
                                        <option value="test" <?php echo ($settings['stripe_mode'] ?? '') === 'test' ? 'selected' : ''; ?>>Test</option>
                                        <option value="live" <?php echo ($settings['stripe_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                                    </select>
                                </div>
                            </div>

                            <div class="config-section mt-4">
                                <h4 class="card-title">M-Pesa Configuration</h4>
                                <div class="form-group">
                                    <label for="mpesa_consumer_key">Consumer Key:</label>
                                    <input type="text" class="form-control" id="mpesa_consumer_key" name="mpesa_consumer_key" 
                                           value="<?php echo htmlspecialchars($settings['mpesa_consumer_key'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="mpesa_consumer_secret">Consumer Secret:</label>
                                    <input type="password" class="form-control" id="mpesa_consumer_secret" name="mpesa_consumer_secret" 
                                           value="<?php echo htmlspecialchars($settings['mpesa_consumer_secret'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="mpesa_passkey">Passkey:</label>
                                    <input type="password" class="form-control" id="mpesa_passkey" name="mpesa_passkey" 
                                           value="<?php echo htmlspecialchars($settings['mpesa_passkey'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="mpesa_shortcode">Shortcode:</label>
                                    <input type="text" class="form-control" id="mpesa_shortcode" name="mpesa_shortcode" 
                                           value="<?php echo htmlspecialchars($settings['mpesa_shortcode'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="mpesa_mode">Mode:</label>
                                    <select class="form-control" id="mpesa_mode" name="mpesa_mode" required>
                                        <option value="sandbox" <?php echo ($settings['mpesa_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                                        <option value="live" <?php echo ($settings['mpesa_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Content -->
    </div>
    <!-- End Container fluid -->
    <?php include('footer.php'); ?>
</div>
<!-- End Page wrapper -->
</div>
<!-- End Wrapper -->

<!-- All Jquery -->
<script src="js/lib/jquery/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="js/lib/bootstrap/js/popper.min.js"></script>
<script src="js/lib/bootstrap/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="js/jquery.slimscroll.js"></script>
<!--Menu sidebar -->
<script src="js/sidebarmenu.js"></script>
<!--stickey kit -->
<script src="js/lib/sticky-kit-master/dist/sticky-kit.min.js"></script>
<!--Custom JavaScript -->
<script src="js/custom.min.js"></script>
</body>
</html> 