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
    'mpesa_mode' => 'sandbox',
    'method_send_money' => '{"enabled":"0","display_name":"Send Money","provider":"","account_name":"","phone":"","instructions":""}',
    'method_pochi' => '{"enabled":"0","display_name":"Pochi","provider":"","account_name":"","phone":"","instructions":""}',
    'method_till' => '{"enabled":"0","display_name":"Till","provider":"","account_name":"","till_number":"","instructions":""}',
    'method_paybill' => '{"enabled":"0","display_name":"Paybill","provider":"","account_name":"","paybill_number":"","account_number":"","instructions":""}',
    'method_cod' => '{"enabled":"0","display_name":"Cash on Delivery","instructions":""}'
];

foreach ($default_settings as $name => $value) {
    $sql = "INSERT IGNORE INTO payment_settings (setting_name, value) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $name, $value);
    $stmt->execute();
}

// Update payment configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'paypal') {
        $paypal_client_id = isset($_POST['paypal_client_id']) ? $_POST['paypal_client_id'] : '';
        $paypal_secret = isset($_POST['paypal_secret']) ? $_POST['paypal_secret'] : '';
        $paypal_mode = isset($_POST['paypal_mode']) ? $_POST['paypal_mode'] : 'sandbox';

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
        $_SESSION['success_message'] = "PayPal settings updated successfully!";
    } elseif ($action === 'stripe') {
        $stripe_publishable_key = isset($_POST['stripe_publishable_key']) ? $_POST['stripe_publishable_key'] : '';
        $stripe_secret_key = isset($_POST['stripe_secret_key']) ? $_POST['stripe_secret_key'] : '';
        $stripe_mode = isset($_POST['stripe_mode']) ? $_POST['stripe_mode'] : 'test';

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
        $_SESSION['success_message'] = "Stripe settings updated successfully!";
    } elseif ($action === 'mpesa') {
        $mpesa_consumer_key = isset($_POST['mpesa_consumer_key']) ? $_POST['mpesa_consumer_key'] : '';
        $mpesa_consumer_secret = isset($_POST['mpesa_consumer_secret']) ? $_POST['mpesa_consumer_secret'] : '';
        $mpesa_passkey = isset($_POST['mpesa_passkey']) ? $_POST['mpesa_passkey'] : '';
        $mpesa_shortcode = isset($_POST['mpesa_shortcode']) ? $_POST['mpesa_shortcode'] : '';
        $mpesa_mode = isset($_POST['mpesa_mode']) ? $_POST['mpesa_mode'] : 'sandbox';

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
        $_SESSION['success_message'] = "M-Pesa settings updated successfully!";
    } elseif ($action === 'method') {
        $method_key = isset($_POST['method_key']) ? $_POST['method_key'] : '';
        $enabled = isset($_POST['enabled']) ? $_POST['enabled'] : '0';
        $display_name = isset($_POST['display_name']) ? $_POST['display_name'] : '';
        $provider = isset($_POST['provider']) ? $_POST['provider'] : '';
        $account_name = isset($_POST['account_name']) ? $_POST['account_name'] : '';
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        $till_number = isset($_POST['till_number']) ? $_POST['till_number'] : '';
        $paybill_number = isset($_POST['paybill_number']) ? $_POST['paybill_number'] : '';
        $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : '';
        $instructions = isset($_POST['instructions']) ? $_POST['instructions'] : '';

        $payload = [
            'enabled' => (string)$enabled,
            'display_name' => $display_name,
            'provider' => $provider,
            'account_name' => $account_name,
            'phone' => $phone,
            'till_number' => $till_number,
            'paybill_number' => $paybill_number,
            'account_number' => $account_number,
            'instructions' => $instructions
        ];

        if ($method_key) {
            $value = json_encode($payload);
            $sql = "INSERT IGNORE INTO payment_settings (setting_name, value) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $method_key, $value);
            $stmt->execute();

            $sql = "UPDATE payment_settings SET value = ? WHERE setting_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $value, $method_key);
            $stmt->execute();
            $_SESSION['success_message'] = "Payment method updated successfully!";
        }
    }

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

$method_keys = [
    'method_send_money',
    'method_pochi',
    'method_till',
    'method_paybill',
    'method_cod'
];

$methods = [];
foreach ($method_keys as $k) {
    $raw = isset($settings[$k]) ? $settings[$k] : '';
    $decoded = [];
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }
    }
    $methods[$k] = $decoded;
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

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>PayPal</td>
                                        <td>
                                            <span class="badge badge-<?php echo !empty($settings['paypal_client_id']) ? 'success' : 'secondary'; ?>">
                                                <?php echo !empty($settings['paypal_client_id']) ? 'Configured' : 'Not Configured'; ?>
                                            </span>
                                        </td>
                                        <td>Mode: <?php echo htmlspecialchars($settings['paypal_mode'] ?? 'sandbox'); ?></td>
                                        <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#paypalModal">Edit</button></td>
                                    </tr>
                                    <tr>
                                        <td>Stripe</td>
                                        <td>
                                            <span class="badge badge-<?php echo !empty($settings['stripe_publishable_key']) ? 'success' : 'secondary'; ?>">
                                                <?php echo !empty($settings['stripe_publishable_key']) ? 'Configured' : 'Not Configured'; ?>
                                            </span>
                                        </td>
                                        <td>Mode: <?php echo htmlspecialchars($settings['stripe_mode'] ?? 'test'); ?></td>
                                        <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#stripeModal">Edit</button></td>
                                    </tr>
                                    <tr>
                                        <td>M-Pesa STK Push</td>
                                        <td>
                                            <span class="badge badge-<?php echo !empty($settings['mpesa_consumer_key']) ? 'success' : 'secondary'; ?>">
                                                <?php echo !empty($settings['mpesa_consumer_key']) ? 'Configured' : 'Not Configured'; ?>
                                            </span>
                                        </td>
                                        <td>Shortcode: <?php echo htmlspecialchars($settings['mpesa_shortcode'] ?? ''); ?> | Mode: <?php echo htmlspecialchars($settings['mpesa_mode'] ?? 'sandbox'); ?></td>
                                        <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mpesaModal">Edit</button></td>
                                    </tr>

                                    <?php
                                    $method_labels = [
                                        'method_send_money' => 'Send Money',
                                        'method_pochi' => 'Pochi',
                                        'method_till' => 'Till',
                                        'method_paybill' => 'Paybill',
                                        'method_cod' => 'Cash on Delivery'
                                    ];

                                    foreach ($method_labels as $key => $label) {
                                        $m = isset($methods[$key]) && is_array($methods[$key]) ? $methods[$key] : [];
                                        $enabled = isset($m['enabled']) ? $m['enabled'] : '0';
                                        $details = '';
                                        if ($key === 'method_till') {
                                            $details = 'Till: ' . (isset($m['till_number']) ? $m['till_number'] : '');
                                        } elseif ($key === 'method_paybill') {
                                            $details = 'Paybill: ' . (isset($m['paybill_number']) ? $m['paybill_number'] : '') . ' | Acc: ' . (isset($m['account_number']) ? $m['account_number'] : '');
                                        } elseif ($key === 'method_cod') {
                                            $details = isset($m['instructions']) ? $m['instructions'] : '';
                                        } else {
                                            $details = 'Phone: ' . (isset($m['phone']) ? $m['phone'] : '');
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($label); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $enabled === '1' ? 'success' : 'secondary'; ?>">
                                                <?php echo $enabled === '1' ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($details); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#methodModal"
                                                    data-method-key="<?php echo htmlspecialchars($key); ?>"
                                                    data-display-name="<?php echo htmlspecialchars(isset($m['display_name']) ? $m['display_name'] : $label); ?>"
                                                    data-enabled="<?php echo htmlspecialchars($enabled); ?>"
                                                    data-provider="<?php echo htmlspecialchars(isset($m['provider']) ? $m['provider'] : ''); ?>"
                                                    data-account-name="<?php echo htmlspecialchars(isset($m['account_name']) ? $m['account_name'] : ''); ?>"
                                                    data-phone="<?php echo htmlspecialchars(isset($m['phone']) ? $m['phone'] : ''); ?>"
                                                    data-till-number="<?php echo htmlspecialchars(isset($m['till_number']) ? $m['till_number'] : ''); ?>"
                                                    data-paybill-number="<?php echo htmlspecialchars(isset($m['paybill_number']) ? $m['paybill_number'] : ''); ?>"
                                                    data-account-number="<?php echo htmlspecialchars(isset($m['account_number']) ? $m['account_number'] : ''); ?>"
                                                    data-instructions="<?php echo htmlspecialchars(isset($m['instructions']) ? $m['instructions'] : ''); ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
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

<style>
    .modal-dialog.modal-compact { max-width: 520px; }
    .modal-dialog.modal-compact-sm { max-width: 460px; }
    .modal-body { padding: 15px 20px; }
    .modal-header, .modal-footer { padding: 12px 20px; }
    .modal-body .form-group { margin-bottom: 10px; }
</style>

<div class="modal fade" id="paypalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-compact" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PayPal Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="paypal">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Client ID</label>
                        <input type="text" class="form-control" name="paypal_client_id" value="<?php echo htmlspecialchars($settings['paypal_client_id'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Secret Key</label>
                        <input type="password" class="form-control" name="paypal_secret" value="<?php echo htmlspecialchars($settings['paypal_secret'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Mode</label>
                        <select class="form-control" name="paypal_mode">
                            <option value="sandbox" <?php echo ($settings['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                            <option value="live" <?php echo ($settings['paypal_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stripeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-compact" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stripe Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="stripe">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Publishable Key</label>
                        <input type="text" class="form-control" name="stripe_publishable_key" value="<?php echo htmlspecialchars($settings['stripe_publishable_key'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Secret Key</label>
                        <input type="password" class="form-control" name="stripe_secret_key" value="<?php echo htmlspecialchars($settings['stripe_secret_key'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Mode</label>
                        <select class="form-control" name="stripe_mode">
                            <option value="test" <?php echo ($settings['stripe_mode'] ?? '') === 'test' ? 'selected' : ''; ?>>Test</option>
                            <option value="live" <?php echo ($settings['stripe_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="mpesaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-compact" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">M-Pesa STK Push Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="mpesa">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Consumer Key</label>
                        <input type="text" class="form-control" name="mpesa_consumer_key" value="<?php echo htmlspecialchars($settings['mpesa_consumer_key'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Consumer Secret</label>
                        <input type="password" class="form-control" name="mpesa_consumer_secret" value="<?php echo htmlspecialchars($settings['mpesa_consumer_secret'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Passkey</label>
                        <input type="password" class="form-control" name="mpesa_passkey" value="<?php echo htmlspecialchars($settings['mpesa_passkey'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Shortcode</label>
                        <input type="text" class="form-control" name="mpesa_shortcode" value="<?php echo htmlspecialchars($settings['mpesa_shortcode'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Mode</label>
                        <select class="form-control" name="mpesa_mode">
                            <option value="sandbox" <?php echo ($settings['mpesa_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                            <option value="live" <?php echo ($settings['mpesa_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="methodModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-compact-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment Method</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="method">
                <input type="hidden" name="method_key" id="method_key" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" class="form-control" name="display_name" id="display_name" value="">
                    </div>
                    <div class="form-group">
                        <label>Enabled</label>
                        <select class="form-control" name="enabled" id="enabled">
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Provider</label>
                        <input type="text" class="form-control" name="provider" id="provider" value="">
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" class="form-control" name="account_name" id="account_name" value="">
                    </div>
                    <div class="form-group" id="phone_group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone" value="">
                    </div>
                    <div class="form-group" id="till_group">
                        <label>Till Number</label>
                        <input type="text" class="form-control" name="till_number" id="till_number" value="">
                    </div>
                    <div class="form-group" id="paybill_group">
                        <label>Paybill Number</label>
                        <input type="text" class="form-control" name="paybill_number" id="paybill_number" value="">
                    </div>
                    <div class="form-group" id="account_number_group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number" id="account_number" value="">
                    </div>
                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea class="form-control" name="instructions" id="instructions" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#methodModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var methodKey = button.data('method-key') || '';

        var displayName = button.data('display-name') || '';
        var enabled = button.data('enabled');
        enabled = (enabled === 1 || enabled === '1') ? '1' : '0';

        var provider = button.data('provider') || '';
        var accountName = button.data('account-name') || '';
        var phone = button.data('phone') || '';
        var tillNumber = button.data('till-number') || '';
        var paybillNumber = button.data('paybill-number') || '';
        var accountNumber = button.data('account-number') || '';
        var instructions = button.data('instructions') || '';

        $('#method_key').val(methodKey);
        $('#display_name').val(displayName);
        $('#enabled').val(enabled);
        $('#provider').val(provider);
        $('#account_name').val(accountName);
        $('#phone').val(phone);
        $('#till_number').val(tillNumber);
        $('#paybill_number').val(paybillNumber);
        $('#account_number').val(accountNumber);
        $('#instructions').val(instructions);

        var isTill = methodKey === 'method_till';
        var isPaybill = methodKey === 'method_paybill';
        var isCod = methodKey === 'method_cod';

        $('#phone_group').toggle(!isTill && !isPaybill && !isCod);
        $('#till_group').toggle(isTill);
        $('#paybill_group').toggle(isPaybill);
        $('#account_number_group').toggle(isPaybill);

        var defaultInstructions = {
            method_send_money: 'M-Pesa > Send Money > Enter phone number and amount > Confirm with PIN.',
            method_pochi: 'M-Pesa > Pochi La Biashara > Enter Pochi number and amount > Confirm with PIN.',
            method_till: 'M-Pesa > Lipa na M-Pesa > Buy Goods > Enter Till Number and amount > Confirm with PIN.',
            method_paybill: 'M-Pesa > Lipa na M-Pesa > Paybill > Enter Business No. and Account No. > Enter amount > Confirm with PIN.',
            method_cod: 'Customer pays cash upon delivery/pickup. Record payment after receiving cash.'
        };

        if (!instructions && defaultInstructions[methodKey]) {
            instructions = defaultInstructions[methodKey];
        }
        $('#instructions').val(instructions);
    });
</script>