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

// Handle form submission for adding expense
if(isset($_POST['add_expense'])) {
    $expense_category = mysqli_real_escape_string($conn, $_POST['expense_category']);
    $employee_id = !empty($_POST['employee_id']) ? intval($_POST['employee_id']) : NULL;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $amount = floatval($_POST['amount']);
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $reference_number = !empty($_POST['reference_number']) ? mysqli_real_escape_string($conn, $_POST['reference_number']) : NULL;
    $receipt_number = !empty($_POST['receipt_number']) ? mysqli_real_escape_string($conn, $_POST['receipt_number']) : NULL;
    $vendor_name = !empty($_POST['vendor_name']) ? mysqli_real_escape_string($conn, $_POST['vendor_name']) : NULL;
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $recorded_by = $_SESSION["id"];
    
    $sql = "INSERT INTO expenses (expense_category, employee_id, description, amount, expense_date, 
            payment_method, reference_number, receipt_number, vendor_name, notes, recorded_by) 
            VALUES ('$expense_category', " . ($employee_id ? $employee_id : "NULL") . ", '$description', 
            $amount, '$expense_date', '$payment_method', " . ($reference_number ? "'$reference_number'" : "NULL") . ", 
            " . ($receipt_number ? "'$receipt_number'" : "NULL") . ", " . ($vendor_name ? "'$vendor_name'" : "NULL") . ", 
            '$notes', $recorded_by)";
    
    if($conn->query($sql)) {
        $_SESSION['success'] = "Expense recorded successfully!";
    } else {
        $_SESSION['error'] = "Error recording expense: " . $conn->error;
    }
    header("Location: expenses.php");
    exit();
}

// Handle delete
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM expenses WHERE id = $delete_id";
    if($conn->query($sql)) {
        $_SESSION['success'] = "Expense deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting expense!";
    }
    header("Location: expenses.php");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Get filter parameters
$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$filter_employee = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Build query
$query = "SELECT e.*, emp.first_name, emp.last_name, emp.position, a.fname as admin_fname, a.lname as admin_lname 
          FROM expenses e 
          LEFT JOIN employees emp ON e.employee_id = emp.id 
          LEFT JOIN admin a ON e.recorded_by = a.id 
          WHERE e.expense_date BETWEEN '$filter_start_date' AND '$filter_end_date'";

if($filter_category) {
    $query .= " AND e.expense_category = '$filter_category'";
}
if($filter_employee) {
    $query .= " AND e.employee_id = $filter_employee";
}
$query .= " ORDER BY e.expense_date DESC, e.id DESC";

$result = $conn->query($query);

// Get summary by category
$summary_query = "SELECT 
    expense_category,
    SUM(amount) as total_amount,
    COUNT(*) as transaction_count
    FROM expenses 
    WHERE expense_date BETWEEN '$filter_start_date' AND '$filter_end_date' 
    GROUP BY expense_category 
    ORDER BY total_amount DESC";
$summary_result = $conn->query($summary_query);

// Get total expenses
$total_query = "SELECT SUM(amount) as grand_total FROM expenses WHERE expense_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
$total_result = $conn->query($total_query);
$grand_total = $total_result->fetch_assoc()['grand_total'] ?? 0;

// Get employees for dropdown
$employees_query = "SELECT id, first_name, last_name, position FROM employees WHERE status = 'active' ORDER BY first_name";
$employees_result = $conn->query($employees_query);

// Expense categories
$categories = [
    'rider_fee' => 'Rider Fee',
    'salary' => 'Salary',
    'rent' => 'Rent',
    'utilities' => 'Utilities',
    'supplies' => 'Supplies',
    'equipment' => 'Equipment',
    'maintenance' => 'Maintenance',
    'fuel' => 'Fuel',
    'marketing' => 'Marketing',
    'insurance' => 'Insurance',
    'taxes' => 'Taxes',
    'other' => 'Other'
];
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Expenses Management</h3> 
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Expenses</li>
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
                    <div class="card-icon"><i class="ti-wallet"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($grand_total, 0); ?></h3>
                        <p>Total Expenses</p>
                    </div>
                </div>
            </div>
            <?php 
            $summary_result->data_seek(0);
            $top_categories = 0;
            while($cat = $summary_result->fetch_assoc()) { 
                if($top_categories < 3) {
            ?>
            <div class="col-md-3">
                <div class="dashboard-card <?php echo $top_categories % 2 == 0 ? 'card-navy' : 'card-orange'; ?>">
                    <div class="card-icon"><i class="ti-receipt"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($cat['total_amount'], 0); ?></h3>
                        <p><?php echo $categories[$cat['expense_category']] ?? ucfirst($cat['expense_category']); ?></p>
                    </div>
                </div>
            </div>
            <?php 
                $top_categories++;
                }
            } 
            ?>
        </div>

        <!-- Filter and Add Section -->
        <div class="row m-t-30">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-filter"></i> Filter Expenses</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                            <i class="fa fa-plus"></i> Record Expense
                        </button>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="filter_category" class="form-control">
                                        <option value="">All Categories</option>
                                        <?php foreach($categories as $key => $label) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo $filter_category == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Employee</label>
                                    <select name="employee_id" class="form-control">
                                        <option value="0">All Employees</option>
                                        <?php while($emp = $employees_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $emp['id']; ?>" <?php echo $filter_employee == $emp['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
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
                                    <a href="expenses.php" class="btn btn-secondary btn-block"><i class="fa fa-refresh"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-list"></i> Expense Records</h4>
                        <a href="expense_report.php" class="btn btn-success" target="_blank"><i class="fa fa-file-excel-o"></i> Export Report</a>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table" id="expensesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Employee</th>
                                        <th>Vendor</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Receipt #</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()) { 
                                        $cat_colors = [
                                            'rider_fee' => 'badge-transit',
                                            'salary' => 'badge-received',
                                            'rent' => 'badge-cleaning',
                                            'utilities' => 'badge-processing',
                                            'supplies' => 'badge-default',
                                            'equipment' => 'badge-delivered',
                                            'maintenance' => 'badge-unpaid',
                                            'fuel' => 'badge-transit',
                                            'marketing' => 'badge-processing',
                                            'insurance' => 'badge-received',
                                            'taxes' => 'badge-cleaning',
                                            'other' => 'badge-default'
                                        ];
                                    ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $row['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['expense_date'])); ?></td>
                                        <td><span class="status-badge <?php echo $cat_colors[$row['expense_category']] ?? 'badge-default'; ?>"><?php echo $categories[$row['expense_category']] ?? ucfirst(str_replace('_', ' ', $row['expense_category'])); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo $row['employee_id'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['position'] . ')') : '-'; ?></td>
                                        <td><?php echo $row['vendor_name'] ?: '-'; ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $row['payment_method'])); ?></td>
                                        <td class="price-cell" style="color: #c53030;">Ksh<?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo $row['receipt_number'] ?: '-'; ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal<?php echo $row['id']; ?>"><i class="fa fa-eye"></i></button>
                                            <a href="expenses.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Expense Details #<?php echo $row['id']; ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
                                                        <tr><td><strong>Category:</strong></td><td><?php echo $categories[$row['expense_category']] ?? ucfirst($row['expense_category']); ?></td></tr>
                                                        <tr><td><strong>Description:</strong></td><td><?php echo htmlspecialchars($row['description']); ?></td></tr>
                                                        <tr><td><strong>Amount:</strong></td><td>Ksh<?php echo number_format($row['amount'], 2); ?></td></tr>
                                                        <tr><td><strong>Date:</strong></td><td><?php echo date('F d, Y', strtotime($row['expense_date'])); ?></td></tr>
                                                        <tr><td><strong>Payment Method:</strong></td><td><?php echo ucfirst($row['payment_method']); ?></td></tr>
                                                        <tr><td><strong>Reference:</strong></td><td><?php echo $row['reference_number'] ?: '-'; ?></td></tr>
                                                        <tr><td><strong>Receipt #:</strong></td><td><?php echo $row['receipt_number'] ?: '-'; ?></td></tr>
                                                        <tr><td><strong>Vendor:</strong></td><td><?php echo $row['vendor_name'] ?: '-'; ?></td></tr>
                                                        <tr><td><strong>Notes:</strong></td><td><?php echo nl2br(htmlspecialchars($row['notes'])); ?></td></tr>
                                                        <tr><td><strong>Recorded By:</strong></td><td><?php echo htmlspecialchars($row['admin_fname'] . ' ' . $row['admin_lname']); ?></td></tr>
                                                        <tr><td><strong>Recorded At:</strong></td><td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td></tr>
                                                    </table>
                                                </div>
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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Record New Expense</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expense Category *</label>
                                <select name="expense_category" class="form-control" required onchange="toggleEmployeeField(this.value)">
                                    <?php foreach($categories as $key => $label) { ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expense Date *</label>
                                <input type="date" name="expense_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="employee_field" style="display:none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    <?php 
                                    $employees_result->data_seek(0);
                                    while($emp = $employees_result->fetch_assoc()) { ?>
                                        <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' - ' . $emp['position']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Description *</label>
                                <input type="text" name="description" class="form-control" required placeholder="What was this expense for?">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount (Ksh) *</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Transaction ref">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control" placeholder="Receipt #">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor/Supplier Name</label>
                                <input type="text" name="vendor_name" class="form-control" placeholder="Who was paid?">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_expense" class="btn btn-primary">Record Expense</button>
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
    color: #c53030;
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
.badge-transit { background: #e6fffa; color: #319795; }
.badge-received { background: #fffff0; color: #d69e2e; }
.badge-cleaning { background: #f3e8ff; color: #805ad5; }
.badge-processing { background: #ebf8ff; color: #3182ce; }
.badge-delivered { background: #f0fff4; color: #38a169; }
.badge-unpaid { background: #fed7d7; color: #c53030; }
</style>

<script>
function toggleEmployeeField(category) {
    var employeeField = document.getElementById('employee_field');
    if(category === 'salary' || category === 'rider_fee') {
        employeeField.style.display = 'flex';
    } else {
        employeeField.style.display = 'none';
    }
}
</script>

<?php include('footer.php'); ?>
