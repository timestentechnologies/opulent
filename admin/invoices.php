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

// Generate invoice number
function generateInvoiceNumber($conn) {
    $prefix = 'INV';
    $year = date('Y');
    $month = date('m');
    $sql = "SELECT COUNT(*) as count FROM invoices WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $next = $row['count'] + 1;
    return $prefix . $year . $month . str_pad($next, 4, '0', STR_PAD_LEFT);
}

// Handle form submission for adding invoice
if(isset($_POST['add_invoice'])) {
    $invoice_number = generateInvoiceNumber($conn);
    $customer_id = intval($_POST['customer_id']);
    $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : NULL;
    $issue_date = mysqli_real_escape_string($conn, $_POST['issue_date']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $subtotal = floatval($_POST['subtotal']);
    $tax_amount = floatval($_POST['tax_amount'] ?? 0);
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    $total_amount = floatval($_POST['total_amount']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $terms = mysqli_real_escape_string($conn, $_POST['terms']);
    $created_by = $_SESSION["id"];
    
    $sql = "INSERT INTO invoices (invoice_number, customer_id, order_id, issue_date, due_date, subtotal, 
            tax_amount, discount_amount, total_amount, balance_due, notes, terms, created_by) 
            VALUES ('$invoice_number', $customer_id, " . ($order_id ? $order_id : "NULL") . ", 
            '$issue_date', '$due_date', $subtotal, $tax_amount, $discount_amount, $total_amount, 
            $total_amount, '$notes', '$terms', $created_by)";
    
    if($conn->query($sql)) {
        $invoice_id = $conn->insert_id;
        
        // Add invoice items
        if(isset($_POST['items']) && is_array($_POST['items'])) {
            foreach($_POST['items'] as $item) {
                $description = mysqli_real_escape_string($conn, $item['description']);
                $quantity = floatval($item['quantity']);
                $unit_price = floatval($item['unit_price']);
                $total_price = $quantity * $unit_price;
                
                $item_sql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price) 
                            VALUES ($invoice_id, '$description', $quantity, $unit_price, $total_price)";
                $conn->query($item_sql);
            }
        }
        
        // Add to customer statement
        $stmt_sql = "INSERT INTO customer_statements (customer_id, transaction_type, reference_id, reference_type, 
                     description, debit_amount, running_balance, transaction_date) 
                     SELECT $customer_id, 'invoice', $invoice_id, 'invoice', 'Invoice $invoice_number', $total_amount, 
                     COALESCE((SELECT running_balance FROM customer_statements WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1), 0) + $total_amount, 
                     '$issue_date'";
        $conn->query($stmt_sql);
        
        $_SESSION['success'] = "Invoice created successfully! Invoice #: $invoice_number";
    } else {
        $_SESSION['error'] = "Error creating invoice: " . $conn->error;
    }
    header("Location: invoices.php");
    exit();
}

// Handle status update
if(isset($_POST['update_status'])) {
    $invoice_id = intval($_POST['invoice_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE invoices SET status = '$status' WHERE id = $invoice_id";
    if($conn->query($sql)) {
        $_SESSION['success'] = "Invoice status updated!";
    } else {
        $_SESSION['error'] = "Error updating status!";
    }
    header("Location: invoices.php");
    exit();
}

// Handle delete
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM invoices WHERE id = $delete_id";
    if($conn->query($sql)) {
        $_SESSION['success'] = "Invoice deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting invoice!";
    }
    header("Location: invoices.php");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Get filter parameters
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Build query
$query = "SELECT i.*, c.fname, c.lname, c.email, c.contact, a.fname as admin_fname, a.lname as admin_lname 
          FROM invoices i 
          LEFT JOIN customer c ON i.customer_id = c.id 
          LEFT JOIN admin a ON i.created_by = a.id 
          WHERE i.issue_date BETWEEN '$filter_start_date' AND '$filter_end_date'";

if($filter_status) {
    $query .= " AND i.status = '$filter_status'";
}
if($filter_customer) {
    $query .= " AND i.customer_id = $filter_customer";
}
$query .= " ORDER BY i.issue_date DESC, i.id DESC";

$result = $conn->query($query);

// Get summary statistics
$summary_query = "SELECT 
    COUNT(*) as total_invoices,
    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
    SUM(CASE WHEN status IN ('sent', 'partial', 'overdue') THEN balance_due ELSE 0 END) as outstanding_amount,
    SUM(total_amount) as total_value
    FROM invoices 
    WHERE issue_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Get customers for dropdown
$customers_query = "SELECT id, fname, lname FROM customer ORDER BY fname";
$customers_result = $conn->query($customers_query);

// Get services for dropdown
$services_query = "SELECT id, sname, prize FROM service ORDER BY sname";
$services_result = $conn->query($services_query);

// Invoice status options
$status_options = [
    'draft' => 'Draft',
    'sent' => 'Sent',
    'paid' => 'Paid',
    'partial' => 'Partially Paid',
    'overdue' => 'Overdue',
    'cancelled' => 'Cancelled'
];

$status_badges = [
    'draft' => 'badge-default',
    'sent' => 'badge-processing',
    'paid' => 'badge-paid',
    'partial' => 'badge-received',
    'overdue' => 'badge-unpaid',
    'cancelled' => 'badge-cleaning'
];
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Invoice Management</h3> 
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Invoices</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid -->
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-file"></i></div>
                    <div class="card-info">
                        <h3><?php echo $summary['total_invoices'] ?? 0; ?></h3>
                        <p>Total Invoices</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-money"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['total_value'] ?? 0, 0); ?></h3>
                        <p>Total Value</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-check"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['paid_amount'] ?? 0, 0); ?></h3>
                        <p>Paid Amount</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-alert"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['outstanding_amount'] ?? 0, 0); ?></h3>
                        <p>Outstanding</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Add Section -->
        <div class="row m-t-30">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-filter"></i> Filter Invoices</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addInvoiceModal">
                            <i class="fa fa-plus"></i> Create Invoice
                        </button>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="filter_status" class="form-control">
                                        <option value="">All Status</option>
                                        <?php foreach($status_options as $key => $label) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo $filter_status == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select name="customer_id" class="form-control">
                                        <option value="0">All Customers</option>
                                        <?php $customers_result->data_seek(0); while($cust = $customers_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $cust['id']; ?>" <?php echo $filter_customer == $cust['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cust['fname'] . ' ' . $cust['lname']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo $filter_start_date; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo $filter_end_date; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info btn-block"><i class="fa fa-filter"></i> Filter</button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <a href="invoices.php" class="btn btn-secondary btn-block"><i class="fa fa-refresh"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-list"></i> Invoice Records</h4>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table" id="invoicesTable">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()) { 
                                        $is_overdue = $row['status'] != 'paid' && strtotime($row['due_date']) < strtotime('today');
                                    ?>
                                    <tr>
                                        <td class="order-id"><?php echo $row['invoice_number']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></strong><br>
                                            <small class="text-muted"><?php echo $row['contact']; ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['issue_date'])); ?></td>
                                        <td <?php echo $is_overdue ? 'class="text-danger"' : ''; ?>>
                                            <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                            <?php if($is_overdue) echo '<br><small class="text-danger">Overdue</small>'; ?>
                                        </td>
                                        <td class="price-cell">Ksh<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td>Ksh<?php echo number_format($row['amount_paid'], 2); ?></td>
                                        <td <?php echo $row['balance_due'] > 0 ? 'class="text-warning font-weight-bold"' : ''; ?>>
                                            Ksh<?php echo number_format($row['balance_due'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $status_badges[$row['status']]; ?>">
                                                <?php echo $status_options[$row['status']] ?? ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="invoice_print.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#statusModal<?php echo $row['id']; ?>"><i class="fa fa-refresh"></i></button>
                                            <a href="invoices.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Update Invoice Status</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="invoice_id" value="<?php echo $row['id']; ?>">
                                                        <div class="form-group">
                                                            <label>Current Status: <?php echo $status_options[$row['status']]; ?></label>
                                                            <select name="status" class="form-control">
                                                                <?php foreach($status_options as $key => $label) { ?>
                                                                    <option value="<?php echo $key; ?>" <?php echo $row['status'] == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create New Invoice</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="invoiceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Customer *</label>
                                <select name="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    <?php 
                                    $customers_result->data_seek(0);
                                    while($cust = $customers_result->fetch_assoc()) { ?>
                                        <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['fname'] . ' ' . $cust['lname']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Order ID (Optional)</label>
                                <input type="number" name="order_id" class="form-control" placeholder="Order #">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Issue Date *</label>
                                <input type="date" name="issue_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Due Date *</label>
                                <input type="date" name="due_date" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row m-t-20">
                        <div class="col-12">
                            <h5>Invoice Items</h5>
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row">
                                        <td><input type="text" name="items[0][description]" class="form-control" required placeholder="Service description"></td>
                                        <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control qty" value="1" required onchange="calculateRow(this)"></td>
                                        <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control price" required onchange="calculateRow(this)"></td>
                                        <td><input type="text" class="form-control row-total" readonly value="0.00"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-info btn-sm" onclick="addRow()"><i class="fa fa-plus"></i> Add Item</button>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row m-t-20">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Terms & Conditions</label>
                                <textarea name="terms" class="form-control" rows="2" placeholder="Payment terms...">Payment is due within 14 days of invoice date.</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td><input type="text" id="subtotal" name="subtotal" class="form-control" readonly value="0.00"></td>
                                </tr>
                                <tr>
                                    <td><strong>Tax:</strong></td>
                                    <td><input type="number" step="0.01" id="tax_amount" name="tax_amount" class="form-control" value="0.00" onchange="calculateTotal()"></td>
                                </tr>
                                <tr>
                                    <td><strong>Discount:</strong></td>
                                    <td><input type="number" step="0.01" id="discount_amount" name="discount_amount" class="form-control" value="0.00" onchange="calculateTotal()"></td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Total:</strong></td>
                                    <td><input type="text" id="total_amount" name="total_amount" class="form-control font-weight-bold" readonly value="0.00"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_invoice" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
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

.card-info h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: #2d3748;
    background: transparent;
    padding: 0;
    border: none;
}
.card-info p {
    font-size: 13px;
    color: #718096;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
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
.order-id {
    font-weight: 600;
    color: #1a365d;
}
.price-cell {
    font-weight: 600;
    color: #38a169;
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
let rowCount = 1;

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const newRow = document.createElement('tr');
    newRow.className = 'item-row';
    newRow.innerHTML = `
        <td><input type="text" name="items[${rowCount}][description]" class="form-control" required placeholder="Service description"></td>
        <td><input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control qty" value="1" required onchange="calculateRow(this)"></td>
        <td><input type="number" step="0.01" name="items[${rowCount}][unit_price]" class="form-control price" required onchange="calculateRow(this)"></td>
        <td><input type="text" class="form-control row-total" readonly value="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
    `;
    tbody.appendChild(newRow);
    rowCount++;
}

function removeRow(btn) {
    const row = btn.closest('tr');
    if(document.querySelectorAll('.item-row').length > 1) {
        row.remove();
        calculateTotal();
    } else {
        alert('At least one item is required');
    }
}

function calculateRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const total = qty * price;
    row.querySelector('.row-total').value = total.toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.row-total').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const total = subtotal + tax - discount;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('total_amount').value = total.toFixed(2);
}

// Initialize
$(document).ready(function() {
    $('.select2').select2();
});
</script>

<?php include('footer.php'); ?>
