<?php
require_once('session_handler.php');
require_once('connect.php');

if (!isset($_SESSION["id"]) || $_SESSION["username"] !== 'admin') {
    header('Location: index.php');
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Check actual database records
$invoice_count = 0;
$payment_count = 0;
$stmt_debit_count = 0;
$stmt_credit_count = 0;

// Count invoices
$inv_res = $conn->query("SELECT COUNT(*) as cnt FROM invoices");
if ($inv_res) {
    $row = $inv_res->fetch_assoc();
    $invoice_count = $row['cnt'];
}

// Count payments
$pay_res = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE payment_type = 'customer_payment'");
if ($pay_res) {
    $row = $pay_res->fetch_assoc();
    $payment_count = $row['cnt'];
}

// Count statement debits
$debit_res = $conn->query("SELECT COUNT(*) as cnt FROM customer_statements WHERE transaction_type = 'invoice'");
if ($debit_res) {
    $row = $debit_res->fetch_assoc();
    $stmt_debit_count = $row['cnt'];
}

// Count statement credits
$credit_res = $conn->query("SELECT COUNT(*) as cnt FROM customer_statements WHERE transaction_type = 'payment'");
if ($credit_res) {
    $row = $credit_res->fetch_assoc();
    $stmt_credit_count = $row['cnt'];
}

// Get sample records
$invoices = [];
$payments = [];
$statements = [];

$inv_list = $conn->query("SELECT i.*, c.fname, c.lname FROM invoices i LEFT JOIN customer c ON i.customer_id = c.id ORDER BY i.id DESC LIMIT 5");
if ($inv_list) {
    while ($r = $inv_list->fetch_assoc()) {
        $invoices[] = $r;
    }
}

$pay_list = $conn->query("SELECT p.*, c.fname, c.lname FROM payments p LEFT JOIN customer c ON p.customer_id = c.id WHERE p.payment_type = 'customer_payment' ORDER BY p.id DESC LIMIT 5");
if ($pay_list) {
    while ($r = $pay_list->fetch_assoc()) {
        $payments[] = $r;
    }
}

$stmt_list = $conn->query("SELECT cs.*, c.fname, c.lname FROM customer_statements cs LEFT JOIN customer c ON cs.customer_id = c.id ORDER BY cs.id DESC LIMIT 10");
if ($stmt_list) {
    while ($r = $stmt_list->fetch_assoc()) {
        $statements[] = $r;
    }
}

// Check orders that should have been processed
$order_res = $conn->query("SELECT COUNT(*) as cnt FROM `order` WHERE customer_id IS NOT NULL");
$order_count = 0;
if ($order_res) {
    $row = $order_res->fetch_assoc();
    $order_count = $row['cnt'];
}

$paid_orders_res = $conn->query("SELECT COUNT(*) as cnt FROM `order` WHERE payment_status = 'paid' AND customer_id IS NOT NULL");
$paid_order_count = 0;
if ($paid_orders_res) {
    $row = $paid_orders_res->fetch_assoc();
    $paid_order_count = $row['cnt'];
}
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Backfill Debug</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Backfill Debug</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4>Database Record Counts</h4>
                        <div class="row">
                            <div class="col-md-3"><strong>Orders with customers:</strong> <?php echo $order_count; ?></div>
                            <div class="col-md-3"><strong>Paid orders:</strong> <?php echo $paid_order_count; ?></div>
                            <div class="col-md-3"><strong>Invoices:</strong> <?php echo $invoice_count; ?></div>
                            <div class="col-md-3"><strong>Customer Payments:</strong> <?php echo $payment_count; ?></div>
                            <div class="col-md-3"><strong>Statement Debits:</strong> <?php echo $stmt_debit_count; ?></div>
                            <div class="col-md-3"><strong>Statement Credits:</strong> <?php echo $stmt_credit_count; ?></div>
                        </div>
                        
                        <hr>
                        
                        <h5>Expected vs Actual</h5>
                        <ul>
                            <li>Expected invoices: <?php echo $order_count; ?> | Actual: <?php echo $invoice_count; ?></li>
                            <li>Expected payments: <?php echo $paid_order_count; ?> | Actual: <?php echo $payment_count; ?></li>
                        </ul>
                        
                        <?php if ($invoice_count < $order_count || $payment_count < $paid_order_count): ?>
                            <div class="alert alert-warning">
                                Some records are missing. The backfill may not have completed successfully.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                All expected records appear to be created.
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5>Sample Invoices (last 5)</h5>
                        <?php if (empty($invoices)): ?>
                            <p>No invoices found.</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <tr><th>ID</th><th>Invoice #</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?php echo $inv['id']; ?></td>
                                    <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['fname'] . ' ' . $inv['lname']); ?></td>
                                    <td><?php echo number_format($inv['total_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($inv['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                        
                        <h5>Sample Payments (last 5)</h5>
                        <?php if (empty($payments)): ?>
                            <p>No customer payments found.</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <tr><th>ID</th><th>Customer</th><th>Amount</th><th>Method</th><th>Date</th></tr>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><?php echo $pay['id']; ?></td>
                                    <td><?php echo htmlspecialchars($pay['fname'] . ' ' . $pay['lname']); ?></td>
                                    <td><?php echo number_format($pay['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($pay['payment_method']); ?></td>
                                    <td><?php echo htmlspecialchars($pay['payment_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                        
                        <h5>Sample Statements (last 10)</h5>
                        <?php if (empty($statements)): ?>
                            <p>No statements found.</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <tr><th>ID</th><th>Customer</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr>
                                <?php foreach ($statements as $stmt): ?>
                                <tr>
                                    <td><?php echo $stmt['id']; ?></td>
                                    <td><?php echo htmlspecialchars($stmt['fname'] . ' ' . $stmt['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($stmt['transaction_type']); ?></td>
                                    <td><?php echo number_format($stmt['debit_amount'], 2); ?></td>
                                    <td><?php echo number_format($stmt['credit_amount'], 2); ?></td>
                                    <td><?php echo number_format($stmt['running_balance'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                        
                        <hr>
                        <a href="financial_backfill.php" class="btn btn-primary">Run Backfill Again</a>
                        <a href="invoices.php" class="btn btn-secondary">View Invoices</a>
                        <a href="payments.php" class="btn btn-secondary">View Payments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
