<?php
require_once('session_handler.php');
?>
<?php 
require_once('connect.php');

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    ?>
    <script>
    window.location="login.php";
    </script>
    <?php
    exit();
}

// Handle payment allocation
if(isset($_POST['allocate_payment'])) {
    $customer_id = intval($_POST['customer_id']);
    $invoice_id = intval($_POST['invoice_id']);
    $amount = floatval($_POST['payment_amount']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $description = mysqli_real_escape_string($conn, $_POST['payment_description']);
    $recorded_by = $_SESSION["id"];
    
    // Record the payment
    $payment_sql = "INSERT INTO payments (payment_type, customer_id, invoice_id, amount, payment_method, 
                    description, payment_date, recorded_by) 
                    VALUES ('customer_payment', $customer_id, $invoice_id, $amount, 'allocated', 
                    '$description', '$payment_date', $recorded_by)";
    
    if($conn->query($payment_sql)) {
        $payment_id = $conn->insert_id;
        
        // Update invoice
        $update_invoice = "UPDATE invoices SET amount_paid = amount_paid + $amount, 
                          balance_due = balance_due - $amount,
                          status = CASE WHEN balance_due - $amount <= 0 THEN 'paid' ELSE 'partial' END
                          WHERE id = $invoice_id";
        $conn->query($update_invoice);
        
        // Add to customer statement
        $stmt_sql = "INSERT INTO customer_statements (customer_id, transaction_type, reference_id, reference_type, 
                     description, credit_amount, running_balance, transaction_date) 
                     SELECT $customer_id, 'payment', $payment_id, 'payment', '$description', $amount, 
                     COALESCE((SELECT running_balance FROM customer_statements WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1), 0) - $amount, 
                     '$payment_date'";
        $conn->query($stmt_sql);
        
        $_SESSION['success'] = "Payment allocated successfully!";
    } else {
        $_SESSION['error'] = "Error allocating payment: " . $conn->error;
    }
    header("Location: customer_statements.php?customer_id=$customer_id");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Get selected customer
$selected_customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Get all customers for dropdown
$customers_query = "SELECT id, fname, lname, email, contact FROM customer ORDER BY fname";
$customers_result = $conn->query($customers_query);

// If a customer is selected, get their statement
$customer_info = null;
$statement_data = [];
$invoices = [];

if($selected_customer) {
    // Get customer info
    $cust_query = "SELECT * FROM customer WHERE id = $selected_customer";
    $cust_result = $conn->query($cust_query);
    $customer_info = $cust_result->fetch_assoc();
    
    // Get customer statement
    $stmt_query = "SELECT cs.*, 
                   CASE 
                       WHEN cs.reference_type = 'invoice' THEN (SELECT invoice_number FROM invoices WHERE id = cs.reference_id)
                       WHEN cs.reference_type = 'payment' THEN CONCAT('PAY-', cs.reference_id)
                       ELSE cs.reference_id
                   END as ref_number
                   FROM customer_statements cs 
                   WHERE cs.customer_id = $selected_customer 
                   ORDER BY cs.transaction_date ASC, cs.id ASC";
    $stmt_result = $conn->query($stmt_query);
    
    // Calculate running balance properly
    $balance = 0;
    while($row = $stmt_result->fetch_assoc()) {
        $balance += $row['debit_amount'] - $row['credit_amount'];
        $row['calculated_balance'] = $balance;
        $statement_data[] = $row;
    }
    
    // Get unpaid invoices for allocation
    $inv_query = "SELECT * FROM invoices WHERE customer_id = $selected_customer AND balance_due > 0 ORDER BY due_date";
    $invoices_result = $conn->query($inv_query);
    while($inv = $invoices_result->fetch_assoc()) {
        $invoices[] = $inv;
    }
}

// Transaction type labels and badges
$type_labels = [
    'invoice' => 'Invoice',
    'payment' => 'Payment',
    'credit' => 'Credit Note',
    'debit' => 'Debit Note',
    'refund' => 'Refund'
];

$type_badges = [
    'invoice' => 'badge-unpaid',
    'payment' => 'badge-paid',
    'credit' => 'badge-received',
    'debit' => 'badge-cleaning',
    'refund' => 'badge-default'
];
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Customer Statements</h3> 
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Customer Statements</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid -->
    <div class="container-fluid">
        <!-- Customer Selection -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-user"></i> Select Customer</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select name="customer_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">-- Select Customer --</option>
                                        <?php $customers_result->data_seek(0); while($cust = $customers_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $cust['id']; ?>" <?php echo $selected_customer == $cust['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cust['fname'] . ' ' . $cust['lname'] . ' - ' . $cust['contact']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if($selected_customer && $customer_info) { ?>
        <!-- Customer Info Cards -->
        <div class="row m-t-20">
            <div class="col-md-4">
                <div class="table-card">
                    <div class="card-body text-center">
                        <h4><?php echo htmlspecialchars($customer_info['fname'] . ' ' . $customer_info['lname']); ?></h4>
                        <p class="text-muted"><i class="fa fa-envelope"></i> <?php echo $customer_info['email']; ?></p>
                        <p class="text-muted"><i class="fa fa-phone"></i> <?php echo $customer_info['contact']; ?></p>
                        <p class="text-muted"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($customer_info['address'] . ', ' . $customer_info['city']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-wallet"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format(end($statement_data)['calculated_balance'] ?? 0, 0); ?></h3>
                        <p>Current Balance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-success btn-block m-t-10" data-toggle="modal" data-target="#allocateModal">
                    <i class="fa fa-plus"></i> Allocate Payment
                </button>
                <a href="customer_statement_print.php?customer_id=<?php echo $selected_customer; ?>" target="_blank" class="btn btn-info btn-block m-t-10">
                    <i class="fa fa-print"></i> Print Statement
                </a>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-list"></i> Account Statement</h4>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $opening_balance = 0;
                                    if(count($statement_data) > 0) {
                                        $first_transaction = $statement_data[0];
                                        $opening_balance = $first_transaction['calculated_balance'] - $first_transaction['debit_amount'] + $first_transaction['credit_amount'];
                                    }
                                    ?>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="6" class="text-right">Opening Balance:</td>
                                        <td>Ksh<?php echo number_format($opening_balance, 2); ?></td>
                                    </tr>
                                    <?php foreach($statement_data as $row) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['transaction_date'])); ?></td>
                                        <td class="order-id"><?php echo $row['ref_number']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $type_badges[$row['transaction_type']]; ?>">
                                                <?php echo $type_labels[$row['transaction_type']] ?? ucfirst($row['transaction_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="text-danger">
                                            <?php echo $row['debit_amount'] > 0 ? 'Ksh' . number_format($row['debit_amount'], 2) : '-'; ?>
                                        </td>
                                        <td class="text-success">
                                            <?php echo $row['credit_amount'] > 0 ? 'Ksh' . number_format($row['credit_amount'], 2) : '-'; ?>
                                        </td>
                                        <td class="font-weight-bold <?php echo $row['calculated_balance'] >= 0 ? 'text-warning' : 'text-success'; ?>">
                                            Ksh<?php echo number_format(abs($row['calculated_balance']), 2); ?>
                                            <?php echo $row['calculated_balance'] > 0 ? ' DR' : ' CR'; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="6" class="text-right">Current Balance:</td>
                                        <td class="<?php echo (end($statement_data)['calculated_balance'] ?? 0) >= 0 ? 'text-warning' : 'text-success'; ?>">
                                            Ksh<?php echo number_format(abs(end($statement_data)['calculated_balance'] ?? 0), 2); ?>
                                            <?php echo (end($statement_data)['calculated_balance'] ?? 0) > 0 ? ' DR' : ' CR'; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Invoices -->
        <?php if(count($invoices) > 0) { ?>
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-alert"></i> Outstanding Invoices</h4>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($invoices as $inv) { 
                                        $is_overdue = strtotime($inv['due_date']) < strtotime('today');
                                    ?>
                                    <tr>
                                        <td class="order-id"><?php echo $inv['invoice_number']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($inv['issue_date'])); ?></td>
                                        <td <?php echo $is_overdue ? 'class="text-danger"' : ''; ?>>
                                            <?php echo date('M d, Y', strtotime($inv['due_date'])); ?>
                                            <?php if($is_overdue) echo ' <span class="badge badge-danger">Overdue</span>'; ?>
                                        </td>
                                        <td>Ksh<?php echo number_format($inv['total_amount'], 2); ?></td>
                                        <td>Ksh<?php echo number_format($inv['amount_paid'], 2); ?></td>
                                        <td class="font-weight-bold text-warning">Ksh<?php echo number_format($inv['balance_due'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $is_overdue ? 'badge-unpaid' : 'badge-received'; ?>">
                                                <?php echo $is_overdue ? 'Overdue' : 'Partial'; ?>
                                            </span>
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
        <?php } ?>

        <!-- Allocate Payment Modal -->
        <div class="modal fade" id="allocateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Allocate Payment</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="customer_id" value="<?php echo $selected_customer; ?>">
                            
                            <div class="form-group">
                                <label>Select Invoice *</label>
                                <select name="invoice_id" class="form-control" required>
                                    <option value="">-- Select Invoice --</option>
                                    <?php foreach($invoices as $inv) { ?>
                                        <option value="<?php echo $inv['id']; ?>">
                                            <?php echo $inv['invoice_number'] . ' - Balance: Ksh' . number_format($inv['balance_due'], 2); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Amount *</label>
                                <input type="number" step="0.01" name="payment_amount" class="form-control" required placeholder="0.00">
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="payment_description" class="form-control" rows="2" placeholder="Payment notes...">Payment allocation</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="allocate_payment" class="btn btn-primary">Allocate Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<style>
/* Dashboard Cards */
.dashboard-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
    min-height: 100px;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.dashboard-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
}

/* Brand Colors - Navy Blue and Warm Orange */
.card-navy::before { background: #1a365d; }
.card-orange::before { background: #ed8936; }

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-right: 20px;
}
.card-navy .card-icon { background: #1a365d; color: #fff; }
.card-orange .card-icon { background: #ed8936; color: #fff; }

.card-info {
    background: transparent;
}
.card-info h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: #2d3748;
    background: transparent;
}
.card-info p {
    font-size: 13px;
    color: #718096;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    background: transparent;
}

/* Table Cards */
.table-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 20px;
}
.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.table-header h4 {
    margin: 0;
    font-size: 18px;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
}
.table-header i {
    color: #1a365d;
}
.table-body {
    padding: 0;
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.modern-table thead th {
    background: #f7fafc;
    color: #4a5568;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 15px;
    border: none;
    border-bottom: 2px solid #e2e8f0;
}
.modern-table tbody tr {
    transition: background 0.2s;
}
.modern-table tbody tr:hover {
    background: #f7fafc;
}
.modern-table tbody td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid #edf2f7;
    vertical-align: middle;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge-default { background: #edf2f7; color: #718096; }
.badge-paid { background: #c6f6d5; color: #276749; }
.badge-unpaid { background: #fed7d7; color: #c53030; }
.badge-received { background: #fffff0; color: #d69e2e; }
.badge-cleaning { background: #f3e8ff; color: #805ad5; }
.badge-processing { background: #ebf8ff; color: #3182ce; }
.badge-transit { background: #e6fffa; color: #319795; }
.badge-delivered { background: #f0fff4; color: #38a169; }
</style>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>

<?php include('footer.php'); ?>
