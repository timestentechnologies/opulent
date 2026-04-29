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

// Handle form submission for adding payment
if(isset($_POST['add_payment'])) {
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;
    $supplier_name = !empty($_POST['supplier_name']) ? mysqli_real_escape_string($conn, $_POST['supplier_name']) : NULL;
    $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : NULL;
    $amount = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_reference = !empty($_POST['transaction_reference']) ? mysqli_real_escape_string($conn, $_POST['transaction_reference']) : NULL;
    $mpesa_code = !empty($_POST['mpesa_code']) ? mysqli_real_escape_string($conn, $_POST['mpesa_code']) : NULL;
    $mpesa_number = !empty($_POST['mpesa_number']) ? mysqli_real_escape_string($conn, $_POST['mpesa_number']) : NULL;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $recorded_by = $_SESSION["id"];
    
    $sql = "INSERT INTO payments (payment_type, customer_id, supplier_name, order_id, amount, payment_method, 
            transaction_reference, mpesa_code, mpesa_number, description, payment_date, recorded_by) 
            VALUES ('$payment_type', " . ($customer_id ? $customer_id : "NULL") . ", " . ($supplier_name ? "'$supplier_name'" : "NULL") . ", 
            " . ($order_id ? $order_id : "NULL") . ", $amount, '$payment_method', 
            " . ($transaction_reference ? "'$transaction_reference'" : "NULL") . ", 
            " . ($mpesa_code ? "'$mpesa_code'" : "NULL") . ", " . ($mpesa_number ? "'$mpesa_number'" : "NULL") . ", 
            '$description', '$payment_date', $recorded_by)";
    
    if($conn->query($sql)) {
        $_SESSION['success'] = "Payment recorded successfully!";
        // Update customer statement if customer payment
        if($payment_type == 'customer_payment' && $customer_id) {
            $payment_id = $conn->insert_id;
            $stmt_sql = "INSERT INTO customer_statements (customer_id, transaction_type, reference_id, reference_type, 
                         description, credit_amount, running_balance, transaction_date) 
                         SELECT $customer_id, 'payment', $payment_id, 'payment', '$description', $amount, 
                         COALESCE((SELECT running_balance FROM customer_statements WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1), 0) - $amount, 
                         '$payment_date'";
            $conn->query($stmt_sql);
        }
    } else {
        $_SESSION['error'] = "Error recording payment: " . $conn->error;
    }
    header("Location: payments.php");
    exit();
}

// Handle delete
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM payments WHERE id = $delete_id";
    if($conn->query($sql)) {
        $_SESSION['success'] = "Payment deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting payment!";
    }
    header("Location: payments.php");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Get filter parameters
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$filter_customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Build query
$query = "SELECT p.*, c.fname, c.lname, a.fname as admin_fname, a.lname as admin_lname 
          FROM payments p 
          LEFT JOIN customer c ON p.customer_id = c.id 
          LEFT JOIN admin a ON p.recorded_by = a.id 
          WHERE p.payment_date BETWEEN '$filter_start_date' AND '$filter_end_date'";

if($filter_type) {
    $query .= " AND p.payment_type = '$filter_type'";
}
if($filter_customer) {
    $query .= " AND p.customer_id = $filter_customer";
}
$query .= " ORDER BY p.payment_date DESC, p.id DESC";

$result = $conn->query($query);

// Get summary statistics
$summary_query = "SELECT 
    SUM(CASE WHEN payment_type = 'customer_payment' THEN amount ELSE 0 END) as customer_payments,
    SUM(CASE WHEN payment_type = 'supplier_payment' THEN amount ELSE 0 END) as supplier_payments,
    SUM(CASE WHEN payment_type = 'refund' THEN amount ELSE 0 END) as refunds,
    COUNT(*) as total_transactions
    FROM payments 
    WHERE payment_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Get customers for dropdown
$customers_query = "SELECT id, fname, lname FROM customer ORDER BY fname";
$customers_result = $conn->query($customers_query);
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Payments Management</h3> 
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Payments</li>
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
                    <div class="card-icon"><i class="ti-money"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['customer_payments'] ?? 0, 0); ?></h3>
                        <p>Customer Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-wallet"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['supplier_payments'] ?? 0, 0); ?></h3>
                        <p>Supplier Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-na"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['refunds'] ?? 0, 0); ?></h3>
                        <p>Refunds</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-receipt"></i></div>
                    <div class="card-info">
                        <h3><?php echo $summary['total_transactions'] ?? 0; ?></h3>
                        <p>Transactions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Add Section -->
        <div class="row m-t-30">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-filter"></i> Filter Payments</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPaymentModal">
                            <i class="fa fa-plus"></i> Record Payment
                        </button>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Payment Type</label>
                                    <select name="filter_type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="customer_payment" <?php echo $filter_type == 'customer_payment' ? 'selected' : ''; ?>>Customer Payment</option>
                                        <option value="supplier_payment" <?php echo $filter_type == 'supplier_payment' ? 'selected' : ''; ?>>Supplier Payment</option>
                                        <option value="refund" <?php echo $filter_type == 'refund' ? 'selected' : ''; ?>>Refund</option>
                                        <option value="other" <?php echo $filter_type == 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select name="customer_id" class="form-control">
                                        <option value="0">All Customers</option>
                                        <?php while($cust = $customers_result->fetch_assoc()) { ?>
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <a href="payments.php" class="btn btn-secondary btn-block"><i class="fa fa-refresh"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-list"></i> Payment Records</h4>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Customer/Supplier</th>
                                        <th>Order #</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Recorded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()) { 
                                        $type_class = '';
                                        switch($row['payment_type']) {
                                            case 'customer_payment': $type_class = 'badge-received'; break;
                                            case 'supplier_payment': $type_class = 'badge-cleaning'; break;
                                            case 'refund': $type_class = 'badge-unpaid'; break;
                                            default: $type_class = 'badge-default';
                                        }
                                    ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $row['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td><span class="status-badge <?php echo $type_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['payment_type'])); ?></span></td>
                                        <td>
                                            <?php 
                                            if($row['customer_id']) {
                                                echo htmlspecialchars($row['fname'] . ' ' . $row['lname']);
                                            } elseif($row['supplier_name']) {
                                                echo htmlspecialchars($row['supplier_name']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['order_id'] ? '#' . $row['order_id'] : '-'; ?></td>
                                        <td><?php echo ucfirst($row['payment_method']); ?></td>
                                        <td><?php echo $row['mpesa_code'] ?: $row['transaction_reference'] ?: '-'; ?></td>
                                        <td class="price-cell">Ksh<?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['admin_fname'] . ' ' . $row['admin_lname']); ?></td>
                                        <td>
                                            <a href="payment_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                                            <a href="payments.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
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
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Record New Payment</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Type *</label>
                                <select name="payment_type" class="form-control" required onchange="togglePaymentFields(this.value)">
                                    <option value="customer_payment">Customer Payment</option>
                                    <option value="supplier_payment">Supplier Payment</option>
                                    <option value="refund">Refund</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6" id="customer_field">
                            <div class="form-group">
                                <label>Customer</label>
                                <select name="customer_id" class="form-control select2">
                                    <option value="">Select Customer</option>
                                    <?php 
                                    $customers_result->data_seek(0);
                                    while($cust = $customers_result->fetch_assoc()) { ?>
                                        <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['fname'] . ' ' . $cust['lname']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="supplier_field" style="display:none;">
                            <div class="form-group">
                                <label>Supplier Name</label>
                                <input type="text" name="supplier_name" class="form-control" placeholder="Enter supplier name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Order ID (Optional)</label>
                                <input type="number" name="order_id" class="form-control" placeholder="Enter order number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount (Ksh) *</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="payment_method" class="form-control" required onchange="toggleReferenceFields(this.value)">
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="mpesa_fields" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>M-Pesa Code</label>
                                <input type="text" name="mpesa_code" class="form-control" placeholder="e.g., QK7HXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>M-Pesa Number</label>
                                <input type="text" name="mpesa_number" class="form-control" placeholder="e.g., 254712345678">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="reference_field" style="display:none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Transaction Reference</label>
                                <input type="text" name="transaction_reference" class="form-control" placeholder="Bank ref, Cheque number, etc.">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Payment description..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_payment" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePaymentFields(type) {
    if(type === 'supplier_payment') {
        document.getElementById('customer_field').style.display = 'none';
        document.getElementById('supplier_field').style.display = 'block';
    } else {
        document.getElementById('customer_field').style.display = 'block';
        document.getElementById('supplier_field').style.display = 'none';
    }
}

function toggleReferenceFields(method) {
    if(method === 'mpesa') {
        document.getElementById('mpesa_fields').style.display = 'flex';
        document.getElementById('reference_field').style.display = 'none';
    } else if(method === 'bank_transfer' || method === 'cheque') {
        document.getElementById('mpesa_fields').style.display = 'none';
        document.getElementById('reference_field').style.display = 'flex';
    } else {
        document.getElementById('mpesa_fields').style.display = 'none';
        document.getElementById('reference_field').style.display = 'none';
    }
}
</script>

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
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        'order': [[0, 'desc']]
    });
});
</script>

<?php include('footer.php'); ?>
