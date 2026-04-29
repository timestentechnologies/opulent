<?php
/**
 * Email Notification Dashboard
 * 
 * Dashboard to monitor email notification status and manually process pending emails
 */

// Start the session first
session_start();

// Basic access control
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    // Allow local access regardless of login
    $allowedIPs = array('127.0.0.1', '::1', $_SERVER['SERVER_ADDR'], '192.168.0.1', '192.168.1.1');
    
    // Allow any local network IP (very permissive)
    $clientIP = $_SERVER['REMOTE_ADDR'];
    $isLocalIP = (strpos($clientIP, '192.168.') === 0) || 
                 (strpos($clientIP, '10.') === 0) || 
                 (strpos($clientIP, '172.') === 0);
                 
    if (!in_array($clientIP, $allowedIPs) && !$isLocalIP) {
        // Redirect to login page instead of dying
        header("Location: login.php");
        exit;
    }
}

// Include required files
require_once('connect.php');

// Process action if any
$message = '';
$messageType = '';

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'process_now') {
        // Process pending emails now via AJAX call
        $message = "Processing started. See results below.";
        $messageType = "info";
    }
}

// Get email statistics
$stats = [];

try {
    // Total orders
    $totalOrdersQuery = "SELECT COUNT(*) as total FROM `order`";
    $totalResult = $conn->query($totalOrdersQuery);
    $stats['total_orders'] = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
    
    // Pending email notifications
    $pendingQuery = "SELECT COUNT(*) as pending FROM `order` WHERE (email_sent = 0 OR email_sent IS NULL)";
    $pendingResult = $conn->query($pendingQuery);
    $stats['pending_emails'] = $pendingResult ? $pendingResult->fetch_assoc()['pending'] : 0;
    
    // Sent email notifications
    $sentQuery = "SELECT COUNT(*) as sent FROM `order` WHERE email_sent = 1";
    $sentResult = $conn->query($sentQuery);
    $stats['sent_emails'] = $sentResult ? $sentResult->fetch_assoc()['sent'] : 0;
    
    // Recent orders with email status
    $recentOrdersQuery = "SELECT o.id, o.tracking_number, o.created_at, o.status, 
                            o.email_sent, c.fname, c.lname, c.email 
                         FROM `order` o 
                         JOIN customer c ON o.customer_id = c.id 
                         ORDER BY o.id DESC LIMIT 10";
    
    $recentResult = $conn->query($recentOrdersQuery);
    $recentOrders = [];
    
    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $recentOrders[] = $row;
        }
    }
    
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notification Dashboard</title>
    <?php include('head.php'); ?>
    <style>
        .stats-card {
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }
        .bg-primary { background-color: #2196F3; }
        .bg-success { background-color: #4CAF50; }
        .bg-warning { background-color: #FF9800; }
        .stats-number {
            font-size: 2em;
            font-weight: bold;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info {
            background-color: #e1f5fe;
            color: #0288d1;
            border: 1px solid #b3e5fc;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        #process-progress {
            display: none;
            margin: 20px 0;
        }
        #log-output {
            height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            font-family: monospace;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .controls {
            margin: 20px 0;
            display: flex;
            align-items: center;
        }
        .controls label {
            margin-left: 10px;
            margin-right: 20px;
        }
        .log-message { margin: 5px 0; }
        .log-success { color: green; }
        .log-error { color: red; }
    </style>
</head>
<body class="fix-header fix-sidebar">
    <?php include('header.php'); ?>
    <?php include('sidebar.php'); ?>
    
    <!-- Page wrapper  -->
    <div class="page-wrapper">
        <!-- Bread crumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Email Notification Dashboard</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Email Dashboard</li>
                </ol>
            </div>
        </div>
        <!-- End Bread crumb -->
        
        <!-- Container fluid  -->
        <div class="container-fluid">
            <!-- Start Page Content -->
            
            <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="stats-card bg-primary">
                        <div class="stats-number"><?php echo $stats['total_orders']; ?></div>
                        <div>Total Orders</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card bg-success">
                        <div class="stats-number"><?php echo $stats['sent_emails']; ?></div>
                        <div>Emails Sent</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card bg-warning">
                        <div class="stats-number"><?php echo $stats['pending_emails']; ?></div>
                        <div>Pending Emails</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Email Notification Controls</h4>
                    
                    <div class="controls">
                        <button id="process-now" class="btn btn-primary">Process Pending Emails Now</button>
                        <input type="checkbox" id="auto-refresh" checked>
                        <label for="auto-refresh">Auto-refresh dashboard every 30 seconds</label>
                        <span id="next-refresh"></span>
                    </div>
                    
                    <div id="process-progress" class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                    
                    <div id="log-output"></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Orders</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tracking #</th>
                                    <th>Customer</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th>Email Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders-table">
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo $order['tracking_number']; ?></td>
                                    <td><?php echo $order['fname'] . ' ' . $order['lname'] . '<br><small>' . $order['email'] . '</small>'; ?></td>
                                    <td><?php echo $order['created_at']; ?></td>
                                    <td><?php echo ucfirst($order['status']); ?></td>
                                    <td>
                                        <?php if ($order['email_sent'] == 1): ?>
                                            <span class="badge badge-success">Sent</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($order['email_sent'] != 1): ?>
                                            <button class="btn btn-sm btn-info send-single-email" data-order-id="<?php echo $order['id']; ?>">Send Email</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary resend-email" data-order-id="<?php echo $order['id']; ?>">Resend</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Email System Integration</h4>
                    <p>To ensure automatic email sending for all new orders, include the following code at the top of your front-end pages:</p>
                    <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">&lt;?php include('auto_check_emails.php'); ?&gt;</pre>
                    
                    <p>Add this code to:</p>
                    <ul>
                        <li>index.php - Main website page</li>
                        <li>customer_dashboard.php - After customer login</li>
                        <li>pages/save_order.php - After order submission</li>
                    </ul>
                </div>
            </div>
            
            <!-- End Page Content -->
        </div>
        <!-- End Container fluid -->
        
        <?php include('footer.php'); ?>
    </div>
    <!-- End Page wrapper -->
    
    <script>
        // Auto-refresh functionality
        let autoRefreshCheckbox = document.getElementById('auto-refresh');
        let autoRefreshTimer = null;
        let nextRefreshSpan = document.getElementById('next-refresh');
        let secondsUntilRefresh = 30;
        
        function updateRefreshCounter() {
            if (autoRefreshCheckbox.checked) {
                nextRefreshSpan.innerText = `Next refresh in ${secondsUntilRefresh} seconds`;
                secondsUntilRefresh--;
                
                if (secondsUntilRefresh < 0) {
                    secondsUntilRefresh = 30;
                    location.reload();
                }
            } else {
                nextRefreshSpan.innerText = '';
            }
        }
        
        function toggleAutoRefresh() {
            if (autoRefreshCheckbox.checked) {
                secondsUntilRefresh = 30;
                autoRefreshTimer = setInterval(updateRefreshCounter, 1000);
                updateRefreshCounter();
            } else {
                if (autoRefreshTimer) {
                    clearInterval(autoRefreshTimer);
                }
                nextRefreshSpan.innerText = '';
            }
        }
        
        autoRefreshCheckbox.addEventListener('change', toggleAutoRefresh);
        
        // Process pending emails
        document.getElementById('process-now').addEventListener('click', function() {
            const logOutput = document.getElementById('log-output');
            const progressBar = document.getElementById('process-progress');
            
            logOutput.innerHTML = '<div class="log-message">Starting email processing...</div>';
            progressBar.style.display = 'block';
            
            // Make AJAX request to auto_email_sender.php
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'auto_email_sender.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    progressBar.style.display = 'none';
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            logOutput.innerHTML += `<div class="log-message">Processing completed. Sent ${response.sent_emails} emails.</div>`;
                            
                            // Add log messages
                            response.messages.forEach(msg => {
                                logOutput.innerHTML += `<div class="log-message">${msg}</div>`;
                            });
                            
                            // Add error messages
                            response.errors.forEach(err => {
                                logOutput.innerHTML += `<div class="log-message log-error">${err}</div>`;
                            });
                            
                            // Scroll to bottom of log
                            logOutput.scrollTop = logOutput.scrollHeight;
                            
                            // Reload after 3 seconds to refresh the stats
                            setTimeout(() => location.reload(), 3000);
                        } catch (e) {
                            logOutput.innerHTML += `<div class="log-message log-error">Error parsing response: ${e.message}</div>`;
                        }
                    } else {
                        logOutput.innerHTML += `<div class="log-message log-error">Error: ${xhr.status} ${xhr.statusText}</div>`;
                    }
                }
            };
            
            xhr.send();
        });
        
        // Handle single email sending
        document.querySelectorAll('.send-single-email, .resend-email').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const logOutput = document.getElementById('log-output');
                
                logOutput.innerHTML = `<div class="log-message">Processing email for order #${orderId}...</div>`;
                
                // Create form data
                const formData = new FormData();
                formData.append('order_id', orderId);
                
                // Make AJAX request
                fetch('simplified_order_notification.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    logOutput.innerHTML += `<div class="log-message log-success">Email for order #${orderId} processed. Reloading page...</div>`;
                    
                    // Reload after 2 seconds
                    setTimeout(() => location.reload(), 2000);
                })
                .catch(error => {
                    logOutput.innerHTML += `<div class="log-message log-error">Error: ${error.message}</div>`;
                });
            });
        });
        
        // Start auto-refresh if checked
        toggleAutoRefresh();
    </script>
</body>
</html> 